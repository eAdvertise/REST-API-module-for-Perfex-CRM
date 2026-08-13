<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: API
Module URI: https://www.eadvertise.eu
Description: Rest API module for Perfex CRM
Version: 3.0.3
Author: eAdvertise.eu
Author URI: https://www.eadvertise.eu
*/

// Silence the PSR-0 `Requests_...` deprecation notice emitted by the bundled
// rmccue/requests v2 shim (vendor/rmccue/requests/library/Requests.php) when the
// legacy `Requests` class is autoloaded via the Composer classmap. This MUST be
// defined before the Composer autoloader is loaded. On servers with display_errors
// enabled the E_USER_DEPRECATED notice is echoed to output, which sends headers
// prematurely and breaks any later redirect() (e.g. the client area ValidatesContact).
if (!defined('REQUESTS_SILENCE_PSR0_DEPRECATIONS')) {
    define('REQUESTS_SILENCE_PSR0_DEPRECATIONS', true);
}

require_once __DIR__.'/vendor/autoload.php';
define('API_MODULE_NAME', 'api');
// Keep in lockstep with the "Version:" header above. Used by the schema
// self-heal and by install.php to record the installed schema version.
define('API_MODULE_VERSION', '3.0.3');
hooks()->add_action('admin_init', 'api_init_menu_items');


/**
* Load the module helper
*/
$CI = & get_instance();
$CI->load->helper(API_MODULE_NAME . '/api');

/**
* Register activation module hook
*/
register_activation_hook(API_MODULE_NAME, 'api_activation_hook');

function api_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

/**
 * Schema self-heal for file-replace upgrades.
 *
 * Perfex only runs install.php + migrations when the module is (re)activated.
 * Customers who upgrade by overwriting the module files never trigger that,
 * which previously left the schema stale (e.g. missing user_api.staff_id and
 * "Unknown column" errors). install.php is fully idempotent and always
 * reflects the current schema, so here we re-run it once whenever the stored
 * schema version is older than the shipped module version, then record it.
 */
hooks()->add_action('admin_init', 'api_schema_self_heal');
function api_schema_self_heal()
{
    try {
        // Admins only, and only when the stored schema is older than the code.
        if (!function_exists('get_option') || !function_exists('is_admin') || !is_admin()) {
            return;
        }
        $installed = (string) get_option('api_schema_version');
        if ($installed !== '' && version_compare($installed, API_MODULE_VERSION, '>=')) {
            return;
        }

        // Back off: attempt at most once per hour. install.php stamps
        // api_schema_version only after the schema is verified present, so a
        // successful heal short-circuits on the version check above. This
        // throttle stops a heal that keeps failing on a given environment
        // (privilege denied, lock timeout, PHP max_execution_time - none of
        // which are Throwables the catch below can trap) from re-running the
        // full DDL on every admin page load, and serialises concurrent loads.
        $lastAttempt = (int) get_option('api_schema_heal_attempt');
        if (time() - $lastAttempt < 3600) {
            return;
        }
        if (!update_option('api_schema_heal_attempt', (string) time())) {
            add_option('api_schema_heal_attempt', (string) time());
        }

        require_once __DIR__ . '/install.php';
    } catch (Throwable $e) {
        if (function_exists('log_message')) {
            log_message('error', 'API schema self-heal: ' . $e->getMessage());
        }
    }
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(API_MODULE_NAME, [API_MODULE_NAME]);

// Ensure custom email template exists for Guest Checkout (Invoice + Receipt)
hooks()->add_action('admin_init', 'api_ensure_guest_checkout_email_template');
function api_ensure_guest_checkout_email_template()
{
    if (!is_admin()) {
        return;
    }

    // One-time guard
    if (get_option('api_guest_checkout_email_template_installed') == '1') {
        return;
    }

    $CI = &get_instance();
    $table = db_prefix() . 'emailtemplates';

    if (!$CI->db->table_exists($table)) {
        return;
    }

    $slug = 'api_guest_invoice_checkout';

    $languages = [];
    if ($CI->db->field_exists('language', $table)) {
        $activeLang = (string)(get_option('active_language') ?: 'english');
        $languages[] = $activeLang;
        if ($activeLang !== 'english') {
            $languages[] = 'english';
        }
    } else {
        $languages[] = null;
    }

    $ok = false;

    foreach ($languages as $lang) {
        $CI->db->where('slug', $slug);
        if ($lang !== null) {
            $CI->db->where('language', $lang);
        }
        $exists = $CI->db->get($table)->row();
        if ($exists) {
            $ok = true;
            continue;
        }

        $data = [];
        if ($CI->db->field_exists('type', $table)) {
            $data['type'] = 'invoices';
        }
        if ($CI->db->field_exists('slug', $table)) {
            $data['slug'] = $slug;
        }
        if ($CI->db->field_exists('name', $table)) {
            $data['name'] = 'API Guest Checkout (Invoice & Receipt)';
        }
        if ($CI->db->field_exists('subject', $table)) {
            $data['subject'] = 'Your Invoice {invoice_number} & Receipt';
        }
        if ($CI->db->field_exists('message', $table)) {
            $data['message'] = 'Hello {contact_firstname},<br><br>Thank you for your payment. Please find attached your invoice <b>{invoice_number}</b> and your receipt.<br><br>Regards,<br>{companyname}';
        }
        if ($lang !== null && $CI->db->field_exists('language', $table)) {
            $data['language'] = $lang;
        }
        if ($CI->db->field_exists('active', $table)) {
            $data['active'] = 1;
        }
        if ($CI->db->field_exists('plaintext', $table)) {
            $data['plaintext'] = 0;
        }
        if ($CI->db->field_exists('order', $table)) {
            $data['order'] = 999;
        }

        // Insert only if we have at least slug+message/subject
        if (!empty($data)) {
            $CI->db->insert($table, $data);
            if ($CI->db->affected_rows() > 0) {
                $ok = true;
            }
        }
    }

    if ($ok) {
        update_option('api_guest_checkout_email_template_installed', '1');
    }
}



// Register one capability group for all Warehouse REST resources.
hooks()->add_filter('api_permissions', 'api_warehouse_permissions');
function api_warehouse_permissions($apiPermissions)
{
    $apiPermissions['warehouse'] = [
        'name'         => 'Warehouse',
        'capabilities' => [
            'get'    => _l('permission_get'),
            'post'   => _l('permission_create'),
            'put'    => _l('permission_update'),
            'delete' => _l('permission_delete'),
        ],
    ];

    return $apiPermissions;
}

// Register one capability group for the PaymentsOnAccount REST facade.
hooks()->add_filter('api_permissions', 'api_paymentsonaccount_permissions');
function api_paymentsonaccount_permissions($apiPermissions)
{
    $apiPermissions['paymentsonaccount'] = [
        'name'         => 'Payments On Account',
        'capabilities' => [
            'get'    => _l('permission_get'),
            'post'   => _l('permission_create'),
            'put'    => _l('permission_update'),
            'delete' => _l('permission_delete'),
        ],
    ];

    return $apiPermissions;
}

// Only expose Delivery Notes permissions when the optional module is active.
if ($CI->app_modules->is_active('delivery_notes')) {
    hooks()->add_filter('api_permissions', 'api_delivery_notes_permissions');
}
function api_delivery_notes_permissions($apiPermissions)
{
    $apiPermissions['delivery_notes'] = [
        'name'         => 'Delivery Notes',
        'capabilities' => [
            'get'    => _l('permission_get'),
            'post'   => _l('permission_create'),
            'put'    => _l('permission_update'),
            'delete' => _l('permission_delete'),
        ],
    ];

    return $apiPermissions;
}

// Only expose Sales Commission permissions when the optional module is active.
if ($CI->app_modules->is_active('commission')) {
    hooks()->add_filter('api_permissions', 'api_commission_permissions');
}
function api_commission_permissions($apiPermissions)
{
    $apiPermissions['commission'] = [
        'name' => 'Sales Commission',
        'capabilities' => [
            'get' => _l('permission_get'), 'post' => _l('permission_create'),
            'put' => _l('permission_update'), 'delete' => _l('permission_delete'),
        ],
    ];
    return $apiPermissions;
}

if ($CI->app_modules->is_active('myshopify')) {
    hooks()->add_filter('api_permissions', 'api_myshopify_permissions');
}
function api_myshopify_permissions($apiPermissions)
{
    $apiPermissions['myshopify'] = [
        'name' => 'MyShopify',
        'capabilities' => ['get' => _l('permission_get'), 'post' => _l('permission_create')],
    ];
    return $apiPermissions;
}

if ($CI->app_modules->is_active('purchase')) {
    hooks()->add_filter('api_permissions', 'api_purchase_permissions');
}
function api_purchase_permissions($apiPermissions)
{
    $apiPermissions['purchase'] = [
        'name' => 'Purchase Management',
        'capabilities' => [
            'get' => _l('permission_get'), 'post' => _l('permission_create'),
            'put' => _l('permission_update'), 'delete' => _l('permission_delete'),
        ],
    ];
    return $apiPermissions;
}


// Register permissions for custom Guest Invoices API endpoints.
hooks()->add_filter('api_permissions', 'api_guest_invoices_permissions');
function api_guest_invoices_permissions($apiPermissions)
{
    $apiPermissions['guest_invoices'] = [
        'name'         => 'Guest Invoices',
        'capabilities' => [
            'post'          => _l('permission_create'),
            'checkout_post' => 'Checkout',
        ],
    ];

    return $apiPermissions;
}




/**
 * Init api module menu items in setup in admin_init hook
 * @return null
 */
function api_init_menu_items()
{
    /**
    * If the logged in user is administrator, add custom menu in Setup
    */
    if (is_admin()) {
        $CI = &get_instance();
        $CI->app_menu->add_sidebar_menu_item('api-options', [
            'collapse' => true,
            'name'     => _l('api'),
            'position' => 40,
            'icon'     => 'fa fa-cogs',
        ]);
        // 1. Users Management
        $CI->app_menu->add_sidebar_children_item('api-options', [
            'slug'     => 'api-register-options',
            'name'     => _l('api_management'),
            'href'     => admin_url('api/api_management'),
            'position' => 10,
        ]);

        // 2. Webhooks
        $CI->app_menu->add_sidebar_children_item('api-options', [
            'slug'     => 'api-webhooks-options',
            'name'     => _l('api_webhooks'),
            'href'     => admin_url('api/event_webhooks'),
            'position' => 20,
        ]);

        // 3. Sandbox
        $CI->app_menu->add_sidebar_children_item('api-options', [
            'slug'     => 'api-sandbox-options',
            'name'     => _l('api_sandbox'),
            'href'     => site_url('api/playground'),
            'position' => 30,
        ]);

        // 4. Statistics
        $CI->app_menu->add_sidebar_children_item('api-options', [
            'slug'     => 'api-user-stats-options',
            'name'     => _l('user_statistics'),
            'href'     => admin_url('api/user_stats'),
            'position' => 40,
        ]);

        // 5. Reports
        $CI->app_menu->add_sidebar_children_item('api-options', [
            'slug'     => 'api-reporting-options',
            'name'     => _l('api_reporting'),
            'href'     => admin_url('api/reporting'),
            'position' => 50,
        ]);

        // 6. Settings
        $CI->app_menu->add_sidebar_children_item('api-options', [
            'slug'     => 'api-settings-options',
            'name'     => _l('api_settings'),
            'href'     => admin_url('api/settings'),
            'position' => 60,
        ]);

        // 7. Automation Connectors
        $CI->app_menu->add_sidebar_children_item('api-options', [
            'slug'     => 'api-connectors-options',
            'name'     => _l('automation_connectors'),
            'href'     => admin_url('api/automation_connectors'),
            'position' => 65,
        ]);

        // Keep the hosted API documentation available from the API menu.
        $CI->app_menu->add_sidebar_children_item('api-options', [
            'slug'     => 'api-documentation-options',
            'name'     => _l('api_documentation'),
            'href'     => 'https://apidoc.eadcrm.eu/',
            'position' => 100,
            'target'   => '_blank',
            'rel'      => 'noopener noreferrer',
        ]);
        
    }
}

// REMOVED: Zapier route interception - now handled by CodeIgniter routing directly
// Routes are defined in config/routes.php and handled by Zapier controller

// Register connector routes early to ensure they're loaded before eAD-CRM core routing
hooks()->add_action('pre_system', API_MODULE_NAME . '_register_connector_routes');
function api_register_connector_routes()
{
    // This hook runs very early, before routing
    // Log that we're attempting to register routes
    if (function_exists('log_message')) {
        log_message('debug', 'API Module: Attempting to register connector routes');
    }
}

// "Support period over" notice removed (provider-neutral).

/**
 * Webhooks 2.0: bridge Perfex core action hooks to webhook events.
 *
 * Perfex fires these hooks for both admin-panel AND API actions (the API
 * endpoints call the same core models), so webhook consumers get notified
 * regardless of where the change originated. Unknown/renamed hooks are
 * simply never fired - mappings are additive and harmless.
 *
 * @return array core_hook => [webhook_event, ...]
 */
function api_webhook_bridge_map()
{
    return [
        // Customers
        'after_client_created'          => ['customer_created'],
        'client_updated'                => ['client_updated', 'customer_updated'],
        'customer_updated_company_info' => ['customer_updated'],
        'after_client_deleted'          => ['customer_deleted'],
        'client_status_changed'         => ['client_status_changed'],
        // Contacts
        'contact_created'        => ['contact_created'],
        'contact_updated'        => ['contact_updated'],
        'contact_deleted'        => ['contact_deleted'],
        'contact_status_changed' => ['contact_status_changed'],
        // Leads
        'lead_created'        => ['lead_created'],
        'after_lead_updated'  => ['lead_updated'],
        'after_lead_deleted'  => ['lead_deleted'],
        'lead_status_changed' => ['lead_status_changed'],
        'lead_marked_as_lost' => ['lead_marked_lost'],
        // Invoices
        'after_invoice_added'         => ['invoice_created'],
        'invoice_updated'             => ['invoice_updated'],
        'after_invoice_deleted'       => ['invoice_deleted'],
        'invoice_sent'                => ['invoice_sent'],
        'invoice_marked_as_cancelled' => ['invoice_cancelled'],
        // Estimates
        'after_estimate_added'          => ['estimate_created'],
        'after_estimate_updated'        => ['estimate_updated'],
        'after_estimate_deleted'        => ['estimate_deleted'],
        'estimate_sent'                 => ['estimate_sent'],
        'estimate_accepted'             => ['estimate_accepted'],
        'estimate_declined'             => ['estimate_declined'],
        'estimate_converted_to_invoice' => ['estimate_converted_to_invoice'],
        // Proposals
        'proposal_created'       => ['proposal_created'],
        'after_proposal_updated' => ['proposal_updated'],
        'after_proposal_deleted' => ['proposal_deleted'],
        'proposal_sent'          => ['proposal_sent'],
        'proposal_accepted'      => ['proposal_accepted'],
        'proposal_declined'      => ['proposal_declined'],
        // Credit notes
        'after_create_credit_note'  => ['credit_note_created'],
        'after_update_credit_note'  => ['credit_note_updated'],
        'after_credit_note_deleted' => ['credit_note_deleted'],
        'credits_applied'           => ['credit_note_applied'],
        'credit_note_refund_created' => ['credit_note_refunded'],
        // Payments
        'after_payment_added'   => ['payment_created', 'amount_paid'],
        'after_payment_updated' => ['payment_updated'],
        'after_payment_deleted' => ['payment_deleted'],
        // Projects
        'after_add_project'                    => ['project_created'],
        'after_update_project'                 => ['project_updated'],
        'after_project_deleted'                => ['project_deleted'],
        'project_status_changed'               => ['project_status_changed'],
        'after_project_staff_added_as_member'  => ['project_member_added'],
        // Tasks
        'after_add_task'      => ['task_created'],
        'after_update_task'   => ['task_updated'],
        'task_deleted'        => ['task_deleted'],
        'task_status_changed' => ['task_status_changed'],
        'task_assignee_added' => ['task_assigned'],
        'task_comment_added'  => ['task_comment_added'],
        'task_timer_started'  => ['task_timer_started'],
        // Tickets
        'ticket_created'              => ['ticket_created'],
        'after_ticket_reply_added'    => ['ticket_reply_created'],
        'after_ticket_status_changed' => ['ticket_status_changed'],
        'after_ticket_deleted'        => ['ticket_deleted'],
        // Contracts (signed/renewed included preemptively for cores that fire them)
        'after_contract_added'   => ['contract_created'],
        'after_contract_updated' => ['contract_updated'],
        'after_contract_deleted' => ['contract_deleted'],
        'contract_signed'        => ['contract_signed'],
        'contract_renewed'       => ['contract_renewed'],
        // Expenses
        'after_expense_added'          => ['expense_created'],
        'expense_updated'              => ['expense_updated'],
        'after_expense_deleted'        => ['expense_deleted'],
        'expense_converted_to_invoice' => ['expense_converted_to_invoice'],
        // Items
        'item_created'       => ['item_created'],
        'after_item_updated' => ['item_updated'],
        'item_deleted'       => ['item_deleted'],
        // Staff
        'staff_member_created' => ['staff_created'],
        'staff_member_updated' => ['staff_updated'],
        'staff_member_deleted' => ['staff_deleted'],
        'after_staff_login'    => ['staff_login'],
        // Notes
        'note_created'       => ['note_created'],
        'before_update_note' => ['note_updated'],
        'note_deleted'       => ['note_deleted'],
    ];
}

/**
 * Fire the mapped webhook events for a core hook occurrence.
 */
function api_bridge_fire($events, $hook, $arg = null)
{
    try {
        $CI = &get_instance();
        if (!$CI->db->table_exists(db_prefix() . 'api_webhooks')) {
            return;
        }
        if (!class_exists('Api_Webhook_Service')) {
            require_once __DIR__ . '/libraries/Api_Webhook_Service.php';
        }
        $data = is_array($arg) ? $arg : ['id' => $arg];
        $data['_source_hook'] = $hook;

        $service = new Api_Webhook_Service();
        foreach ($events as $event) {
            $service->triggerWebhooks($event, $data);
        }
    } catch (Exception $e) {
        if (function_exists('log_message')) {
            log_message('error', 'API webhook bridge (' . $hook . '): ' . $e->getMessage());
        }
    }
}

// Register every bridge listener
foreach (api_webhook_bridge_map() as $api_bridge_hook => $api_bridge_events) {
    hooks()->add_action($api_bridge_hook, function ($arg = null) use ($api_bridge_events, $api_bridge_hook) {
        api_bridge_fire($api_bridge_events, $api_bridge_hook, $arg);
        return $arg; // pass filter-style values through untouched
    });
}
unset($api_bridge_hook, $api_bridge_events);

/**
 * Webhooks 2.0: process the async delivery queue on Perfex cron.
 */
hooks()->add_action('after_cron_run', 'api_process_webhook_queue');
function api_process_webhook_queue()
{
    try {
        $CI = &get_instance();
        if (!$CI->db->table_exists(db_prefix() . 'api_webhook_queue')) {
            return;
        }
        if (!class_exists('Api_Webhook_Service')) {
            require_once __DIR__ . '/libraries/Api_Webhook_Service.php';
        }
        $service = new Api_Webhook_Service();
        $service->processQueue(25);

        // Housekeeping: idempotency keys are replay-safe for 24 hours
        if ($CI->db->table_exists(db_prefix() . 'api_idempotency_keys')) {
            $CI->db->query(
                'DELETE FROM `' . db_prefix() . "api_idempotency_keys` WHERE created_at < (NOW() - INTERVAL 24 HOUR)"
            );
        }
    } catch (Exception $e) {
        if (function_exists('log_message')) {
            log_message('error', 'API webhook queue (cron): ' . $e->getMessage());
        }
    }
}

/**
 * Webhooks 2.0: fallback processing on admin page loads (throttled to once
 * per minute) for installs where Perfex cron is not configured.
 */
hooks()->add_action('admin_init', 'api_webhook_queue_fallback');

/**
 * Vendor self-update checks are disabled intentionally.
 * This fork is maintained in-repo and should not contact the upstream vendor.
 */
function api_daily_update_check()
{
    return null;
}
function api_webhook_queue_fallback()
{
    try {
        if (get_option('api_webhook_delivery_mode') !== 'queue') {
            return;
        }
        $last = (int)get_option('api_webhook_queue_last_run');
        if (time() - $last < 60) {
            return;
        }
        if (!update_option('api_webhook_queue_last_run', time())) {
            add_option('api_webhook_queue_last_run', time());
        }
        if (!class_exists('Api_Webhook_Service')) {
            require_once __DIR__ . '/libraries/Api_Webhook_Service.php';
        }
        $service = new Api_Webhook_Service();
        $service->processQueue(10);
    } catch (Exception $e) {
        if (function_exists('log_message')) {
            log_message('error', 'API webhook queue (fallback): ' . $e->getMessage());
        }
    }
}
