<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_105 extends App_module_migration
{

    public function up()
    {

        $CI = &get_instance();

        add_option('account_balance_enable_multi_currency_for_invoice' , 0 , 0 );

        add_option('account_balance_enable_multi_currency_for_estimate' , 0 , 0 );


    }

}
