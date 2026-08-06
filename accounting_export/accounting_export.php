<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Accounting Export
Description: Export invoices, payments and credit notes to accounting-friendly CSV/XLSX files.
Version: 1.0.0
Requires at least: 3.0.*
Author: eAdvertise.eu
Author URI: https://www.eadvertise.eu
*/

define('ACCOUNTING_EXPORT_MODULE_NAME', 'accounting_export');
define('ACCOUNTING_EXPORT_MODULE_VERSION', '1.0.0');

register_activation_hook(ACCOUNTING_EXPORT_MODULE_NAME, 'accounting_export_module_activate');
register_language_files(ACCOUNTING_EXPORT_MODULE_NAME, [ACCOUNTING_EXPORT_MODULE_NAME]);

hooks()->add_action('admin_init', 'accounting_export_init');

function accounting_export_module_activate()
{
    require_once __DIR__ . '/install.php';
}

function accounting_export_init()
{
    $CI = &get_instance();

    if (function_exists('register_staff_capability')) {
        register_staff_capability('accounting_export', 'view', _l('permission_view'));
    }

    if (is_admin()) {
        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug'     => 'accounting-export',
            'name'     => _l('accounting_export'),
            'href'     => admin_url('accounting_export'),
            'position' => 12,
            'icon'     => 'fa fa-file-export',
        ]);
    }
}
