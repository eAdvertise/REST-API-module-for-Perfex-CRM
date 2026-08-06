<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: API
Module URI: https://codecanyon.net/item/rest-api-for-perfex-crm/25278359
Description: Rest API module for Perfex CRM
Version: 2.0.8
Author: Themesic Interactive
Author URI: https://1.envato.market/themesic
*/

require_once __DIR__.'/vendor/autoload.php';
define('API_MODULE_NAME', 'api');
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
        $CI->app_menu->add_sidebar_children_item('api-options', [
            'slug'     => 'api-register-options',
            'name'     => _l('api_management'),
            'href'     => admin_url('api/api_management'),
            'position' => 5,
        ]);
        
        $CI->app_menu->add_sidebar_children_item('api-options', [
            'slug'     => 'api-guide-options',
            'name'     => _l('api_guide'),
            'href'     => 'https://perfexcrm.themesic.com/apiguide/',
            'position' => 10,
        ]);
    }
}
