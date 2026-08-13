<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: PaymentsOnAccount
Description: Manage customer Receipts and apply payments to invoices (eAD-CRM module)
Version: 3.1.1
Author: eAdvertise.eu
Author URI: https://www.eadvertise.eu
*/

define('PAYMENTS_ON_ACCOUNT_MODULE_NAME', 'paymentsonaccount');
define('PAYMENTS_ON_ACCOUNT_MODULE_VERSION', '3.1.1');

/** Activation: migrations + email template */
register_activation_hook(PAYMENTS_ON_ACCOUNT_MODULE_NAME, 'paymentsonaccount_module_activate');
function paymentsonaccount_module_activate()
{
    $CI =& get_instance();

    require_once(__DIR__ . '/migrations/001_create_receipts_table.php');
    require_once(__DIR__ . '/migrations/003_add_payment_fields_to_receipts.php');
    
	
    (new Migration_Create_Receipts_Table())->up();
    (new Migration_Add_Payment_Fields_To_Receipts())->up();
    paymentsonaccount_ensure_client_payment_modes_table();
	add_option('receipt_number_prefix', 'RCPT-');
	add_option('next_receipt_number', 1);
	add_option('receipt_auto_send_email', '0');
	paymentsonaccount_apply_3_1_1_database_updates();


    paymentsonaccount_register_email_template();

	// === Copy my_invoicepdf.php to all PDF themes on activation ===
	try {
		$source = module_dir_path(PAYMENTS_ON_ACCOUNT_MODULE_NAME, 'assets/pdf/my_invoicepdf.php');

		if (is_file($source) && is_readable($source)) {
			$themesDir = APPPATH . 'views/themes/';
			if (is_dir($themesDir)) {
				$themes = array_values(array_filter(scandir($themesDir), function ($d) use ($themesDir) {
					return $d !== '.' && $d !== '..' && is_dir($themesDir . $d);
				}));

				$copied = 0;
				foreach ($themes as $theme) {
					$viewsDir = $themesDir . $theme . '/views/';
					if (!is_dir($viewsDir)) {
						continue;
					}

					$dest = $viewsDir . 'my_invoicepdf.php';

					// Αν υπάρχει ήδη και δεν θες overwrite, κάνε continue.
					// Για σιγουριά το κάνουμε overwrite (κρατάμε backup μια φορά).
					if (file_exists($dest) && is_writable($viewsDir)) {
						@copy($dest, $viewsDir . 'my_invoicepdf.backup.' . date('Ymd_His') . '.php');
					}

					$ok = @copy($source, $dest);
					if (!$ok) {
						// Αν copy αποτύχει, προσπάθησε με file_get_contents/put_contents
						$data = @file_get_contents($source);
						if ($data !== false) {
							$ok = (bool)@file_put_contents($dest, $data);
						}
					}

					if ($ok) { $copied++; }
				}

				if ($copied > 0) {
					log_activity('[paymentsonaccount] Installed my_invoicepdf.php to ' . $copied . ' theme(s).');
				} else {
					log_activity('[paymentsonaccount] my_invoicepdf.php not installed to any theme (check permissions/paths).');
				}
			} else {
				log_activity('[paymentsonaccount] Themes directory not found: ' . $themesDir);
			}
		} else {
			log_activity('[paymentsonaccount] Source template not found or unreadable: ' . $source);
		}
	} catch (Throwable $e) {
		log_activity('[paymentsonaccount] Error copying my_invoicepdf.php: ' . $e->getMessage());
	}
	
}


hooks()->add_action('admin_init', 'paymentsonaccount_apply_3_1_1_database_updates');

if (!function_exists('paymentsonaccount_emit_event')) {
    function paymentsonaccount_emit_event($eventName, $receiptId, array $extra = [], $receiptSnapshot = null)
    {
        $CI = &get_instance();
        $receiptId = (int) $receiptId;
        if ($receiptId < 1) {
            return;
        }

        if ($receiptSnapshot === null) {
            $CI->load->model('paymentsonaccount/payments_on_account_model');
            $receiptSnapshot = $CI->payments_on_account_model->get_receipt($receiptId);
        }
        if (!$receiptSnapshot) {
            return;
        }

        $applications = [];
        $bridge = db_prefix() . 'receipt_invoice_applications';
        if ($CI->db->table_exists($bridge)) {
            $applications = $CI->db->where('receipt_id', $receiptId)->get($bridge)->result_array();
        }

        hooks()->do_action('paymentsonaccount_event', array_merge([
            'event_name'   => (string) $eventName,
            'receipt_id'   => $receiptId,
            'receipt'      => $receiptSnapshot,
            'applications' => $applications,
            'occurred_at'  => date('c'),
        ], $extra));
    }
}


function paymentsonaccount_ensure_client_payment_modes_table()
{
    $CI =& get_instance();
    $tbl = db_prefix() . 'poa_client_payment_modes';

    if ($CI->db->table_exists($tbl)) {
        return;
    }

    $CI->db->query("CREATE TABLE IF NOT EXISTS `{$tbl}` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `client_id` INT NOT NULL,
      `payment_mode_id` INT NOT NULL,
      `created_at` DATETIME NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uniq_client_mode` (`client_id`,`payment_mode_id`),
      KEY `idx_client_id` (`client_id`),
      KEY `idx_mode_id` (`payment_mode_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}


function paymentsonaccount_prefix_receipt_numbers_with_rec()
{
    $CI =& get_instance();
    $tbl = db_prefix() . 'receipts';

    if (!$CI->db->table_exists($tbl) || !$CI->db->field_exists('receipt_number', $tbl)) {
        return 0;
    }

    $prefix = 'REC-';
    $like   = $CI->db->escape_like_str($prefix) . '%';

    $CI->db->where('receipt_number IS NOT NULL', null, false);
    $CI->db->where("TRIM(receipt_number) != ''", null, false);
    $CI->db->not_like('receipt_number', $prefix, 'after');
    $count = (int) $CI->db->count_all_results($tbl);

    if ($count === 0) {
        return 0;
    }

    $CI->db->query(
        "UPDATE `{$tbl}`
         SET `receipt_number` = CONCAT('REC-', `receipt_number`)
         WHERE `receipt_number` IS NOT NULL
           AND TRIM(`receipt_number`) != ''
           AND `receipt_number` NOT LIKE " . $CI->db->escape($like)
    );

    return $count;
}


function paymentsonaccount_table_has_column($table, $column)
{
    $CI =& get_instance();
    return $CI->db->query("SHOW COLUMNS FROM `{$table}` LIKE " . $CI->db->escape($column))->num_rows() > 0;
}

function paymentsonaccount_sync_module_database_version($version = PAYMENTS_ON_ACCOUNT_MODULE_VERSION)
{
    $CI =& get_instance();

    if (get_option('paymentsonaccount_module_version') !== $version) {
        update_option('paymentsonaccount_module_version', $version);
    }

    $modulesTable = db_prefix() . 'modules';
    if (!$CI->db->table_exists($modulesTable)) {
        return;
    }

    $versionColumns = ['installed_version', 'version'];
    $moduleColumns  = ['module_name', 'module'];
    $moduleColumn   = null;

    foreach ($moduleColumns as $column) {
        if (paymentsonaccount_table_has_column($modulesTable, $column)) {
            $moduleColumn = $column;
            break;
        }
    }

    if ($moduleColumn === null) {
        return;
    }

    foreach ($versionColumns as $column) {
        if (paymentsonaccount_table_has_column($modulesTable, $column)) {
            $CI->db->where($moduleColumn, PAYMENTS_ON_ACCOUNT_MODULE_NAME)
                   ->update($modulesTable, [$column => $version]);
        }
    }
}

function paymentsonaccount_apply_3_0_0_database_updates()
{
    paymentsonaccount_ensure_client_payment_modes_table();
    paymentsonaccount_prefix_receipt_numbers_with_rec();

    if (get_option('receipt_number_prefix') !== 'REC-') {
        update_option('receipt_number_prefix', 'REC-');
    }

    $current = get_option('paymentsonaccount_module_version');
    if (!$current || version_compare($current, '3.0.0', '<')) {
        paymentsonaccount_sync_module_database_version('3.0.0');
    }
}

function paymentsonaccount_apply_3_0_1_database_updates()
{
    paymentsonaccount_apply_3_0_0_database_updates();

    $current = get_option('paymentsonaccount_module_version');
    if (!$current || version_compare($current, '3.0.1', '<')) {
        paymentsonaccount_sync_module_database_version('3.0.1');
    }
}

function paymentsonaccount_apply_3_0_2_database_updates()
{
    paymentsonaccount_apply_3_0_1_database_updates();
    paymentsonaccount_register_email_template();

    $current = get_option('paymentsonaccount_module_version');
    if (!$current || version_compare($current, '3.0.2', '<')) {
        paymentsonaccount_sync_module_database_version('3.0.2');
    }
}

function paymentsonaccount_apply_3_0_3_database_updates()
{
    paymentsonaccount_apply_3_0_2_database_updates();
    paymentsonaccount_register_email_template();

    $current = get_option('paymentsonaccount_module_version');
    if (!$current || version_compare($current, '3.0.3', '<')) {
        paymentsonaccount_sync_module_database_version('3.0.3');
    }
}

function paymentsonaccount_apply_3_0_4_database_updates()
{
    paymentsonaccount_apply_3_0_3_database_updates();
    paymentsonaccount_register_email_template();

    $current = get_option('paymentsonaccount_module_version');
    if (!$current || version_compare($current, '3.0.4', '<')) {
        paymentsonaccount_sync_module_database_version('3.0.4');
    }
}

function paymentsonaccount_apply_3_1_0_database_updates()
{
    paymentsonaccount_apply_3_0_4_database_updates();
    paymentsonaccount_register_email_template();

    $current = get_option('paymentsonaccount_module_version');
    if (!$current || version_compare($current, '3.1.0', '<')) {
        paymentsonaccount_sync_module_database_version('3.1.0');
    }
}

function paymentsonaccount_apply_3_1_1_database_updates()
{
    paymentsonaccount_apply_3_1_0_database_updates();
    paymentsonaccount_register_email_template();
    paymentsonaccount_sync_module_database_version(PAYMENTS_ON_ACCOUNT_MODULE_VERSION);
}

/** Load module language safely (works across versions) */
hooks()->add_action('app_init', function () {
    $CI = &get_instance();
    $CI->load->helper('modules');

    $lang = get_option('active_language');
    if (!$lang) { $lang = 'english'; }

    $modulePath = module_dir_path(PAYMENTS_ON_ACCOUNT_MODULE_NAME);
    $CI->load->add_package_path($modulePath);
    // expects: modules/paymentsonaccount/language/<lang>/paymentsonaccount_lang.php
    $CI->lang->load('paymentsonaccount', $lang);
    $CI->load->remove_package_path($modulePath);
});

hooks()->add_action('admin_init', function () {
    $CI = &get_instance();

    /* Sidebar menu: κάτω από Sales (όπως στο άλλο module) */
    if (staff_can('view_own', 'payments_on_account') || staff_can('view', 'payments_on_account')) {
        $CI->app_menu->add_sidebar_children_item('sales', [
            'slug'     => PAYMENTS_ON_ACCOUNT_MODULE_NAME,
            'name'     => _l('Receipts') ?: 'Payments On Account',
            'icon'     => '', // άσε κενό ή βάλε 'fa fa-credit-card'
            'href'     => admin_url('paymentsonaccount'),
            'position' => 15,
        ]);
    }

    /* Permissions (ίδια λογική με το άλλο module) */
    $cap = [];
    $cap['capabilities'] = [
        'view'     => _l('permission_view') . ' (' . _l('permission_global') . ')',
        'view_own' => _l('permission_view_own'),
        'create'   => _l('permission_create'),
        'edit'     => _l('permission_edit'),
        'delete'   => _l('permission_delete'),
    ];
    register_staff_capabilities('payments_on_account', $cap, _l('payments_on_account_menu') ?: 'Payments On Account');

    /* Settings tab (backward/forward compatible) */
    $settings_tab = [
        'name'     => _l('payments_on_account_settings') ?: 'Payments On Account',
        // ΣΗΜΑΝΤΙΚΟ: path σε view μέσα στο module (δες Β παρακάτω)
        'view'     => PAYMENTS_ON_ACCOUNT_MODULE_NAME . '/admin/' . PAYMENTS_ON_ACCOUNT_MODULE_NAME . '/settings',
        'position' => 20,
        'icon'     => 'fa fa-credit-card',
    ];

    if (method_exists($CI->app, 'add_settings_section_child')) {
        // Νεότερες εκδόσεις – βάλ’ το κάτω από Finance (ή ό,τι group θες)
        $CI->app->add_settings_section_child('finance', PAYMENTS_ON_ACCOUNT_MODULE_NAME, $settings_tab);
    } else {
        // Παλαιότερες εκδόσεις
        $CI->app_tabs->add_settings_tab(PAYMENTS_ON_ACCOUNT_MODULE_NAME, $settings_tab);
    }
}, PHP_INT_MAX);


/** Staff permissions */
hooks()->add_filter('staff_permissions', 'paymentsonaccount_register_permissions');
function paymentsonaccount_register_permissions($permissions)
{
    $permissions['payments_on_account'] = [
        'name'         => 'Payments On Account',
        'capabilities' => ['view', 'create', 'edit', 'delete'],
    ];
    return $permissions;
}

hooks()->add_action('admin_init', function () {
    $CI = &get_instance();

    // Receipts tab
    $CI->app_tabs->add_customer_profile_tab('poa_payment_modes', [
        'name'     => _l('poa_payment_modes_tab') ?: 'Payment Modes',
        'icon'     => 'fa fa-credit-card',
        'view'     => 'paymentsonaccount/admin/clients/groups/poa_payment_modes',
        'position' => 24,
        'badge'    => [],
    ]);

    $CI->app_tabs->add_customer_profile_tab('poa_receipts', [
        'name'     => _l('poa_receipts_tab') ?: 'Receipts',
        'icon'     => 'fa fa-receipt',
        'view'     => 'paymentsonaccount/admin/clients/groups/poa_receipts',
        'position' => 25,
        'badge'    => [],
    ]);

    // Statement (Receipts) tab
    $CI->app_tabs->add_customer_profile_tab('poa_statement', [
        'name'     => _l('poa_statement_tab') ?: 'Statement (Receipts)',
        'icon'     => 'fa fa-list',
        'view'     => 'paymentsonaccount/admin/clients/statement_receipts',
        'position' => 26,
        'badge'    => [],
    ]);
});

hooks()->add_filter('customers_profile_tab_badge', function ($data) {
    if ($data['feature'] === 'poa_receipts') {
        $CI = &get_instance();
        $customerid = (int)$data['customer_id'];
        $count = $CI->db->where('client_id', $customerid)->count_all_results(db_prefix().'receipts');
        if ($count > 0) {
            $data['badge'] = ['value' => $count, 'color' => '', 'type' => 'default'];
        }
    }
    return $data;
});


/** Email template registration */
function paymentsonaccount_register_email_template()
{
    $CI = &get_instance();
    $table = db_prefix() . 'emailtemplates';
    $slug  = 'receipt-sent-to-customer';
    $languages = ['english'];
    $activeLanguage = get_option('active_language');
    if ($activeLanguage && !in_array($activeLanguage, $languages, true)) {
        $languages[] = $activeLanguage;
    }

    foreach ($languages as $language) {
        $template = [
            'type'     => 'invoice',
            'slug'     => $slug,
            'language' => $language,
            'name'     => 'Receipt Sent to Customer',
            'subject'  => 'New Payment Receipt {receipt_number}',
            'fromname' => '{companyname} | CRM',
            'message'  => 'Dear {client_name},<br><br>Your payment receipt is attached.<br><br>Receipt Number: {receipt_number}<br>Total Paid: {total_amount}<br>Date: {receipt_date}<br><br>Thank you,<br>{companyname}',
        ];

        if ($CI->db->field_exists('active', $table)) {
            $template['active'] = 1;
        }

        $existing = $CI->db->where('slug', $slug)
                           ->where('language', $language)
                           ->limit(1)
                           ->get($table)
                           ->row();

        if ($existing) {
            $update = [
                'type' => 'invoice',
                'name' => !empty($existing->name) ? $existing->name : $template['name'],
            ];
            if (trim((string)($existing->subject ?? '')) === '') {
                $update['subject'] = $template['subject'];
            }
            if (trim((string)($existing->fromname ?? '')) === '') {
                $update['fromname'] = $template['fromname'];
            }
            if (trim(strip_tags((string)($existing->message ?? ''))) === '') {
                $update['message'] = $template['message'];
            }
            if ($CI->db->field_exists('active', $table) && isset($existing->active) && (int)$existing->active === 0) {
                $update['active'] = 1;
            }
            $CI->db->where('emailtemplateid', (int)$existing->emailtemplateid)->update($table, $update);
            continue;
        }

        $CI->db->insert($table, $template);
    }

    $CI->db->insert($table, $template);
}


/**
 * Auto-create Receipt when a default Perfex Payment is added
 * Hook signature: after_payment_added($payment_id)
 */
hooks()->add_action('after_payment_added', 'paymentsonaccount_create_receipt_after_payment');
hooks()->add_action('after_cron_run', 'paymentsonaccount_cron_repair_receipt_core_payments');


function paymentsonaccount_cron_repair_receipt_core_payments()
{
    static $ran = false;
    if ($ran) {
        return;
    }
    $ran = true;

    $CI = &get_instance();
    $CI->load->model('paymentsonaccount/payments_on_account_model');
    $CI->payments_on_account_model->cron_repair_receipt_core_payments();
}

function paymentsonaccount_create_receipt_after_payment($payment_id)
{
    $CI = &get_instance();
    $CI->load->model('payments_model');
    $CI->load->model('invoices_model');
    $CI->load->model('paymentsonaccount/payments_on_account_model');

    $payment = $CI->payments_model->get($payment_id);
    if (!$payment) { return; }

    // Skip αν προέρχεται από δικό μας receipt flow (marker σε note/transactionid)
    if (!empty($payment->note) && stripos($payment->note, 'via Receipt #') !== false) {
        return;
    }
    if (!empty($payment->note) && stripos($payment->note, 'Applied from Receipt #') !== false) {
        return;
    }
    if (!empty($payment->transactionid) && stripos($payment->transactionid, 'RCPT-') === 0) {
        return;
    }

    // --- ΝΕΟΣ ΕΛΕΓΧΟΣ: Υπάρχει ήδη εφαρμογή στο receipt_invoice_applications για αυτό το payment_id; ---
    $exists = $CI->db->where('payment_record_id', (int)$payment_id)
                     ->limit(1)
                     ->get(db_prefix().'receipt_invoice_applications')
                     ->row();
    if ($exists) {
        return; // ήδη έχει γίνει mapping ➜ μην ξαναφτιάχνεις receipt
    }

    if ($CI->db->field_exists('source_payment_id', db_prefix().'receipts')) {
        $existingReceipt = $CI->db->where('source_payment_id', (int)$payment_id)
                                  ->limit(1)
                                  ->get(db_prefix().'receipts')
                                  ->row();
        if ($existingReceipt) {
            if (!$CI->payments_on_account_model->receipt_email_was_sent((int)$existingReceipt->id)) {
                $CI->payments_on_account_model->send_receipt_email((int)$existingReceipt->id);
            }
            return;
        }
    }

    // Πάρε το invoice
    $invoice = $CI->invoices_model->get($payment->invoiceid);
    if (!$invoice) { return; }

    $client_id = (int)$invoice->clientid;

    // Δημιουργία receipt
    $receipt_id = $CI->payments_on_account_model->create_receipt(
        $client_id,
        (float)$payment->amount,
        $payment->paymentmode,
        [$payment->invoiceid],
        'Auto-generated from Payment ID: ' . $payment->paymentid,
        date('Y-m-d', strtotime($payment->date)),
        null,
        $payment->transactionid,
        false,
        $payment_id
    );

    $sent = $CI->payments_on_account_model->send_receipt_email($receipt_id);

    paymentsonaccount_emit_event('receipt_created', $receipt_id, [
        'source'      => 'core_payment',
        'payment_id'  => (int) $payment_id,
        'email_sent'  => (bool) $sent,
    ]);
    if ($sent) {
        paymentsonaccount_emit_event('receipt_email', $receipt_id, [
            'source' => 'core_payment',
            'payment_id' => (int) $payment_id,
            'email_sent' => true,
        ]);
    }

    log_activity('POA Auto Receipt Created [Receipt ID: ' . $receipt_id . ', Payment ID: ' . $payment_id . ']');
    log_activity('POA Receipt Email ' . ($sent ? 'Sent' : 'Failed') . ' [Receipt ID: ' . $receipt_id . ', Payment ID: ' . $payment_id . ']');
}


hooks()->add_action('admin_init', function () {
    if (get_option('poa_receipts_regenerated') != '1') {
        require_once(__DIR__ . '/migrations/006_regenerate_receipts_from_payments.php');
        (new Migration_Regenerate_Receipts_From_Payments())->up();
    }
});

// Hide core "Payments" & "Statement" tabs from Customer Profile
hooks()->add_action('admin_init', function () {
    // Τρέξε όσο πιο αργά γίνεται για να «κερδίσουμε» άλλα modules
    hooks()->add_filter('customers_profile_tabs', 'poa_hide_core_customer_tabs', 100);
    // Σε μερικές εκδόσεις/θέματα χρησιμοποιείται και αυτό το όνομα
    hooks()->add_filter('customer_profile_tabs', 'poa_hide_core_customer_tabs', 100);
});

/**
 * Remove core Payments/Statement tabs from the customer profile.
 * Συμβατό με διάφορες δομές tabs (associative ή indexed arrays).
 */
function poa_hide_core_customer_tabs($tabs)
{
    // 1) Αν είναι associative με keys 'payments' / 'statement'
    if (isset($tabs['payments']))  { unset($tabs['payments']); }
    if (isset($tabs['statement'])) { unset($tabs['statement']); }

    // 2) Διαπέρασε και καθάρισε ό,τι μοιάζει με payments/statement σε indexed μορφή
    foreach ($tabs as $key => $tab) {
        if (is_array($tab)) {
            $slug = $tab['slug'] ?? ($tab['view'] ?? '');
            $url  = $tab['url']  ?? '';
            $view = $tab['view'] ?? '';

            // core περιπτώσεις που έχουμε δει
            $isPayments  = in_array($slug, ['payments'], true)
                        || in_array($view, ['payments'], true)
                        || (is_string($url)  && (strpos($url, '#tab_client_payments') !== false || strpos($url, '#tab_payments') !== false));

            $isStatement = in_array($slug, ['statement'], true)
                        || in_array($view, ['statement'], true)
                        || (is_string($url)  && (strpos($url, '#tab_client_statement') !== false || strpos($url, '#tab_statement') !== false));

            if ($isPayments || $isStatement) {
                unset($tabs[$key]);
            }
        } else {
            // Σε περίπτωση που για κάποιο λόγο το στοιχείο δεν είναι array
            if (in_array($key, ['payments','statement'], true)) {
                unset($tabs[$key]);
            }
        }
    }

    return $tabs;
}


hooks()->add_action('admin_init', 'poa_register_credit_report_menu');

function poa_register_credit_report_menu()
{
    // Μόνο χρήστες με δικαίωμα προβολής reports
    if (!has_permission('reports', '', 'view')) {
        return;
    }

    $CI = &get_instance();

    // Ασφαλές label (πέφτει σε fallback αν λείπει το language line)
    $label = _l('poa_credit_balances_report');
    if ($label === 'poa_credit_balances_report') { // fallback if not translated
        $label = 'Client Credit Balances';
    }

    // Προσθήκη ως child κάτω από Reports
    $CI->app_menu->add_sidebar_children_item('reports', [
        'slug'     => 'client-credit-balances',                     // unique
        'name'     => $label,                                       // εμφανιζόμενο κείμενο
        'href'     => admin_url('paymentsonaccount/reports/credits'),
        'position' => 15,                                           // ρυθμίστε αν θες άλλη σειρά
        // (προαιρετικό) active state για το highlight του μενού
        'badge'    => [],                                           // χωρίς badge
    ]);
	$CI->app_menu->add_sidebar_children_item('reports', [
                'slug'     => 'reports-receipts',
                'name'     => _l('poa_receipts_report') ?: 'Receipts',
                'href'     => admin_url('paymentsonaccount/reports_receipts'),
                'position' => 16,
            ]);
}

	require_once(__DIR__ . '/migrations/007_add_receipt_invoice_applications.php');
    (new Migration_Add_receipt_invoice_applications())->up();
	require_once(__DIR__ . '/migrations/008_update_module_version.php');
	(new Migration_Update_module_version())->up();
	require_once(__DIR__ . '/migrations/200_update_to_2_0_0.php');
	(new Migration_Version_200())->up();
	$poaMigration300 = __DIR__ . '/migrations/300_update_to_3_0_0.php';
	if (is_file($poaMigration300)) {
		require_once($poaMigration300);
		if (class_exists('Migration_Version_300')) {
			(new Migration_Version_300())->up();
		}
	}
	$poaMigration301 = __DIR__ . '/migrations/301_update_to_3_0_1.php';
	if (is_file($poaMigration301)) {
		require_once($poaMigration301);
		if (class_exists('Migration_Version_301')) {
			(new Migration_Version_301())->up();
		}
	}
	$poaMigration302 = __DIR__ . '/migrations/302_update_to_3_0_2.php';
	if (is_file($poaMigration302)) {
		require_once($poaMigration302);
		if (class_exists('Migration_Version_302')) {
			(new Migration_Version_302())->up();
		}
	}
	$poaMigration303 = __DIR__ . '/migrations/303_update_to_3_0_3.php';
	if (is_file($poaMigration303)) {
		require_once($poaMigration303);
		if (class_exists('Migration_Version_303')) {
			(new Migration_Version_303())->up();
		}
	}
	$poaMigration304 = __DIR__ . '/migrations/304_update_to_3_0_4.php';
	if (is_file($poaMigration304)) {
		require_once($poaMigration304);
		if (class_exists('Migration_Version_304')) {
			(new Migration_Version_304())->up();
		}
	}
	$poaMigration310 = __DIR__ . '/migrations/310_update_to_3_1_0.php';
	if (is_file($poaMigration310)) {
		require_once($poaMigration310);
		if (class_exists('Migration_Version_310')) {
			(new Migration_Version_310())->up();
		}
	}
	$poaMigration311 = __DIR__ . '/migrations/311_update_to_3_1_1.php';
	if (is_file($poaMigration311)) {
		require_once($poaMigration311);
		if (class_exists('Migration_Version_311')) {
			(new Migration_Version_311())->up();
		}
	}


// === Override core "Attach Customer Statement" with paymentsonaccount custom PDF ===
// ΠΑΛΙΟ: hooks()->add_action('before_email_template_send', 'poa_attach_custom_statement_pdf');
hooks()->add_filter('before_email_template_send', 'poa_prevent_core_payment_email');
hooks()->add_filter('before_email_template_send', 'poa_attach_custom_statement_pdf');


/**
 * Prevents Perfex's core "payment recorded" customer email. The receipt module
 * sends its own receipt PDF instead, so this avoids one core email per invoice
 * when a single receipt is allocated across multiple invoices.
 */
function poa_prevent_core_payment_email($data)
{
    $context = poa_email_template_context($data);

    if (($context['slug'] ?? null) !== 'invoice-payment-recorded-to-customer') {
        return $data;
    }

    poa_send_receipt_from_core_payment_email((int)($context['rel_id'] ?? 0));

    if (is_array($data) && isset($data['template']) && is_object($data['template'])) {
        $data['template']->prevent_sending = true;
    } elseif (is_array($data) && isset($data['mail_template']) && is_object($data['mail_template'])) {
        $data['mail_template']->prevent_sending = true;
    } elseif (is_object($data)) {
        $data->prevent_sending = true;
    }

    return $data;
}

function poa_send_receipt_from_core_payment_email(int $invoice_id): void
{
    static $processedPayments = [];

    if ($invoice_id <= 0) {
        return;
    }

    $CI = &get_instance();
    $paymentsTable = db_prefix().'invoicepaymentrecords';
    if (!$CI->db->table_exists($paymentsTable)) {
        return;
    }

    $payment = $CI->db->where('invoiceid', $invoice_id)
                      ->order_by('id', 'DESC')
                      ->limit(1)
                      ->get($paymentsTable)
                      ->row();

    if (!$payment) {
        $payment = $CI->db->where('id', $invoice_id)
                          ->limit(1)
                          ->get($paymentsTable)
                          ->row();
        if ($payment) {
            $invoice_id = (int)$payment->invoiceid;
        }
    }

    if (!$payment) {
        return;
    }

    $payment_id = (int)($payment->id ?? ($payment->paymentid ?? 0));
    if ($payment_id <= 0 || isset($processedPayments[$payment_id])) {
        return;
    }
    $processedPayments[$payment_id] = true;

    $note = (string)($payment->note ?? '');
    if (stripos($note, 'via Receipt #') !== false || stripos($note, 'Applied from Receipt #') !== false) {
        return;
    }

    $CI->load->model('invoices_model');
    $CI->load->model('paymentsonaccount/payments_on_account_model');

    $receipt = null;
    if ($CI->db->field_exists('source_payment_id', db_prefix().'receipts')) {
        $receipt = $CI->db->where('source_payment_id', $payment_id)
                          ->limit(1)
                          ->get(db_prefix().'receipts')
                          ->row();
    }

    if (!$receipt) {
        $invoice = $CI->invoices_model->get($invoice_id);
        if (!$invoice) {
            return;
        }

        $receipt_id = $CI->payments_on_account_model->create_receipt(
            (int)$invoice->clientid,
            (float)$payment->amount,
            $payment->paymentmode,
            [$invoice_id],
            'Auto-generated from Payment ID: '.$payment_id,
            date('Y-m-d', strtotime($payment->date)),
            null,
            $payment->transactionid,
            false,
            $payment_id
        );
        $receipt = $CI->payments_on_account_model->get_receipt($receipt_id);
    }

    if (!$receipt || $CI->payments_on_account_model->receipt_email_was_sent((int)$receipt->id)) {
        return;
    }

    $CI->payments_on_account_model->send_receipt_email((int)$receipt->id);
}

/**
 * Normalizes the different payload shapes Perfex has used for the
 * before_email_template_send hook/filter.
 */
function poa_email_template_context($data): array
{
    $mailTpl = null;
    $slug    = null;
    $relId   = null;

    if (is_array($data)) {
        $mailTpl = $data['template'] ?? ($data['mail_template'] ?? null);
        $slug    = $data['slug'] ?? ($data['template_slug'] ?? null);
        $relId   = $data['rel_id']
            ?? ($data['payment_id'] ?? ($data['paymentid'] ?? ($data['invoice_id'] ?? ($data['invoiceid'] ?? null))));
    } elseif (is_object($data)) {
        $mailTpl = $data;
    }

    if (is_object($mailTpl)) {
        if (!$slug) {
            if (isset($mailTpl->slug)) {
                $slug = $mailTpl->slug;
            } elseif (method_exists($mailTpl, 'slug')) {
                $slug = $mailTpl->slug();
            }
        }

        if (!$relId) {
            if (isset($mailTpl->rel_id)) {
                $relId = (int)$mailTpl->rel_id;
            } elseif (isset($mailTpl->relation_id)) {
                $relId = (int)$mailTpl->relation_id;
            } elseif (isset($mailTpl->payment_id)) {
                $relId = (int)$mailTpl->payment_id;
            } elseif (isset($mailTpl->invoice_id)) {
                $relId = (int)$mailTpl->invoice_id;
            } elseif (method_exists($mailTpl, 'get_rel_id')) {
                $relId = (int)$mailTpl->get_rel_id();
            } elseif (method_exists($mailTpl, 'get_relation_id')) {
                $relId = (int)$mailTpl->get_relation_id();
            }
        }
    }

    return [
        'template' => $mailTpl,
        'slug'     => $slug ? (string)$slug : null,
        'rel_id'   => $relId ? (int)$relId : null,
    ];
}

/**
 * Αντικαθιστά το core statement PDF με το custom Statement (Receipts) του module
 * όταν στέλνεται email σχετικό με τιμολόγιο ΚΑΙ έχει τσεκαριστεί "Attach Customer Statement".
 *
 * @param \app\services\mail_template\MailTemplate $template
 * @return void
 */
function poa_attach_custom_statement_pdf($data)
{
    if ($data === false) {
        return $data;
    }

    $CI = &get_instance();

    // --- Normalize: υποστήριξη ΚΑΙ array payload ΚΑΙ object MailTemplate ---
    $mailTpl     = null;
    $slug        = null;
    $relId       = null;
    $attachments = [];

    if (is_array($data)) {
        // Συνήθη keys σε αυτή τη φάση του Perfex
        $mailTpl     = $data['template']      ?? ($data['mail_template'] ?? null);
        $slug        = $data['slug']          ?? ($data['template_slug'] ?? null);
        $relId       = $data['rel_id']        ?? null;
        $attachments = $data['attachments']   ?? [];

        // Συμπλήρωσε από object αν λείπουν
        if ((!$slug || !$relId) && is_object($mailTpl)) {
            if (!$slug && isset($mailTpl->slug))                 { $slug  = $mailTpl->slug; }
            if (!$slug && method_exists($mailTpl, 'slug'))       { $slug  = $mailTpl->slug(); }
            if (!$relId && isset($mailTpl->rel_id))              { $relId = (int)$mailTpl->rel_id; }
            if (!$relId && method_exists($mailTpl, 'get_rel_id')){ $relId = (int)$mailTpl->get_rel_id(); }
            if (empty($attachments)) {
                if (method_exists($mailTpl, 'get_attachments')) { $attachments = $mailTpl->get_attachments(); }
                elseif (isset($mailTpl->attachments))           { $attachments = $mailTpl->attachments; }
            }
        }
    } else {
        // Παλιότερες/άλλες εκδόσεις: έρχεται object
        $mailTpl = $data;
        if (is_object($mailTpl)) {
            if (isset($mailTpl->slug))                  { $slug  = $mailTpl->slug; }
            elseif (method_exists($mailTpl, 'slug'))     { $slug  = $mailTpl->slug(); }
            if (isset($mailTpl->rel_id))                 { $relId = (int)$mailTpl->rel_id; }
            elseif (method_exists($mailTpl, 'get_rel_id')) { $relId = (int)$mailTpl->get_rel_id(); }
            if (method_exists($mailTpl, 'get_attachments')) { $attachments = $mailTpl->get_attachments(); }
            elseif (isset($mailTpl->attachments))           { $attachments = $mailTpl->attachments; }
        }
    }

    // Αν δεν μάθαμε slug/relId, δεν επεμβαίνουμε
    if (!$slug || !$relId) {
        return $data;
    }

    // Φιλτράρουμε μόνο για invoice-related templates
    $invoiceRelatedSlugs = [
        'invoice-send-to-client',
        'invoice-overdue-notice',
        'invoice-payment-recorded-to-customer',
    ];
    if (!in_array($slug, $invoiceRelatedSlugs, true)) {
        return $data;
    }

    // Έχει τσεκαριστεί "Attach Customer Statement";
    $attachStatementChecked = $CI->input->post() ? (bool)$CI->input->post('attach_statement') : false;
    if (!$attachStatementChecked) {
        return $data;
    }

    // Πιάσε invoice -> client
    $CI->load->model('invoices_model');
    $invoice = $CI->invoices_model->get((int)$relId);
    if (!$invoice) {
        return $data;
    }
    $clientId = (int)$invoice->clientid;

    // Αφαίρεσε τυχόν core statement.pdf από τα attachments που ήδη έχει
    $filtered = [];
    foreach ((array)$attachments as $att) {
        // Μπορεί να είναι path string ή array με keys
        $pathOrName = is_array($att) ? ($att['attachment'] ?? $att['filename'] ?? '') : $att;
        $base       = basename((string)$pathOrName);
        if (stripos($base, 'statement') !== false && stripos($base, '.pdf') !== false) {
            continue; // drop core statement
        }
        $filtered[] = $att;
    }

    // Ημερομηνίες (από POST αν υπάρχουν, αλλιώς τελευταίοι 12 μήνες)
    $from_sql = $CI->input->post('poa_statement_from');
    $to_sql   = $CI->input->post('poa_statement_to');
    if (empty($from_sql) || empty($to_sql)) {
        $to_sql   = date('Y-m-d');
        $from_sql = date('Y-m-d', strtotime('-12 months', strtotime($to_sql)));
    }

    // Χτίσε το δικό σας Statement (RAW BYTES) από το model σας
    $CI->load->model('paymentsonaccount/payments_on_account_model');
    [$ok, $bytesOrMsg, $filename] =
        $CI->payments_on_account_model->build_statement_receipts_pdf_bytes($clientId, $from_sql, $to_sql);

    if ($ok) {
        $filtered[] = [
            'content'  => $bytesOrMsg, // RAW BYTES
            'filename' => $filename ?: ('statement_'._d($from_sql).'_to_'._d($to_sql).'.pdf'),
            'type'     => 'application/pdf',
        ];
    }

    // Επιστροφή στο filter (πολύ σημαντικό)
    if (is_array($data)) {
        $data['attachments'] = $filtered;
        return $data;
    }

    // Object path (fallback)
    if (is_object($mailTpl)) {
        if (method_exists($mailTpl, 'set_attachments')) { $mailTpl->set_attachments($filtered); }
        elseif (property_exists($mailTpl, 'attachments')) { $mailTpl->attachments = $filtered; }
    }
    return $data;
}



function poa_get_client_payment_mode_ids(int $client_id): array
{
    $CI =& get_instance();
    $rows = $CI->db->select('payment_mode_id')->where('client_id', $client_id)->get(db_prefix().'poa_client_payment_modes')->result_array();
    return array_values(array_unique(array_map('intval', array_column($rows, 'payment_mode_id'))));
}

function poa_filter_payment_modes_for_client($payment_modes, int $client_id)
{
    $allowed = poa_get_client_payment_mode_ids($client_id);
    if (empty($allowed)) { return $payment_modes; }
    if (!is_array($payment_modes)) { return $payment_modes; }

    return array_values(array_filter($payment_modes, function($mode) use ($allowed){
        $id = is_object($mode) ? (int)($mode->id ?? 0) : (int)($mode['id'] ?? 0);
        return in_array($id, $allowed, true);
    }));
}

hooks()->add_filter('invoice_available_payment_modes', function($payment_modes, $invoice = null){
    $client_id = (int)($invoice->clientid ?? 0);
    return $client_id > 0 ? poa_filter_payment_modes_for_client($payment_modes, $client_id) : $payment_modes;
}, 10, 2);

hooks()->add_filter('payment_modes_for_invoice', function($payment_modes, $invoice = null){
    $client_id = (int)($invoice->clientid ?? 0);
    return $client_id > 0 ? poa_filter_payment_modes_for_client($payment_modes, $client_id) : $payment_modes;
}, 10, 2);
