<?php
//./guestinvoices.php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Guest Invoices
Description: Invoicing on Guest
Version: 1.0.0
Requires at least: 3.4.*
Author: eAdvertise
*/

define('GUESTINVOICES_MODULE', 'guestinvoices');

if (!function_exists('gi_debug_log')) {
    function gi_debug_log($msg) {
        // Στο PHP error log
        log_message('error', '[GI] ' . $msg);
        // Και στο Perfex Activity Log (Utilities -> Activity Log)
        if (function_exists('log_activity')) {
            log_activity('[GI] ' . $msg);
        }
    }
}


// ------------------------------------------------------
// ΜΙΝΙΜΑΛ εγκατάσταση (κρατάμε τυχόν υπάρχουσες ρυθμίσεις σου)
// ------------------------------------------------------
if (!function_exists('guestinvoices_install')) {
    function guestinvoices_install()
    {
        $installFile = __DIR__ . '/install.php';
        if (is_file($installFile)) {
            require_once $installFile;
            if (function_exists('guestinvoices_run_install')) {
                guestinvoices_run_install();
            }
        }
    }
}
register_activation_hook(GUESTINVOICES_MODULE, 'guestinvoices_install');
register_language_files(GUESTINVOICES_MODULE, ['guestinvoices']);


// ------------------------------------------------------
// Utility: αν είμαστε σε σελίδες που μας νοιάζουν
// ------------------------------------------------------
if (!function_exists('gi_is_supported_admin_page')) {
    function gi_is_supported_admin_page()
    {
        $uri = strtolower($_SERVER['REQUEST_URI'] ?? '');
        return (strpos($uri, '/admin/invoices') !== false)
            || (strpos($uri, '/admin/estimates') !== false)
            || (strpos($uri, '/admin/credit_notes') !== false);
    }
}

// ------------------------------------------------------
// CSS για 80/20 layout με gap 10px (ασφαλές, μόνο για supported pages)
// ------------------------------------------------------
hooks()->add_action('app_admin_head', function () {
    if (!gi_is_supported_admin_page()) { return; }
    echo '<style>
    .gi-client-row{ display:flex; align-items:stretch; gap:10px; width:100%; }
    .gi-client-row .bootstrap-select,
    .gi-client-row select[name="clientid"],
    .gi-client-row select[name="customerid"]{ flex:0 0 80%; max-width:80%; }
    #gi-open-guest-btn{ flex:0 0 20%; max-width:20%; width:100%; white-space:nowrap; height:100%; }
    .gi-client-row .bootstrap-select > .btn{ border-radius:6px; box-shadow:0 1px 2px rgba(0,0,0,.06); border-color:#e5e7eb; }
    #gi-open-guest-btn.btn{ border-radius:6px; box-shadow:0 1px 2px rgba(0,0,0,.06); }
    @media (max-width:768px){
      .gi-client-row{ flex-direction:column; }
      .gi-client-row .bootstrap-select,
      .gi-client-row select[name="clientid"],
      .gi-client-row select[name="customerid"],
      #gi-open-guest-btn{ flex:0 0 100%; max-width:100%; }
    }
    </style>';
});

// ------------------------------------------------------
// Inject Modal + JS ΜΟΝΟ σε supported pages και ΜΟΝΟ αν υπάρχουν τα αρχεία
// ------------------------------------------------------
hooks()->add_action('app_admin_footer', function () {
    if (!gi_is_supported_admin_page()) { return; }
    $CI = &get_instance();

    // Προαιρετικά περνάμε χώρες στο modal (αν υπάρχει ο πίνακας)
    $data = ['countries' => []];
    if ($CI->db->table_exists(db_prefix().'countries')) {
        $CI->db->select('country_id, short_name');
        $data['countries'] = $CI->db->get(db_prefix().'countries')->result_array();
    }

    // Paths των views
    $modalViewPath = APP_MODULES_PATH . 'guestinvoices/views/invoice_guest_modal.php';
    $guestJsPath   = APP_MODULES_PATH . 'guestinvoices/views/guest_customer_js.php';
    $saveJsPath    = APP_MODULES_PATH . 'guestinvoices/views/gi_save_record_js.php';
    $labelJsPath   = APP_MODULES_PATH . 'guestinvoices/views/gi_customer_label_js.php';

    // Modal
    if (is_file($modalViewPath)) {
        $CI->load->view('guestinvoices/invoice_guest_modal', $data);
    }

    // Ενοποιημένο JS για κουμπί "Guest"
    if (is_file($guestJsPath)) {
        echo '<script>' . $CI->load->view('guestinvoices/guest_customer_js', [], true) . '</script>';
    }

    // JS για flag "Save & Record Payment" (Invoices)
    if (strpos(strtolower($_SERVER['REQUEST_URI'] ?? ''), '/admin/invoices') !== false && is_file($saveJsPath)) {
        echo '<script>' . $CI->load->view('guestinvoices/gi_save_record_js', [], true) . '</script>';
    }

    if (is_file($labelJsPath)) {
        echo '<script>' . $CI->load->view('guestinvoices/gi_customer_label_js', [], true) . '</script>';
    }
});

// ------------------------------------------------------
// Hook: μετά τη δημιουργία Invoice -> Record Payment + 1 email (με guards)
// ------------------------------------------------------
hooks()->add_action('after_invoice_added', 'gi_after_invoice_added_combo');

if (!function_exists('gi_after_invoice_added_combo')) {
    function gi_after_invoice_added_combo($invoice_id)
    {
        $CI =& get_instance();

        // --- Διαβάζουμε flags από SESSION, ΟΧΙ από POST ---
        $comboFlow = (int)$CI->session->userdata('gi_combo_flow');
        $comboAmount = (string)$CI->session->userdata('gi_combo_amount');

        // Καθαρίζουμε αμέσως για να μην “μένουν”
        $CI->session->unset_userdata('gi_combo_flow');
        $CI->session->unset_userdata('gi_combo_amount');

        if ($comboFlow !== 1) { return; } // δεν είναι το combo flow -> τέλος

        // Models
        $CI->load->model('invoices_model');
        $CI->load->model('payments_model');
        $CI->load->model('clients_model');

        $invoice = $CI->invoices_model->get($invoice_id);
        if (!$invoice) { return; }

        // Ποσό πληρωμής
        $amount = (strtolower($comboAmount) === 'full') ? (float)$invoice->total : (float)$invoice->total;

        // Mode
        $modeId = gi_find_default_payment_mode_id();

        // Record Payment
        $paymentData = [
            'amount'      => $amount,
            'invoiceid'   => $invoice_id,
            'paymentmode' => $modeId,
            'date'        => date('Y-m-d'),
            'note'        => 'Auto payment via Save & Record (GI)',
        ];
        $payment_id = $CI->payments_model->add($paymentData);

        if ($payment_id) {
            log_activity('GI MAIL: triggering combined email invoice_id='.$invoice_id.' payment_id='.$payment_id);
            gi_send_combined_invoice_payment_email($invoice_id, $payment_id);
        }
    }
}



// ------------------------------------------------------
// Helper: Default payment mode (π.χ. Cash) με guards
// ------------------------------------------------------
if (!function_exists('gi_find_default_payment_mode_id')) {
    function gi_find_default_payment_mode_id()
    {
        $CI =& get_instance();
        $modeId = 0;

        if ($CI->db->table_exists(db_prefix().'payment_modes')) {
            $modes = $CI->db->get(db_prefix().'payment_modes')->result_array();
            foreach ($modes as $m) {
                if (!empty($m['active']) && stripos($m['name'], 'cash') !== false) {
                    $modeId = (int)$m['id']; break;
                }
            }
            if (!$modeId) {
                foreach ($modes as $m) {
                    if (!empty($m['active'])) { $modeId = (int)$m['id']; break; }
                }
            }
        }
        return $modeId;
    }
}

// ------------------------------------------------------
// Email με 2 συνημμένα (Invoice + Payment) με ελέγχους
// ------------------------------------------------------
if (!function_exists('gi_send_combined_invoice_payment_email')) {
    function gi_send_combined_invoice_payment_email($invoice_id, $payment_id)
    {
        $CI =& get_instance();

        $CI->load->model('invoices_model');
        $CI->load->model('payments_model');
        $CI->load->model('clients_model');

        $invoice = $CI->invoices_model->get($invoice_id);
        $payment = $CI->payments_model->get($payment_id);
        if (!$invoice || !$payment) { return false; }

        $primaryContactId = get_primary_contact_user_id($invoice->clientid);
        if (!$primaryContactId) { return false; }
        $contact = $CI->clients_model->get_contact($primaryContactId);
        if (!$contact || empty($contact->email)) { return false; }

        $toEmail = $contact->email;
        $toName  = trim(($contact->firstname ?? '').' '.($contact->lastname ?? ''));

        // Δημιουργία PDFs μόνο αν υπάρχουν οι helpers
        $invoicePdfPath = gi_build_invoice_pdf($invoice);
        $paymentPdfPath = gi_build_payment_pdf($payment);
        $currency = null;
        if (is_object($invoice->currency) && isset($invoice->currency->symbol)) {
            $currency = $invoice->currency;
        } else {
            if (!function_exists('get_currency')) { $CI->load->helper('misc'); }
            if (function_exists('get_currency')) {
                $currency = get_currency($invoice->currency);
            }
            if (!$currency && function_exists('get_base_currency')) {
                $currency = get_base_currency();
            }
        }
        $subject = (_l('invoice') . ' #' . ($invoice->number ?? $invoice->id))
                 . ' & '
                 . (_l('payment') . ' #' . ($payment->paymentid ?? $payment->id));

        $body  = '<p>' . _l('hello') . ' ' . html_escape($toName) . ',</p>';
        $body .= '<p>' . _l('invoice').' #' . html_escape(format_invoice_number($invoice->id)) . ' ' . _l('has_been_created_successfully') . '.</p>';
        $body .= '<p>' . _l('payment') . ' ' . _l('has_been_recorded') . ' ('. app_format_money($payment->amount, $currency) .').</p>';
        $body .= '<p>' . _l('attached_pdf') . ': ' . _l('invoice') . ' & ' . _l('payment') . '.</p>';
        $body .= '<p>' . _l('thank_you') . '</p>';

        $CI->load->library('email');
        $CI->email->clear(true);
        $CI->email->set_mailtype('html');
        $CI->email->set_newline("\r\n");
        $CI->email->to($toEmail);
        $CI->email->from(get_option('smtp_email') ?: 'no-reply@'.parse_url(site_url(), PHP_URL_HOST),
                         get_option('companyname') ?: 'System');
        $CI->email->subject($subject);
        $CI->email->message($body);

        if (is_file($invoicePdfPath)) { $CI->email->attach($invoicePdfPath); }
        if (is_file($paymentPdfPath)) { $CI->email->attach($paymentPdfPath); }

        $sent = false;
        try {
            $sent = $CI->email->send(false);
            log_activity('GI MAIL: combined sent to '.$toEmail.' (invoice '.$invoice_id.', payment '.$payment_id.') result='.($sent?'OK':'FAIL'));
            if (!$sent) {
                log_activity('GI MAIL DEBUG: '.$CI->email->print_debugger(['headers']));
            }
        } catch (\Throwable $e) {
            log_activity('GI MAIL EXC: '.$e->getMessage());
            $sent = false;
        }

        if (is_file($invoicePdfPath)) { @unlink($invoicePdfPath); }
        if (is_file($paymentPdfPath)) { @unlink($paymentPdfPath); }

        return $sent;
    }
}

// ------------------------------------------------------
// PDF builders με έλεγχο ύπαρξης helpers
// ------------------------------------------------------
if (!function_exists('gi_build_invoice_pdf')) {
    function gi_build_invoice_pdf($invoice)
    {
        $CI =& get_instance();
        $CI->load->helper('pdf'); // πρέπει να παρέχει invoice_pdf()
        if (!function_exists('invoice_pdf')) { return ''; }
        $pdf = invoice_pdf($invoice);
        $tmp = tempnam(sys_get_temp_dir(), 'pdf-inv-');
        $path = $tmp . '.pdf';
        @unlink($tmp);
        if (method_exists($pdf, 'Output')) {
            $pdf->Output($path, 'F');
            return $path;
        }
        return '';
    }
}

if (!function_exists('gi_build_payment_pdf')) {
    function gi_build_payment_pdf($payment)
    {
        $CI =& get_instance();
        $CI->load->helper('pdf');
        if (!function_exists('payment_pdf')) { return ''; }

        // Εμπλουτισμός payment με invoice_data
        if (!isset($payment->invoiceid) && isset($payment->invoice_id)) {
            $payment->invoiceid = $payment->invoice_id;
        }
        if (isset($payment->invoiceid)) {
            $CI->load->model('invoices_model');
            $invoice = $CI->invoices_model->get($payment->invoiceid);
            if ($invoice) {
                $payment->invoice_data = $invoice;
                $payment->invoice      = $invoice;
            }
        }

        $pdf = payment_pdf($payment);
        $tmp = tempnam(sys_get_temp_dir(), 'pdf-pay-');
        $path = $tmp . '.pdf';
        @unlink($tmp);
        if (method_exists($pdf, 'Output')) {
            $pdf->Output($path, 'F');
            return $path;
        }
        return '';
    }
}

// Καταχώρηση του provider μας (όπως στο Delivery Notes παράδειγμα)
//register_merge_fields(GUESTINVOICES_MODULE . '/merge_fields/Guestinvoices_primary_merge_fields');

// Προβολή στο UI των Available Merge Fields
hooks()->add_filter('available_merge_fields', function($available) {
    $available[] = [
        'name'      => 'Primary Contact Email',
        'key'       => '{primary_contact_email}',
        'available' => ['invoice','estimate','credit_note'],
    ];
    return $available;
});


// --- Primary Contact Email για Invoice ---
hooks()->add_filter('invoice_merge_fields', function($fields, $invoice_id) {
    $CI =& get_instance();
    $email = '';
    $clientId = null;

    gi_debug_log('invoice_merge_fields START id=' . (int)$invoice_id);

    $CI->load->model('invoices_model');
    $CI->load->model('clients_model');

    $inv = $CI->invoices_model->get($invoice_id);
    if ($inv) {
        $clientId = (int)$inv->clientid;
        gi_debug_log('invoice_merge_fields invoice found, clientid=' . $clientId);

        if ($clientId) {
            $pid = get_primary_contact_user_id($clientId);
            gi_debug_log('invoice_merge_fields primary_contact_id=' . (int)$pid);

            if ($pid) {
                $c = $CI->clients_model->get_contact($pid);
                if ($c) {
                    $email = $c->email ?? '';
                    gi_debug_log('invoice_merge_fields contact email="' . $email . '"');
                } else {
                    gi_debug_log('invoice_merge_fields contact NOT found for pid='.(int)$pid);
                }
            }
        }
    } else {
        gi_debug_log('invoice_merge_fields invoice NOT found');
    }

    $fields['{primary_contact_email}'] = $email;
    gi_debug_log('invoice_merge_fields END; set {primary_contact_email}="' . $email . '"');

    return $fields;
}, 10, 2);


// --- Primary Contact Email για Estimate ---
hooks()->add_filter('estimate_merge_fields', function($fields, $estimate_id) {
    $CI =& get_instance();
    $email = '';
    $CI->load->model('estimates_model');
    $CI->load->model('clients_model');

    $est = $CI->estimates_model->get($estimate_id);
    if ($est && !empty($est->clientid)) {
        $pid = get_primary_contact_user_id($est->clientid);
        if ($pid) {
            $c = $CI->clients_model->get_contact($pid);
            if ($c && !empty($c->email)) {
                $email = $c->email;
            }
        }
    }
    $fields['{primary_contact_email}'] = $email;
    return $fields;
}, 10, 2);

// --- Primary Contact Email για Credit Note ---
hooks()->add_filter('credit_note_merge_fields', function($fields, $credit_note_id) {
    $CI =& get_instance();
    $email = '';
    $CI->load->model('credit_notes_model');
    $CI->load->model('clients_model');

    $cn = $CI->credit_notes_model->get($credit_note_id);
    if ($cn && !empty($cn->clientid)) {
        $pid = get_primary_contact_user_id($cn->clientid);
        if ($pid) {
            $c = $CI->clients_model->get_contact($pid);
            if ($c && !empty($c->email)) {
                $email = $c->email;
            }
        }
    }
    $fields['{primary_contact_email}'] = $email;
    return $fields;
}, 10, 2);

// --- Να φαίνεται και στο UI των διαθέσιμων merge fields ---
hooks()->add_filter('available_merge_fields', function($groups) {
    // Θα το βάλουμε στην "other" ομάδα για να εμφανίζεται στο UI
    $groups[] = [
        'other' => [
            [
                'name'      => 'Primary Contact Email',
                'key'       => '{primary_contact_email}',
                'available' => ['invoice','estimate','credit_note'],
            ],
        ]
    ];
    return $groups;
});


// =======================
// GI: Add "Email" column (Primary Contact) after "Customer"
// για Invoices / Estimates / Credit Notes tables
// Με subquery στα $aColumns (sortable/searchable) και ασφαλές render.
// =======================

// 1) Προσθέτουμε το subquery στο $aColumns ΑΜΕΣΩΣ ΜΕΤΑ την “Customer” στήλη (ΧΩΡΙΣ alias)
if (!function_exists('gi_sql_columns_insert_email_after_customer')) {
    function gi_sql_columns_insert_email_after_customer($aColumns, $type = 'invoices')
    {
        // Εντοπισμός της στήλης του πελάτη
        $idx = -1;
        foreach ($aColumns as $i => $col) {
            if (!is_string($col)) { continue; }
            $c = strtolower($col);
            if (strpos($c, 'clients.company') !== false || preg_match('/\bas\s+company\b/', $c)) {
                $idx = $i;
                break;
            }
        }

        if ($type === 'invoices') {
            $emailExpr = '(SELECT c.email FROM ' . db_prefix() . 'contacts c WHERE c.userid = ' . db_prefix() . 'invoices.clientid AND c.is_primary = 1 LIMIT 1)';
        } elseif ($type === 'estimates') {
            $emailExpr = '(SELECT c.email FROM ' . db_prefix() . 'contacts c WHERE c.userid = ' . db_prefix() . 'estimates.clientid AND c.is_primary = 1 LIMIT 1)';
        } else { // credit notes
            $emailExpr = '(SELECT c.email FROM ' . db_prefix() . 'contacts c WHERE c.userid = ' . db_prefix() . 'creditnotes.clientid AND c.is_primary = 1 LIMIT 1)';
        }

        if ($idx === -1) { $aColumns[] = $emailExpr; }
        else { array_splice($aColumns, $idx + 1, 0, [$emailExpr]); }

        return $aColumns;
    }
}
hooks()->add_filter('invoices_table_sql_columns', function ($a) { return gi_sql_columns_insert_email_after_customer($a, 'invoices'); });
hooks()->add_filter('estimates_table_sql_columns', function ($a) { return gi_sql_columns_insert_email_after_customer($a, 'estimates'); });

// 2) Προσθέτουμε label “Email” στη λίστα των column headers ακριβώς μετά το “Customer”

if (!function_exists('gi_columns_insert_after_customer')) {
    function gi_columns_insert_after_customer($columns)
    {
        $customerIndex = -1;

        foreach ($columns as $i => $col) {
            // Κάνε stringify Ο,ΤΙ κι αν είναι (string/array/μικτό)
            $label = '';
            if (is_string($col)) {
                $label = $col;
            } elseif (is_array($col)) {
                // δοκίμασε κοινά κλειδιά
                if (isset($col['name']) && is_string($col['name'])) {
                    $label = $col['name'];
                } elseif (isset($col[0]) && is_string($col[0])) {
                    $label = $col[0];
                } else {
                    // fallback: ενώσε ό,τι μπορεί να εκτυπωθεί
                    $parts = [];
                    foreach ($col as $k => $v) {
                        if (is_string($v)) $parts[] = $v;
                        elseif (is_array($v) && isset($v['name']) && is_string($v['name'])) $parts[] = $v['name'];
                    }
                    $label = implode(' ', $parts);
                }
            }

            $hay = mb_strtolower(strip_tags((string)$label));
            if ($hay !== '' && (strpos($hay, 'customer') !== false || strpos($hay, 'client') !== false || strpos($hay, 'company') !== false)) {
                $customerIndex = (int)$i;
                break;
            }
        }

        $newCol = _l('email');

        if ($customerIndex >= 0) {
            array_splice($columns, $customerIndex + 1, 0, [$newCol]);
        } else {
            // fallback: βάλε στο τέλος (σε ακραίες περιπτώσεις)
            $columns[] = $newCol;
        }

        return $columns;
    }
}

hooks()->add_filter('invoices_table_columns', 'gi_columns_insert_after_customer');
hooks()->add_filter('estimates_table_columns', 'gi_columns_insert_after_customer');
hooks()->add_filter('credit_notes_table_columns', 'gi_columns_insert_after_customer');

// 3) Γέμισμα του κελιού Email στο render, ευθυγραμμισμένο με τη θέση του “Customer”
if (!function_exists('gi_rowdata_insert_email_after_customer')) {
    function gi_rowdata_insert_email_after_customer($row, $aRow)
    {
        static $re = null;
        static $emailCache = [];

        if ($re === null) {
            // /admin/clients/client/123
            $re = '#/admin/clients/client/(\d+)#';
        }

        // Βρες το κελί “Customer”
        $insertPos = -1;
        $clientId  = null;
        foreach ($row as $i => $cellHtml) {
            if (is_string($cellHtml) && strpos($cellHtml, '/admin/clients/client/') !== false) {
                $insertPos = $i + 1; // βάζουμε ακριβώς μετά
                if (preg_match($re, $cellHtml, $m)) {
                    $clientId = (int)$m[1];
                }
                break;
            }
        }

        if ($insertPos < 0) {
            // fallback στο τέλος
            $row[] = '';
            return $row;
        }

        // Προσπάθησε να διαβάσεις την αντίστοιχη τιμή από το ίδιο offset στο $aRow (ευθυγραμμισμένο με $aColumns)
        $email = '';
        if (array_key_exists($insertPos, $aRow)) {
            $email = trim((string)$aRow[$insertPos]);
        }

        // Fallback DB/cached lookup αν άδειο (σε τυχόν διαφορετική διάταξη)
        if ($email === '' && $clientId) {
            if (!isset($emailCache[$clientId])) {
                $CI = &get_instance();
                $CI->db->select('email')->from(db_prefix().'contacts')->where(['userid'=>$clientId,'is_primary'=>1])->limit(1);
                $r = $CI->db->get()->row();
                $emailCache[$clientId] = $r ? (string)$r->email : '';
            }
            $email = $emailCache[$clientId];
        }

        array_splice($row, $insertPos, 0, [$email]);
        return $row;
    }
}
hooks()->add_filter('invoices_table_row_data',      'gi_rowdata_insert_email_after_customer', 10, 2);
hooks()->add_filter('estimates_table_row_data',     'gi_rowdata_insert_email_after_customer', 10, 2);

// =======================
// CREDIT NOTES – Primary Contact Email column (searchable & sortable)
// =======================

// 1) SQL: πρόσθεσε alias gi_primary_email (δουλεύει χωρίς JOIN)
if (!function_exists('gi_cn_sql_email')) {
    function gi_cn_sql_email($select)
    {
        $select[] = '(SELECT c.email
                      FROM ' . db_prefix() . 'contacts c
                      WHERE c.userid = ' . db_prefix() . 'creditnotes.clientid
                        AND c.is_primary = 1
                      LIMIT 1) AS gi_primary_email';
        return $select;
    }
}
// ορισμένα builds χρησιμοποιούν αυτό:
hooks()->add_filter('credit_notes_table_sql_columns', 'gi_cn_sql_email');
// …ενώ σε άλλα χρειάζεται αυτό:
hooks()->add_filter('credit_notes_table_additional_columns', 'gi_cn_sql_email');


// 2) Δήλωσε στο DataTables ότι υπάρχει extra column (για να είναι searchable/sortable)
if (!function_exists('gi_cn_dt_columns_register')) {
    function gi_cn_dt_columns_register($columns)
    {
        // Σε κάποια builds, ο core διαβάζει από εδώ τις “επιπλέον” στήλες
        // ώστε να τις συμπεριλάβει στο ORDER BY / search.
        $columns[] = 'gi_primary_email';
        return $columns;
    }
}
// Δίνουμε και τα 2 πιθανά hooks ώστε να “πιαστεί” όπου κι αν κοιτάει ο core.
hooks()->add_filter('credit_notes_table_additional_columns', 'gi_cn_dt_columns_register');
hooks()->add_filter('credit_notes_table_sql_columns', 'gi_cn_dt_columns_register');


// 3) Header: βάλε "Email" ακριβώς μετά το "Customer"
if (!function_exists('gi_cn_columns_after_customer')) {
    function gi_cn_columns_after_customer($columns)
    {
        $customerIndex = -1;

        foreach ($columns as $i => $col) {
            $label = '';
            if (is_string($col)) {
                $label = $col;
            } elseif (is_array($col)) {
                if (isset($col['name']) && is_string($col['name'])) {
                    $label = $col['name'];
                } elseif (isset($col[0]) && is_string($col[0])) {
                    $label = $col[0];
                } else {
                    $parts = [];
                    foreach ($col as $k => $v) {
                        if (is_string($v)) $parts[] = $v;
                        elseif (is_array($v) && isset($v['name']) && is_string($v['name'])) $parts[] = $v['name'];
                    }
                    $label = implode(' ', $parts);
                }
            }

            $hay = mb_strtolower(strip_tags((string)$label));
            if ($hay !== '' && (strpos($hay, 'customer') !== false || strpos($hay, 'client') !== false || strpos($hay, 'company') !== false)) {
                $customerIndex = (int)$i;
                break;
            }
        }

        $newCol = _l('email');

        if ($customerIndex >= 0) {
            array_splice($columns, $customerIndex + 1, 0, [$newCol]);
        } else {
            $columns[] = $newCol;
        }

        return $columns;
    }
}

hooks()->add_filter('credit_notes_table_columns', 'gi_cn_columns_after_customer');


// 4) Row: βάλε την τιμή μετά το κελί του πελάτη
if (!function_exists('gi_cn_rowdata_after_customer')) {
    function gi_cn_rowdata_after_customer($row, $aRow)
    {
        $email = isset($aRow['gi_primary_email']) ? trim((string)$aRow['gi_primary_email']) : '';

        // Βρες το κελί του πελάτη (link προς /admin/clients/client/)
        $insertPos = -1;
        foreach ($row as $i => $cellHtml) {
            if (is_string($cellHtml) && strpos($cellHtml, '/admin/clients/client/') !== false) {
                $insertPos = $i + 1;
                break;
            }
        }

        if ($insertPos < 0) {
            $row[] = $email; // fallback: στο τέλος
        } else {
            array_splice($row, $insertPos, 0, [$email]);
        }
        return $row;
    }
}
hooks()->add_filter('credit_notes_table_row_data', 'gi_cn_rowdata_after_customer', 10, 2);
