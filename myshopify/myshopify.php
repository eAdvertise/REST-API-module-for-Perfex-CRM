<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Module Name: My Shopify Module
 * Description: View Shopify shop orders, customers, products, and discounts directly from the eAD-CRM dashboard.
 * Version: 1.0.0
 * Requires at least: 3.0.*
 * Author: eAdvertise.eu
 * Author URI: https://www.eadvertise.eu
 */

require_once __DIR__ . '/vendor/autoload.php';

// Define module name constant
define('MYSHOPIFY_MODULE_NAME', 'myshopify');


$CI = &get_instance();

// Load helper functions
$CI->load->helper(MYSHOPIFY_MODULE_NAME . '/myshopify');


// Register activation hook to trigger installation logic
register_activation_hook(MYSHOPIFY_MODULE_NAME, 'myshopify_activate_module');

/**
 * Module activation callback.
 * Called once when the module is activated.
 */
function myshopify_activate_module()
{
    require_once __DIR__ . '/install.php';
}




// Register language files
register_language_files(MYSHOPIFY_MODULE_NAME, [MYSHOPIFY_MODULE_NAME]);



// Add admin menu items
hooks()->add_action('admin_init', 'shopify_init_menu_items');

/**
 * Initialize Shopify menu in the admin sidebar
 */
function shopify_init_menu_items()
{
    if (!has_permission('shopify', '', 'view')) {
        return;
    }

    $CI = &get_instance();

    // Add main Shopify menu
    $CI->app_menu->add_sidebar_menu_item('shopify-menu', [
        'name'     => _l('my_shopify'),
        'collapse' => true,
        'position' => 11,
        'icon'     => 'fa-brands fa-shopify',
    ]);

    // Add submenu: Orders
    $CI->app_menu->add_sidebar_children_item('shopify-menu', [
        'slug'     => 'my_shopify-orders',
        'name'     => _l('my_shopify_orders'),
        'href'     => admin_url('myshopify/orders'),
        'position' => 15,
    ]);

    // Add submenu: Customers
    $CI->app_menu->add_sidebar_children_item('shopify-menu', [
        'slug'     => 'my_shopify-customers',
        'name'     => _l('my_shopify_customers'),
        'href'     => admin_url('myshopify/customers'),
        'position' => 13,
    ]);

    // Add submenu: Products
    $CI->app_menu->add_sidebar_children_item('shopify-menu', [
        'slug'     => 'my_shopify-products',
        'name'     => _l('my_shopify_products'),
        'href'     => admin_url('myshopify/products'),
        'position' => 16,
    ]);
     // Add submenu: Categories
     $CI->app_menu->add_sidebar_children_item('shopify-menu', [
        'slug'     => 'my_shopify-categories',
        'name'     => _l('my_shopify_categories'),
        'href'     => admin_url('myshopify/categories'),
        'position' => 16,
    ]);
    
    // Add submenu: Discounts
    $CI->app_menu->add_sidebar_children_item('shopify-menu', [
        'slug'     => 'my_shopify-discounts',
        'name'     => _l('my_shopify_discounts'),
        'href'     => admin_url('myshopify/discounts'),
        'position' => 17,
    ]);

    $CI->app->add_settings_section_child('integrations', 'myshopify', [
        'name'     => '' . _l('myshopify') . '',
        'view'     => 'myshopify/admin/settings',
        'position' => 60,
        'icon'     => 'fa-brands fa-shopify',
    ]);

    if (is_admin()) {
        $CI->app_menu->add_setup_menu_item(MYSHOPIFY_MODULE_NAME, [
            'slug'     => 'myshopify_verify',
            'name'     => _l('myshopify_verify'),
            'href'     => admin_url('myshopify/verify'),
            'position' => 35,
        ]);
    }
}




