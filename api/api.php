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

modules\api\core\Apiinit::the_da_vinci_code(API_MODULE_NAME);

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

hooks()->add_action('app_init', API_MODULE_NAME . '_actLib');
function api_actLib()
{
    $CI = &get_instance();
    $CI->load->library(API_MODULE_NAME . '/api_aeiou');
    $envato_res = $CI->api_aeiou->validatePurchase(API_MODULE_NAME);
    if (!$envato_res) {
        set_alert('danger', 'One of your modules failed its verification and got deactivated. Please reactivate or contact support.');
    }
}

hooks()->add_action('pre_activate_module', API_MODULE_NAME . '_sidecheck');
function api_sidecheck($module_name)
{
    if (API_MODULE_NAME == $module_name['system_name']) {
        modules\api\core\Apiinit::activate($module_name);
    }
}

hooks()->add_action('pre_deactivate_module', API_MODULE_NAME . '_deregister');
function api_deregister($module_name)
{
    if (API_MODULE_NAME == $module_name['system_name']) {
        delete_option(API_MODULE_NAME . '_verification_id');
        delete_option(API_MODULE_NAME . '_last_verification');
        delete_option(API_MODULE_NAME . '_product_token');
        delete_option(API_MODULE_NAME . '_heartbeat');
    }
}


function api_supported_until() {

    if (get_option('extra_support_notice') == 0) {
        return;
    } else {
    $supported_until = get_option(API_MODULE_NAME.'_supported_until');
    if (empty($supported_until)) {
        return;
    }
$date_only = substr($supported_until, 0, 10);
$supported_until_timestamp = strtotime($date_only);
$current_date_timestamp = time();
if ($supported_until_timestamp < ($current_date_timestamp - (6 * 30 * 24 * 60 * 60))) {
echo '<div class="supported_until alert alert-warning" style="font-size: 16px; background-color: #fff3cd; border-color: #ffeeba; color: #856404;
            position: fixed; top: 50px; left: 50%; padding: 20px; transform: translateX(-50%); z-index: 9999; width: 90%; max-width: 600px; box-shadow: rgba(0, 0, 0, 0.25) 0px 54px 55px, rgba(0, 0, 0, 0.12) 0px -12px 30px, rgba(0, 0, 0, 0.12) 0px 4px 6px, rgba(0, 0, 0, 0.17) 0px 12px 13px, rgba(0, 0, 0, 0.09) 0px -3px 5px;">
<img style="max-width:100px;" src="https://themesic.com/wp-content/uploads/2023/07/cropped-logo-with-text-minus.png"><br><br>
<p>⚠️ The support period for one of your modules seems over.<br><br>We offer an alternative way to receive <strong>free support</strong> for potential issues,<br>simply by rating our product on <img style="max-width:80px;" src="https://themesic.com/wp-content/plugins/fast-plugin/assets/images/envato.svg">. <a href="https://1.envato.market/themesic" target="_blank" style="text-decoration:underline !important;"><strong> Click here to do that</strong></a> 👈</p><br>
<p>Your feedback help us continue developing and improving the product!</p>
<br /><br />
<a href="?dismiss=true" class="alert-link" style="text-decoration:underline !important;">Thanks, do not show this again</a> ✔️
</div></center>';
}
    }
}

// Check for the dismiss URL and update the option
if (isset($_GET['dismiss']) && $_GET['dismiss'] === 'true') {
    update_option('extra_support_notice', 0); // Dismiss the notice
    // Redirect to clear the URL parameter and avoid it being triggered again
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

hooks()->add_action('app_admin_head', 'api_supported_until');

function api_hide_support_extension() {
    echo "<script>
        jQuery(document).ready(function($) {
            // Get all elements with class 'supported_until'
            var divs = $('.supported_until');
            console.log('Total .supported_until divs:', divs.length); // Log how many divs are rendered
           
            // If more than one div, hide all except the first
            if (divs.length > 1) {
                divs.slice(1).hide(); // Hide all but the first one
            }
        });
    </script>";
}



hooks()->add_action('app_admin_footer', 'api_hide_support_extension');