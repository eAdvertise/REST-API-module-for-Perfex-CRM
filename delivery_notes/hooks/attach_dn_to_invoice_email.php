<?php
defined('BASEPATH') or exit('No direct script access allowed');

function delivery_notes_attach_dn_pdf_before_email_template_send($payload)
{
    $CI = &get_instance();

    // helper for debug (disable easily by commenting)
    $log = function ($msg) {
        log_activity('[DN->InvoiceMail] ' . $msg);
    };

    $originalPayload = $payload;

    // 1) Get template object from payload (object or array wrapper)
    $template = null;

    if (is_object($payload)) {
        $template = $payload;
    } elseif (is_array($payload)) {
        foreach (['template', 'mailtemplate', 'email_template', 'data'] as $k) {
            if (isset($payload[$k]) && is_object($payload[$k])) {
                $template = $payload[$k];
                break;
            }
        }
    }

    if (!$template || !is_object($template)) {
        $log('No template object in payload. Type=' . gettype($payload));
        return $originalPayload;
    }

    // 2) Detect invoice template robustly
    $slug    = (string)($template->slug ?? ($template->template ?? ($template->name ?? '')));
    $relType = (string)($template->rel_type ?? '');

    $isInvoice = false;

    if ($relType === 'invoice') {
        $isInvoice = true;
    } elseif (isset($template->invoice) && is_object($template->invoice)) {
        $isInvoice = true;
    } elseif ($slug !== '' && stripos($slug, 'invoice') !== false) {
        $isInvoice = true;
    }

    if (!$isInvoice) {
        $log('Not an invoice template. rel_type=' . $relType . ' slug=' . $slug);
        return $originalPayload;
    }

    // 3) Invoice ID
    $invoiceId = (int)($template->rel_id ?? 0);

    if ($invoiceId <= 0 && isset($template->invoice) && is_object($template->invoice) && isset($template->invoice->id)) {
        $invoiceId = (int)$template->invoice->id;
    }

    if ($invoiceId <= 0) {
        $log('Invoice detected but invoiceId=0. rel_id missing. slug=' . $slug);
        return $originalPayload;
    }

    $log('Invoice detected. invoiceId=' . $invoiceId . ' slug=' . $slug . ' rel_type=' . $relType);

    // 4) Find Delivery Note linked to this invoice
    $dnRow = $CI->db
        ->select('id')
        ->where('invoiceid', $invoiceId)
        ->order_by('id', 'DESC')
        ->limit(1)
        ->get(db_prefix() . 'delivery_notes')
        ->row();

    if (!$dnRow || empty($dnRow->id)) {
        $log('No delivery note found for invoiceId=' . $invoiceId . ' (tbldelivery_notes.invoiceid).');
        return $originalPayload;
    }

    $dnId = (int)$dnRow->id;
    $log('Found delivery note id=' . $dnId . ' for invoiceId=' . $invoiceId);

    // 5) Load DN + build PDF to temp file
    try {
        $CI->load->model('delivery_notes/delivery_notes_model');
        $dn = $CI->delivery_notes_model->get($dnId);

        if (!$dn) {
            $log('delivery_notes_model->get(' . $dnId . ') returned null.');
            return $originalPayload;
        }

        // Generate PDF
        $pdf = delivery_note_pdf($dn);

        $tmpDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'perfex_dn_mail';
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0755, true);
        }

        $dnNumber = function_exists('format_delivery_note_number')
            ? format_delivery_note_number($dn->id)
            : ('DN-' . $dn->id);

        $fileName = mb_strtoupper(slug_it($dnNumber)) . '.pdf';
        $tmpPath  = $tmpDir . DIRECTORY_SEPARATOR . 'dn_' . (int)$dn->id . '_' . time() . '.pdf';

        $pdf->Output($tmpPath, 'F');

        if (!is_file($tmpPath) || filesize($tmpPath) < 100) {
            $log('PDF temp file not created or too small: ' . $tmpPath);
            return $originalPayload;
        }

        $log('PDF created: ' . $tmpPath . ' (' . filesize($tmpPath) . ' bytes), filename=' . $fileName);

        // 6) Attach with compatibility for different Perfex builds
        $attached = false;

        if (method_exists($template, 'add_attachment')) {

            // Try common signatures
            try {
                // (path, filename, mime)
                $template->add_attachment($tmpPath, $fileName, 'application/pdf');
                $attached = true;
                $log('Attached via add_attachment(path, filename, mime)');
            } catch (\Throwable $e1) {
                try {
                    // (array)
                    $template->add_attachment([
                        'attachment' => $tmpPath,
                        'filename'   => $fileName,
                        'type'       => 'application/pdf',
                    ]);
                    $attached = true;
                    $log('Attached via add_attachment(array)');
                } catch (\Throwable $e2) {
                    // fallback: set attachments property if exists
                }
            }
        }

        // Fallback: template has attachments array property
        if (!$attached) {
            if (property_exists($template, 'attachments') && is_array($template->attachments)) {
                $template->attachments[] = [
                    'attachment' => $tmpPath,
                    'filename'   => $fileName,
                    'type'       => 'application/pdf',
                ];
                $attached = true;
                $log('Attached by pushing into $template->attachments[]');
            }
        }

        if (!$attached) {
            $log('FAILED to attach (no compatible add_attachment / attachments).');
        }

    } catch (\Throwable $e) {
        $log('Exception while generating/attaching DN PDF: ' . $e->getMessage());
        // do not break email
    }

    return $originalPayload; // CRITICAL (filter)
}