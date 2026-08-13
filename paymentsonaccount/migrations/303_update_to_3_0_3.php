<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Module upgrade script for version 3.0.3.
 *
 * - Ensures the editable receipt email template exists.
 * - Marks installations that include the core-email fallback sender fix.
 */
class Migration_Version_303 extends App_module_migration
{
    public function up()
    {
        if (function_exists('paymentsonaccount_apply_3_0_3_database_updates')) {
            paymentsonaccount_apply_3_0_3_database_updates();
            return;
        }

        if (function_exists('paymentsonaccount_register_email_template')) {
            paymentsonaccount_register_email_template();
        }

        $version = '3.0.3';

        if (function_exists('paymentsonaccount_sync_module_database_version')) {
            paymentsonaccount_sync_module_database_version($version);
            return;
        }

        update_option('paymentsonaccount_module_version', $version);
    }
}
