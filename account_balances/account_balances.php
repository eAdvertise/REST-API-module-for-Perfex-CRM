<?php

defined("BASEPATH") or exit("No direct script access allowed");

/*
Module Name: ACCOUNT BALANCES
Description: View the total balance and account transactions of your cash/bank accounts.

Version: 1.0.5
Author: eAdvertise.eu
Author URI: https://www.eadvertise.eu
*/



define("ACCOUNT_BALANCES_MODULE_NAME", "account_balances");


hooks()->add_action('admin_init', 'case_module_menu');
hooks()->add_action('admin_init', 'case_permissions');
hooks()->add_filter('get_dashboard_widgets', 'account_balance_add_dashboard_widget');

register_activation_hook(ACCOUNT_BALANCES_MODULE_NAME , "case_activation_hook");

function case_activation_hook()
{

    $CI = &get_instance();

    require_once __DIR__ . "/install.php";

}

// language registered
register_language_files(ACCOUNT_BALANCES_MODULE_NAME, [ACCOUNT_BALANCES_MODULE_NAME]);

/**
 * Permission
 *
 * @return void
 */
function case_permissions()
{

    $capabilities = [];

    $capabilities['capabilities'] = [
        'case_manage'   => _l("case_manage") ,

        'case_manage_transfer'   => _l('account_balances_transfer'),
        'case_manage_transfer_del'   => _l('account_balances_transfer').' <span class="text-danger">( '._l('delete').' )</span>',

        'case_manage_entry'   => _l('account_entry'),
        'case_manage_entry_del'   => _l('account_entry').' <span class="text-danger">( '._l('delete').' )</span>',

        'case_manage_withdraw'   => _l('account_withdrawal'),
        'case_manage_withdraw_del'   => _l('account_withdrawal').' <span class="text-danger">( '._l('delete').' )</span>',

    ];

    register_staff_capabilities('case_manage', $capabilities, _l("case_manage"));

}

/**
 * item menu setting
 *
 * @return void
 */
function case_module_menu()
{

    $CI = &get_instance();

    if(
        staff_can( 'case_manage' , 'case_manage'  ) ||

        staff_can( 'case_manage_transfer' , 'case_manage'  ) ||
        staff_can( 'case_manage_transfer_del' , 'case_manage'  ) ||

        staff_can( 'case_manage_entry' , 'case_manage'  ) ||
        staff_can( 'case_manage_entry_del' , 'case_manage'  ) ||

        staff_can( 'case_manage_withdraw' , 'case_manage'  ) ||
        staff_can( 'case_manage_withdraw_del' , 'case_manage'  )

    )
    {

        $CI->app_menu->add_sidebar_menu_item("account_balances", [

            'collapse' => true,

            'name' => _l("case_manage"),

            'position' => 48,

            'icon' => 'fa fa-briefcase',

        ]);


        if ( staff_can( 'case_manage' , 'case_manage'  )  )
        {
            $CI->app_menu->add_sidebar_children_item('account_balances', [
                'slug' => 'account_balances_manage',
                'name' => _l('case_list'),
                'href' => admin_url('account_balances/casebank/case_manage'),
                'position' => 1,
            ]);
        }

        if (
            staff_can( 'case_manage_transfer' , 'case_manage'  ) ||
            staff_can( 'case_manage_transfer_del' , 'case_manage'  )
        )
        {
            $CI->app_menu->add_sidebar_children_item('account_balances', [
                'slug' => 'account_balances_transfer',
                'name' => _l('account_balances_transfer'),
                'href' => admin_url('account_balances/casebank/transfer_manage'),
                'position' => 2,
            ]);
        }


        if (
            staff_can( 'case_manage_withdraw' , 'case_manage'  ) ||
            staff_can( 'case_manage_withdraw_del' , 'case_manage'  )
        )
        {
            $CI->app_menu->add_sidebar_children_item('account_balances', [
                'slug' => 'account_balances_withdrawal',
                'name' => _l('account_withdrawal'),
                'href' => admin_url('account_balances/casebank/withdrawal'),
                'position' => 4,
            ]);
        }


        if ( is_admin() )
        {
            $CI->app_menu->add_sidebar_children_item('account_balances', [
                'slug' => 'account_balances_deliver',
                'name' => _l('account_balances_deliver'),
                'href' => admin_url('account_balances/casebank/balance_manage'),
                'position' => 10,
            ]);


            /**
             * @version 1.0.5 Setting page
             */

            $CI->app_menu->add_sidebar_children_item('account_balances', [
                'slug' => 'account_balances_setting',
                'name' => _l('settings'),
                'href' => admin_url('account_balances/casebank/settings'),
                'position' => 25,
            ]);


        }


        if (
            staff_can( 'case_manage_entry' , 'case_manage'  ) ||
            staff_can( 'case_manage_entry_del' , 'case_manage'  )
        )
        {
            $CI->app_menu->add_sidebar_children_item('account_balances', [
                'slug' => 'account_balances_account_entry',
                'name' => _l('account_entry'),
                'href' => admin_url('account_balances/casebank/income'),
                'position' =>3,
            ]);
        }



    }

}

/**
 * Dashboard widget
 *
 * @param $widgets
 * @return void
 */
function account_balance_add_dashboard_widget($widgets){

    if( staff_can( 'case_manage' , 'case_manage'  ) )
    {

        $widgets[] = [

            'path'      => 'account_balances/widget',

            'container' => 'right-4',

        ];

    }

    return $widgets;

}


/**
 * @version @date 1.0.5
 */


hooks()->add_filter('invoice_currency_attributes', 'account_balance_invoice_currency_attributes');
function account_balance_invoice_currency_attributes( $currency_attr )
{

    $setting_option = get_option('account_balance_enable_multi_currency_for_invoice');

    if ( !empty( $setting_option ) )
    {

        if ( isset( $currency_attr['disabled'] ) )
            unset($currency_attr['disabled']);

    }


    return $currency_attr;

}


hooks()->add_filter('estimate_currency_attributes', 'account_balance_estimate_currency_attributes');
function account_balance_estimate_currency_attributes( $currency_attr )
{

    $setting_option = get_option('account_balance_enable_multi_currency_for_estimate');

    if ( !empty( $setting_option ) )
    {

        if ( isset( $currency_attr['disabled'] ) )
            unset($currency_attr['disabled']);

    }


    return $currency_attr;

}
