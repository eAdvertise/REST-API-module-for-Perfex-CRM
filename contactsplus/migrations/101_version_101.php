<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_101 extends App_module_migration
{
    public function up()
    {
        require_once __DIR__ . '/schema_sanity.php';
        contactsplus_schema_sanity();

        $CI = &get_instance();
        $table = db_prefix() . 'pmc_contact_company';

        if (!$CI->db->table_exists($table)) {
            require APP_MODULES_PATH . 'contactsplus/install.php';
        }

        if (!$CI->db->field_exists('perms_json', $table)) {
            $CI->db->query("ALTER TABLE `{$table}` ADD `perms_json` TEXT NULL AFTER `notifications`");
        }

        if (!$CI->db->field_exists('email_notif_json', $table)) {
            $CI->db->query("ALTER TABLE `{$table}` ADD `email_notif_json` TEXT NULL AFTER `perms_json`");
        }
    }
}
