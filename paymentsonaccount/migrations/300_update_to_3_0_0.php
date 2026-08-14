<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Module upgrade script for version 3.0.0.
 *
 * - Sets the module database version option to 3.0.0.
 * - Sets the default receipt prefix to REC-.
 * - Prefixes existing receipt numbers that do not already start with REC-.
 */
class Migration_Version_300 extends App_module_migration
{
    public function up()
    {
        if (function_exists('paymentsonaccount_apply_3_0_0_database_updates')) {
            paymentsonaccount_apply_3_0_0_database_updates();
            return;
        }

        $CI =& get_instance();
        $tbl = db_prefix() . 'receipts';

        if ($CI->db->table_exists($tbl) && $CI->db->field_exists('receipt_number', $tbl)) {
            $like = $CI->db->escape_like_str('REC-') . '%';
            $CI->db->query(
                "UPDATE `{$tbl}`
                 SET `receipt_number` = CONCAT('REC-', `receipt_number`)
                 WHERE `receipt_number` IS NOT NULL
                   AND TRIM(`receipt_number`) != ''
                   AND `receipt_number` NOT LIKE " . $CI->db->escape($like)
            );
        }

        update_option('receipt_number_prefix', 'REC-');
        update_option('paymentsonaccount_module_version', '3.0.0');

        $modulesTable = db_prefix() . 'modules';
        if ($CI->db->table_exists($modulesTable)) {
            $hasModuleName = $CI->db->query("SHOW COLUMNS FROM `{$modulesTable}` LIKE 'module_name'")->num_rows() > 0;
            $hasInstalledVersion = $CI->db->query("SHOW COLUMNS FROM `{$modulesTable}` LIKE 'installed_version'")->num_rows() > 0;
            if ($hasModuleName && $hasInstalledVersion) {
                $CI->db->where('module_name', PAYMENTS_ON_ACCOUNT_MODULE_NAME)
                       ->update($modulesTable, ['installed_version' => '3.0.0']);
            }
        }
    }
}
