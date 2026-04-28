<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** @noinspection PhpIncludeInspection */
require_once __DIR__ . '/REST_Controller.php';

/**
 * Guest Invoices API endpoint
 *
 * Existing:
 * - POST /api/guest_invoices  (data_post) -> create/find guest + create invoice (minimal payload supported)
 *
 * New:
 * - POST /api/guest_invoices/checkout (checkout_post)
 *   -> create/find guest
 *   -> create invoice (auto number + prefix)
 *   -> create payment (tblinvoicepaymentrecords.paymentmode)
 *   -> send email with Invoice PDF + Receipt/Payment PDF (single email, 2 attachments)
 *
 * Payload (checkout):
 * - email (required)
 * - items (required): array of lines, each line can be:
 *    A) { "item_id": 12, "qty": 2, "taxes_id": [1,2] }          // details pulled from tblitems
 *    B) { "description": "...", "long_description": "...", "qty": 1, "rate": 100, "taxes_id": [1] }
 * - payment_mode (required)  // maps to tblinvoicepaymentrecords.paymentmode
 * - payment_date (optional)  // default today
 * - transaction_id (optional)
 * - send_email (optional, default 1)
 *
 * IMPORTANT:
 * - This controller follows your current server setup approach:
 *   licensing call + error_reporting(0) (you chose to keep it for now).
 *   When you want, we will restore proper authtoken validation.
 */

class Guest_invoices extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Keep licensing intact
        \modules\api\core\Apiinit::the_da_vinci_code('api');

        // Your current approach: silence notices/warnings
        error_reporting(0);
    }

    private function get_payload(): array
    {
        $payload = $this->input->post(null, true);
        if (is_array($payload) && count($payload) > 0) {
            return $payload;
        }

        $raw = $this->input->raw_input_stream;
        if (!empty($raw)) {
            $json = json_decode($raw, true);
            if (is_array($json)) {
                return $json;
            }
        }

        return [];
    }

    private function normalize_items(array &$payload): void
    {
        // legacy: newitems/items may come as JSON string
        if (isset($payload['newitems']) && is_string($payload['newitems'])) {
            $decoded = json_decode($payload['newitems'], true);
            if (is_array($decoded)) {
                $payload['newitems'] = $decoded;
            }
        }
        if (isset($payload['items']) && is_string($payload['items'])) {
            $decoded = json_decode($payload['items'], true);
            if (is_array($decoded)) {
                $payload['items'] = $decoded;
            }
        }
    }

    private function ensure_invoice_basics(array &$invoice_data): void
    {
        if (empty($invoice_data['date'])) {
            $invoice_data['date'] = date('Y-m-d');
        }

        if (empty($invoice_data['currency'])) {
            $def = get_option('default_currency');
            $invoice_data['currency'] = $def ? $def : '1';
        }

        if (empty($invoice_data['duedate'])) {
            $dueAfter = (int)get_option('invoice_due_after');
            if ($dueAfter > 0) {
                $invoice_data['duedate'] = date('Y-m-d', strtotime('+' . $dueAfter . ' days', strtotime($invoice_data['date'])));
            } else {
                $invoice_data['duedate'] = $invoice_data['date'];
            }
        }
    }

    private function ensure_totals(array &$invoice_data): void
    {
        $hasSubtotal = isset($invoice_data['subtotal']) && $invoice_data['subtotal'] !== '' && $invoice_data['subtotal'] !== null;
        $hasTotal    = isset($invoice_data['total']) && $invoice_data['total'] !== '' && $invoice_data['total'] !== null;
        if ($hasSubtotal && $hasTotal) {
            return;
        }

        $subtotal = 0.0;
        if (isset($invoice_data['newitems']) && is_array($invoice_data['newitems'])) {
            foreach ($invoice_data['newitems'] as $it) {
                $qty  = isset($it['qty']) ? (float)$it['qty'] : 0.0;
                $rate = isset($it['rate']) ? (float)$it['rate'] : 0.0;
                $subtotal += ($qty * $rate);
            }
        }

        $subtotal = round($subtotal, 2);
        $total = $subtotal;

        if (!isset($invoice_data['discount_total']) || $invoice_data['discount_total'] === '' || $invoice_data['discount_total'] === null) {
            $invoice_data['discount_total'] = '0';
        }
        if (!isset($invoice_data['adjustment']) || $invoice_data['adjustment'] === '' || $invoice_data['adjustment'] === null) {
            $invoice_data['adjustment'] = '0';
        }

        $total += (float)$invoice_data['adjustment'];
        $total -= (float)$invoice_data['discount_total'];

        $invoice_data['subtotal'] = (string)number_format($subtotal, 2, '.', '');
        $invoice_data['total']    = (string)number_format(round($total, 2), 2, '.', '');
    }

    /**
     * taxes_id -> taxname array like ["VAT|19.00"]
     */
    private function taxname_from_ids($taxes_id): array
    {
        if (empty($taxes_id)) {
            return [];
        }

        if (!is_array($taxes_id)) {
            $taxes_id = array_filter(array_map('trim', explode(',', (string)$taxes_id)));
        }

        $out = [];
        foreach ($taxes_id as $tid) {
            $tid = (int)$tid;
            if ($tid <= 0) continue;

            $tax = $this->db->where('id', $tid)->get(db_prefix().'taxes')->row();
            if ($tax) {
                $out[] = $tax->name . '|' . number_format((float)$tax->taxrate, 2, '.', '');
            }
        }

        return $out;
    }

    /**
     * Build newitems from mixed input:
     * - item_id + qty (+ optional taxes_id override)
     * - or description/long_description/qty/rate (+ optional taxes_id)
     */
    private function build_newitems_from_mixed(array $items): array
    {
        $newitems = [];
        $order = 1;

        foreach ($items as $it) {
            if (!is_array($it)) continue;

            $qty = isset($it['qty']) ? (float)$it['qty'] : 1.0;
            if ($qty <= 0) $qty = 1.0;

            $itemId = (int)($it['item_id'] ?? $it['id'] ?? $it['itemid'] ?? 0);
            if ($itemId > 0) {
                $row = $this->db->where('id', $itemId)->get(db_prefix().'items')->row();
                if (!$row) continue;

                $newitems[] = [
                    'itemid'           => (string)$itemId,
                    'description'      => (string)$row->description,
                    'long_description' => (string)$row->long_description,
                    'qty'              => (string)$qty,
                    'rate'             => (string)$row->rate,
                    'unit'             => isset($row->unit) ? (string)$row->unit : '',
                    'order'            => (string)$order,
                    'taxname'          => $this->taxname_from_ids($it['taxes_id'] ?? []),
                ];
                $order++;
                continue;
            }

            $desc = trim((string)($it['description'] ?? ''));
            if ($desc === '') continue;

            $rate = isset($it['rate']) ? (float)$it['rate'] : 0.0;

            $newitems[] = [
                'description'      => $desc,
                'long_description' => (string)($it['long_description'] ?? ''),
                'qty'              => (string)$qty,
                'rate'             => (string)$rate,
                'unit'             => (string)($it['unit'] ?? ''),
                'order'            => (string)$order,
                'taxname'          => $this->taxname_from_ids($it['taxes_id'] ?? []),
            ];
            $order++;
        }

        return $newitems;
    }

    private function compute_totals_with_taxes(array $newitems): array
    {
        $subtotal = 0.0;
        $tax_total = 0.0;

        foreach ($newitems as $it) {
            $qty  = (float)($it['qty'] ?? 0);
            $rate = (float)($it['rate'] ?? 0);
            $line = $qty * $rate;
            $subtotal += $line;

            if (!empty($it['taxname']) && is_array($it['taxname'])) {
                foreach ($it['taxname'] as $tx) {
                    $parts = explode('|', (string)$tx);
                    $pct = isset($parts[1]) ? (float)$parts[1] : 0.0;
                    if ($pct > 0) {
                        $tax_total += ($line * $pct / 100.0);
                    }
                }
            }
        }

        $subtotal = round($subtotal, 2);
        $tax_total = round($tax_total, 2);
        $total = round($subtotal + $tax_total, 2);

        return [
            'subtotal'  => number_format($subtotal, 2, '.', ''),
            'total'     => number_format($total, 2, '.', ''),
            'tax_total' => number_format($tax_total, 2, '.', ''),
        ];
    }

    /**
     * Guest create/find:
     * - if exists contact by email -> return
     * - else create client + primary contact
     * - if no name/company provided, rename to Guest{client_id} (like your Guest Invoices module behavior)
     */
    private function get_or_create_guest(array $payload): array
    {
        $this->load->model('clients_model');

        $email = trim((string)($payload['email'] ?? ''));
        if ($email === '') {
            return [0, 0, 'Missing guest email'];
        }
		 // Optional single-field name from API form
        $name = trim((string)($payload['name'] ?? $payload['full_name'] ?? ''));
        $contact = $this->db->where('email', $email)->get(db_prefix().'contacts')->row();
        if ($contact) {
            $client_id  = (int)$contact->userid;
            $contact_id = (int)$contact->id;

            if ($name !== '') {
                $parts = preg_split('/\s+/', $name, 2);
                $firstname = trim($parts[0] ?? $name);
                $lastname  = trim($parts[1] ?? '');

                $this->db->where('userid', $client_id)->update(db_prefix().'clients', [
                    'company' => $name,
                ]);

                $this->db->where('id', $contact_id)->update(db_prefix().'contacts', [
                    'firstname' => $firstname,
                    'lastname'  => $lastname,
                ]);
            }

            return [$client_id, $contact_id, ''];
        }


        $company   = trim((string)($payload['company'] ?? $payload['company_name'] ?? ''));
        $firstname = trim((string)($payload['firstname'] ?? ''));
        $lastname  = trim((string)($payload['lastname'] ?? ''));

         // If only "name" provided, use it as company and contact name
        if ($name !== '' && $company === '' && $firstname === '' && $lastname === '') {
            $company = $name;
            $parts = preg_split('/\s+/', $name, 2);
            $firstname = trim($parts[0] ?? $name);
            $lastname  = trim($parts[1] ?? '');
        }

        if ($company === '') {
            $company = trim($firstname . ' ' . $lastname);
            if ($company === '') {
                $company = $email;
            }
        }

        $clientData = [
            'company'     => $company,
            'phonenumber' => (string)($payload['phonenumber'] ?? ''),
            'website'     => (string)($payload['website'] ?? ''),
            'address'     => (string)($payload['address'] ?? ''),
            'city'        => (string)($payload['city'] ?? ''),
            'state'       => (string)($payload['state'] ?? ''),
            'zip'         => (string)($payload['zip'] ?? ''),
            'country'     => (string)($payload['country'] ?? ''),
            'active'      => 1,
        ];

        $client_id = (int)$this->clients_model->add($clientData);
        if ($client_id <= 0) {
            return [0, 0, 'Failed to create client'];
        }

        $contactData = [
            'firstname' => $firstname !== '' ? $firstname : $company,
            'lastname'  => $lastname,
            'email'     => $email,
            'phonenumber' => (string)($payload['phonenumber'] ?? ''),
            'title'     => 'Guest',
            'is_primary'=> 1,
            'donotsendwelcomeemail' => 1,
        ];

        $contact_id = (int)$this->clients_model->add_contact($contactData, $client_id);
        if ($contact_id <= 0) {
            return [$client_id, 0, 'Failed to create contact'];
        }
		// If name provided, enforce it now (company + contact name)
        if ($name !== '') {
            $parts = preg_split('/\s+/', $name, 2);
            $fn = trim($parts[0] ?? $name);
            $ln = trim($parts[1] ?? '');

            $this->db->where('userid', $client_id)->update(db_prefix().'clients', [
                'company' => $name,
            ]);

            $this->db->where('id', $contact_id)->update(db_prefix().'contacts', [
                'firstname' => $fn,
                'lastname'  => $ln,
            ]);

        } else {

            // If nothing provided, use Guest{ID}
            $needsGuestName = (trim((string)($payload['company'] ?? '')) === ''
                && trim((string)($payload['firstname'] ?? '')) === ''
                && trim((string)($payload['lastname'] ?? '')) === '');

            if ($needsGuestName) {
                $guestName = 'Guest' . $client_id;

                $this->db->where('userid', $client_id)->update(db_prefix().'clients', [
                    'company' => $guestName,
                ]);

                $this->db->where('id', $contact_id)->update(db_prefix().'contacts', [
                    'firstname' => $guestName,
                    'lastname'  => '',
                ]);
            }
        }

        /* Match Guest Invoices module: if no provided name/company -> Guest{client_id}
        $needsGuestName = (trim((string)($payload['company'] ?? '')) === ''
            && trim((string)($payload['firstname'] ?? '')) === ''
            && trim((string)($payload['lastname'] ?? '')) === '');

        if ($needsGuestName) {
            $guestName = 'Guest' . $client_id;

            $this->db->where('userid', $client_id)->update(db_prefix().'clients', [
                'company' => $guestName,
            ]);

            $this->db->where('id', $contact_id)->update(db_prefix().'contacts', [
                'firstname' => $guestName,
                'lastname'  => '',
            ]);
        }*/

        return [$client_id, $contact_id, ''];
    }

    private function apply_auto_number(array &$invoice_data, bool &$didAutoNumber): void
    {
        $didAutoNumber = false;

        if (!empty($invoice_data['number'])) {
            return;
        }

        $next = (int)get_option('next_invoice_number');
        if ($next <= 0) $next = 1;

        $invoice_data['number'] = $next;
        $didAutoNumber = true;
    }

    private function bump_next_invoice_number_if_needed(bool $didAutoNumber, $usedNumber): void
    {
        if (!$didAutoNumber) return;

        $usedNumber = (int)$usedNumber;
        if ($usedNumber <= 0) return;

        $current = (int)get_option('next_invoice_number');
        if ($current <= $usedNumber) {
            update_option('next_invoice_number', $usedNumber + 1);
        }
    }

    /**
     * Send a single email with Invoice PDF + Receipt/Payment PDF.
     * Returns true/false.
     */

    /**
     * Send a single email with Invoice PDF + Receipt/Payment PDF.
     * Uses an editable Email Template (Admin -> Setup -> Email Templates) when available.
     * Returns true/false.
     */
    private function send_invoice_and_receipt_email(int $invoice_id, int $payment_id, int $contact_id): bool
    {
        try {
            $contact = $this->db->where('id', $contact_id)->get(db_prefix() . 'contacts')->row();
            if (!$contact || empty($contact->email)) {
                return false;
            }

            $this->load->model('invoices_model');
            $this->load->model('payments_model');

            $invoice = $this->invoices_model->get($invoice_id);
            $payment = $this->payments_model->get($payment_id);

            if (!$invoice || !$payment) {
                log_message('error', '[API Guest Checkout] Missing invoice/payment. invoice_id=' . $invoice_id . ' payment_id=' . $payment_id);
                return false;
            }

            // ---------- Build PDFs (robust to helper signature differences) ----------
            if (!function_exists('invoice_pdf')) {
                log_message('error', '[API Guest Checkout] invoice_pdf() helper not found.');
                return false;
            }

            $invPdfObj = invoice_pdf($invoice);
            $invPdfStr = $invPdfObj->Output('', 'S');

            $payPdfObj = null;
            $payPdfStr = '';

            if (function_exists('payment_pdf')) {
                try {
                    $payPdfObj = payment_pdf($payment);
                } catch (Throwable $e) {
                    try {
                        $payPdfObj = payment_pdf($payment_id);
                    } catch (Throwable $e2) {
                        $payPdfObj = null;
                    }
                }
            }

            if (!$payPdfObj && function_exists('payment_record_pdf')) {
                try {
                    $payPdfObj = payment_record_pdf($payment);
                } catch (Throwable $e) {
                    try {
                        $payPdfObj = payment_record_pdf($payment_id);
                    } catch (Throwable $e2) {
                        $payPdfObj = null;
                    }
                }
            }

            if (!$payPdfObj) {
                log_message('error', '[API Guest Checkout] No payment PDF helper available (payment_pdf/payment_record_pdf).');
                return false;
            }

            $payPdfStr = $payPdfObj->Output('', 'S');

            // ---------- Temp paths ----------
            $tmpDir = sys_get_temp_dir();
            $perfexTmp = defined('FCPATH') ? rtrim(FCPATH, '/\\') . '/uploads/temp' : '';
            if ($perfexTmp && is_dir($perfexTmp) && is_writable($perfexTmp)) {
                $tmpDir = $perfexTmp;
            }

            $uniq = uniqid('', true);
            $invPath = rtrim($tmpDir, '/\\') . '/invoice_' . $invoice_id . '_' . $uniq . '.pdf';
            $payPath = rtrim($tmpDir, '/\\') . '/payment_' . $payment_id . '_' . $uniq . '.pdf';

            file_put_contents($invPath, $invPdfStr);
            file_put_contents($payPath, $payPdfStr);

            // ---------- Subject/message from template (fallback to defaults) ----------
            $tplSlug = 'api_guest_invoice_checkout';
            $subject = 'Invoice & Receipt';
            $message = 'Please find attached your invoice and receipt.';
            $fromEmail = '';
            $fromName  = '';

            try {
                if ($this->db->table_exists(db_prefix() . 'emailtemplates')) {
                    $this->db->where('slug', $tplSlug);
                    if ($this->db->field_exists('language', db_prefix() . 'emailtemplates')) {
                        $lang = (string)(get_option('active_language') ?: 'english');
                        $this->db->where('language', $lang);
                    }
                    $tpl = $this->db->get(db_prefix() . 'emailtemplates')->row();
                    if ($tpl) {
                        if (!empty($tpl->subject)) $subject = (string)$tpl->subject;
                        if (!empty($tpl->message)) $message = (string)$tpl->message;

                        // Optional per-template overrides (if present)
                        if (property_exists($tpl, 'fromemail') && !empty($tpl->fromemail)) {
                            $fromEmail = (string)$tpl->fromemail;
                        }
                        if (property_exists($tpl, 'fromname') && !empty($tpl->fromname)) {
                            $fromName = (string)$tpl->fromname;
                        }
                    }
                }
            } catch (Throwable $e) {
                // ignore template lookup errors, keep defaults
            }

            // Fallback to global settings (Setup -> Settings -> Email)
            if ($fromEmail === '') {
                $fromEmail = (string)(get_option('smtp_email') ?: get_option('smtp_username') ?: get_option('company_email') ?: '');
            }
            if ($fromName === '') {
                $fromName = (string)(get_option('companyname') ?: get_option('email_fromname') ?: '');
            }

            // Guarantee non-empty subject/message (some mailers display blank otherwise)
            if (trim((string)$subject) === '') {
                $subject = 'Invoice & Receipt';
            }
            if (trim((string)$message) === '') {
                $message = 'Please find attached your invoice and receipt.';
            }

            $invoiceNumber = '';
            if (function_exists('format_invoice_number')) {
                try {
                    $invoiceNumber = format_invoice_number($invoice_id);
                } catch (Throwable $e) {
                    $invoiceNumber = '';
                }
            }
            if ($invoiceNumber === '') {
                $invoiceNumber = (string)($invoice->number ?? $invoice_id);
            }

            $placeholders = [
                '{companyname}'       => (string)(get_option('companyname') ?: ''),
                '{contact_firstname}' => (string)($contact->firstname ?? ''),
                '{contact_lastname}'  => (string)($contact->lastname ?? ''),
                '{contact_email}'     => (string)$contact->email,
                '{invoice_id}'        => (string)$invoice_id,
                '{invoice_number}'    => (string)$invoiceNumber,
                '{payment_id}'        => (string)$payment_id,
                '{payment_amount}'    => (string)($payment->amount ?? ''),
                '{payment_date}'      => (string)($payment->date ?? ''),
            ];

            $subject = strtr($subject, $placeholders);
            $message = strtr($message, $placeholders);

            // Signature placeholder (common in Perfex templates)
            if (strpos($message, '{email_signature}') !== false) {
                $message = str_replace('{email_signature}', (string)(get_option('email_signature') ?: ''), $message);
            }

            if (stripos($message, '<') === false) {
                $message = nl2br($message);
            }

            // ---------- Send (prefer app_mail; fallback to CI Email) ----------
            $sent = false;

            if (function_exists('app_mail')) {
                try {
                    // Perfex app_mail (common pattern): array of arrays with attachment + filename.
                    $attachments = [
                        ['attachment' => $invPath, 'filename' => 'Invoice-' . $invoiceNumber . '.pdf'],
                        ['attachment' => $payPath, 'filename' => 'Receipt-' . $payment_id . '.pdf'],
                    ];

                    $sent = (bool)app_mail($contact->email, $subject, $message, $attachments);
                } catch (Throwable $e) {
                    $sent = false;
                }
            }

            if (!$sent) {
                $CI = &get_instance();
                $CI->load->library('email');
                $CI->email->clear(true);
                $CI->email->set_mailtype('html');
                if ($fromEmail !== '') {
                    $CI->email->from($fromEmail, $fromName);
                }
                $CI->email->set_newline("\r\n");
                $CI->email->set_crlf("\r\n");
                $CI->email->to($contact->email);
                $CI->email->subject($subject);
                $CI->email->message($message);
                $CI->email->set_alt_message(strip_tags((string)$message));
                $CI->email->attach($invPath, 'attachment', 'Invoice-' . $invoiceNumber . '.pdf');
                $CI->email->attach($payPath, 'attachment', 'Receipt-' . $payment_id . '.pdf');

                $sent = (bool)$CI->email->send();
                if (!$sent) {
                    try {
                        log_message('error', '[API Guest Checkout] Email send failed. Debug: ' . $CI->email->print_debugger(['headers']));
                    } catch (Throwable $e) {
                        log_message('error', '[API Guest Checkout] Email send failed and debugger unavailable.');
                    }
                }
            }

            @unlink($invPath);
            @unlink($payPath);

            return (bool)$sent;
        } catch (Throwable $e) {
            log_message('error', '[API Guest Checkout] Exception while sending email: ' . $e->getMessage());
            return false;
        }
    }
    /**
     * POST /api/guest_invoices
     * (minimal payload supported) - create/find guest + create invoice
     */
    public function data_post()
    {
        $payload = $this->get_payload();
        $this->normalize_items($payload);

        if (empty($payload['email'])) {
            return $this->response(['status' => false, 'message' => 'email is required'], REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Allow items[{id,qty}] -> build newitems from tblitems
        if ((!isset($payload['newitems']) || !is_array($payload['newitems']) || count($payload['newitems']) === 0)
            && isset($payload['items']) && is_array($payload['items'])) {

            $payload['newitems'] = $this->build_newitems_from_mixed($payload['items']);
        }

        if (!isset($payload['newitems']) || !is_array($payload['newitems']) || count($payload['newitems']) === 0) {
            return $this->response(['status' => false, 'message' => 'items or newitems is required'], REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Create/find guest
        [$client_id, $contact_id, $err] = $this->get_or_create_guest($payload);
        if ($client_id <= 0 || $contact_id <= 0) {
            return $this->response(['status' => false, 'message' => $err ?: 'Unable to create/find guest'], REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Create invoice
        $this->load->model('invoices_model');

        $invoice_data = $payload;
        unset(
            $invoice_data['email'],
            $invoice_data['firstname'],
            $invoice_data['lastname'],
            $invoice_data['company'],
            $invoice_data['phonenumber'],
            $invoice_data['address'],
            $invoice_data['city'],
            $invoice_data['state'],
            $invoice_data['zip'],
            $invoice_data['country'],
            $invoice_data['website'],
            $invoice_data['items']
        );

        $invoice_data['clientid'] = $client_id;

        $this->ensure_invoice_basics($invoice_data);

        $didAutoNumber = false;
        $this->apply_auto_number($invoice_data, $didAutoNumber);

        $this->ensure_totals($invoice_data);

        $invoice_id = $this->invoices_model->add($invoice_data);
        if (!$invoice_id) {
            $db_err = $this->db->error();
            return $this->response([
                'status'   => false,
                'message'  => 'Invoice not created',
                'db_error' => $db_err,
            ], REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->bump_next_invoice_number_if_needed($didAutoNumber, $invoice_data['number']);

        return $this->response([
            'status'         => true,
            'message'        => 'Guest invoice created successfully',
            'invoice_id'     => (int)$invoice_id,
            'client_id'      => (int)$client_id,
            'contact_id'     => (int)$contact_id,
            'invoice_number' => (int)$invoice_data['number'],
            'invoice_prefix' => get_option('invoice_prefix'),
        ], REST_Controller::HTTP_CREATED);
    }

    /**
     * POST /api/guest_invoices/checkout
     * create invoice + payment + email (invoice + receipt)
     */
    public function checkout_post()
    {
        $payload = $this->get_payload();
        $this->normalize_items($payload);

        if (empty($payload['email'])) {
            return $this->response(['status'=>false,'message'=>'email is required'], REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
        }

        $items = $payload['items'] ?? [];
        if (is_string($items)) $items = json_decode($items, true);
        if (!is_array($items) || count($items) === 0) {
            return $this->response(['status'=>false,'message'=>'items is required'], REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
        }

        // payment_mode is required
        $paymentmode = (int)($payload['payment_mode'] ?? $payload['paymentmode'] ?? $payload['payment_mode_id'] ?? 0);
        if ($paymentmode <= 0) {
            return $this->response(['status'=>false,'message'=>'payment_mode is required'], REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
        }

        // 1) guest
        [$client_id, $contact_id, $err] = $this->get_or_create_guest($payload);
        if ($client_id <= 0 || $contact_id <= 0) {
            return $this->response(['status'=>false,'message'=>$err ?: 'Unable to create/find guest'], REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
        }

        // 2) invoice items
        $newitems = $this->build_newitems_from_mixed($items);
        if (count($newitems) === 0) {
            return $this->response(['status'=>false,'message'=>'No valid items provided'], REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
        }

        // 3) invoice create
        $this->load->model('invoices_model');

        $invoice_data = [
            'clientid' => $client_id,
            'date'     => $payload['date'] ?? date('Y-m-d'),
            'currency' => $payload['currency'] ?? (get_option('default_currency') ?: '1'),
            'newitems' => $newitems,
        ];

        // duedate default
        $dueAfter = (int)get_option('invoice_due_after');
        $invoice_data['duedate'] = !empty($payload['duedate'])
            ? $payload['duedate']
            : ($dueAfter > 0 ? date('Y-m-d', strtotime('+'.$dueAfter.' days', strtotime($invoice_data['date']))) : $invoice_data['date']);

        // auto number if missing
        $didAutoNumber = false;
        $this->apply_auto_number($invoice_data, $didAutoNumber);

        // totals with taxes
        $totals = $this->compute_totals_with_taxes($newitems);
        $invoice_data['subtotal'] = $totals['subtotal'];
        $invoice_data['total']    = $totals['total'];

        $invoice_id = $this->invoices_model->add($invoice_data);
        if (!$invoice_id) {
            return $this->response(['status'=>false,'message'=>'Invoice not created','db_error'=>$this->db->error()], REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->bump_next_invoice_number_if_needed($didAutoNumber, $invoice_data['number']);

        // 4) payment create
        $this->load->model('payments_model');

        $amount = !empty($payload['amount']) ? (float)$payload['amount'] : (float)$invoice_data['total'];

        $payment_data = [
            'invoiceid'     => $invoice_id,
            'amount'        => $amount,
            'paymentmode'   => $paymentmode, // tblinvoicepaymentrecords.paymentmode
            'date'          => $payload['payment_date'] ?? date('Y-m-d'),
            'transactionid' => (string)($payload['transaction_id'] ?? ''),
            'note'          => (string)($payload['payment_note'] ?? ''),
        ];

        // Some Perfex builds don't auto-load app_hooks but Payments_model->add() expects it
        $this->ensure_app_hooks();


        $payment_id = $this->payments_model->add($payment_data);
        if (!$payment_id) {
            return $this->response(['status'=>false,'message'=>'Payment not created','db_error'=>$this->db->error()], REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
        }

        // 5) email
        $send_email = isset($payload['send_email']) ? (int)$payload['send_email'] : 1;
        $email_sent = false;

        if ($send_email === 1) {
            $email_sent = $this->send_invoice_and_receipt_email((int)$invoice_id, (int)$payment_id, (int)$contact_id);
        }

        return $this->response([
            'status'         => true,
            'message'        => 'Checkout completed',
            'client_id'      => (int)$client_id,
            'contact_id'     => (int)$contact_id,
            'invoice_id'     => (int)$invoice_id,
            'payment_id'     => (int)$payment_id,
            'paymentmode'    => (int)$paymentmode,
            'email_sent'     => (bool)$email_sent,
            'invoice_prefix' => get_option('invoice_prefix'),
            'invoice_number' => (int)($invoice_data['number'] ?? 0),
            'tax_total'      => $totals['tax_total'],
        ], REST_Controller::HTTP_CREATED);
    }

    /**
     * Perfex compatibility:
     * Some core builds call $this->app_hooks->trigger() inside Payments_model->add()
     * but don't auto-load app_hooks in API context. If app_hooks is missing,
     * we inject a no-op stub to prevent fatal errors.
     */
    private function ensure_app_hooks(): void
    {
        if (isset($this->app_hooks) && $this->app_hooks) {
            return;
        }

        // If the library exists in this Perfex build, load it.
        $possible = [
            APPPATH . 'libraries/App_hooks.php',
            APPPATH . 'libraries/App_Hooks.php',
            APPPATH . 'libraries/app_hooks.php',
        ];

        foreach ($possible as $p) {
            if (is_file($p)) {
                // Safe to load only when file exists, otherwise CI will show_error and exit.
                $this->load->library('app_hooks');
                break;
            }
        }

        // If still not available, inject stub.
        if (!isset($this->app_hooks) || !$this->app_hooks) {
            $this->app_hooks = new Api_Null_Hooks();
        }
    }


}


/**
 * Minimal no-op hooks stub used only when Perfex app_hooks library is not available.
 */
class Api_Null_Hooks
{
    public function trigger(...$args)
    {
        // Return "payload" when provided, to mimic filter-like behaviour.
        return $args[1] ?? null;
    }

    public function do_action(...$args)
    {
        return null;
    }

    public function apply_filters(...$args)
    {
        return $args[1] ?? null;
    }

    public function __call($name, $arguments)
    {
        return $arguments[1] ?? null;
    }
}

