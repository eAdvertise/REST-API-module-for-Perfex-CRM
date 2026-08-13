<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Module upgrade script for version 3.1.0.
 *
 * - Marks installations with the finalized receipt email/PDF fixes.
 * - Keeps the editable receipt email template registered.
 */
class Migration_Version_310 extends App_module_migration
{
    public function up()
    {
        if (function_exists('paymentsonaccount_apply_3_1_0_database_updates')) {
            paymentsonaccount_apply_3_1_0_database_updates();
            return;
        }

        if (function_exists('paymentsonaccount_register_email_template')) {
            paymentsonaccount_register_email_template();
        }

        $version = '3.1.0';

        if (function_exists('paymentsonaccount_sync_module_database_version')) {
            paymentsonaccount_sync_module_database_version($version);
            return;
        }

        update_option('paymentsonaccount_module_version', $version);
    }
}
