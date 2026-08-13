<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Module upgrade script for version 3.0.4.
 *
 * - Moves the receipt email template to the core invoice group so it is visible
 *   under Setup > Email Templates (admin/emails).
 * - Ensures payment receipt emails use core activity logs and the version marker is updated.
 */
class Migration_Version_304 extends App_module_migration
{
    public function up()
    {
        if (function_exists('paymentsonaccount_apply_3_0_4_database_updates')) {
            paymentsonaccount_apply_3_0_4_database_updates();
            return;
        }

        if (function_exists('paymentsonaccount_register_email_template')) {
            paymentsonaccount_register_email_template();
        }

        $version = '3.0.4';

        if (function_exists('paymentsonaccount_sync_module_database_version')) {
            paymentsonaccount_sync_module_database_version($version);
            return;
        }

        update_option('paymentsonaccount_module_version', $version);
    }
}
