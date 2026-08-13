<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Update_module_version extends App_module_migration
{
    public function up()
    {
        // Καταγράφουμε την νέα έκδοση στη βάση (option)
        $current = get_option('paymentsonaccount_module_version');
        if (!$current || version_compare($current, '2.0.0', '<')) {
            update_option('paymentsonaccount_module_version', '2.0.0');
        }
    }
}
