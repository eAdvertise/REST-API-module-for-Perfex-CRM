<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Module upgrade script for version 3.0.1.
 *
 * - Marks installations that include the core payment email suppression fix.
 */
class Migration_Version_301 extends App_module_migration
{
    public function up()
    {
        if (function_exists('paymentsonaccount_apply_3_0_1_database_updates')) {
            paymentsonaccount_apply_3_0_1_database_updates();
            return;
        }

        $version = '3.0.1';
        $moduleName = defined('PAYMENTS_ON_ACCOUNT_MODULE_NAME')
            ? PAYMENTS_ON_ACCOUNT_MODULE_NAME
            : 'paymentsonaccount';

        if (function_exists('paymentsonaccount_sync_module_database_version')) {
            paymentsonaccount_sync_module_database_version($version);
            return;
        }

        $CI =& get_instance();
        update_option('paymentsonaccount_module_version', $version);

        $modulesTable = db_prefix() . 'modules';
        if ($CI->db->table_exists($modulesTable)) {
            $moduleColumns  = ['module_name', 'module'];
            $versionColumns = ['installed_version', 'version'];
            $moduleColumn   = null;

            foreach ($moduleColumns as $column) {
                if ($CI->db->query("SHOW COLUMNS FROM `{$modulesTable}` LIKE " . $CI->db->escape($column))->num_rows() > 0) {
                    $moduleColumn = $column;
                    break;
                }
            }

            if ($moduleColumn !== null) {
                foreach ($versionColumns as $column) {
                    if ($CI->db->query("SHOW COLUMNS FROM `{$modulesTable}` LIKE " . $CI->db->escape($column))->num_rows() > 0) {
                        $CI->db->where($moduleColumn, $moduleName)
                               ->update($modulesTable, [$column => $version]);
                    }
                }
            }
        }
    }
}
