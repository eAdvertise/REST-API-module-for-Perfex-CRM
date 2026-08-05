<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Τρέχει πάντα και διορθώνει idempotently κρίσιμα σημεία σχήματος.
 */
function contactsplus_schema_sanity()
{
    $CI   = &get_instance();
    $pref = db_prefix();
    $eng  = 'ENGINE=InnoDB DEFAULT CHARSET=utf8';

    // 0) Bridge table – να υπάρχει πάντα
    if (!$CI->db->table_exists($pref.'pmc_contacts_bridge')) {
        $CI->db->query("
            CREATE TABLE `{$pref}pmc_contacts_bridge` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `tblcontact_id` INT(11) NOT NULL,
                `client_id` INT(11) NOT NULL,
                `pmc_contact_id` INT(11) NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_core_client` (`tblcontact_id`,`client_id`),
                KEY `idx_pmc_contact` (`pmc_contact_id`)
            ) {$eng};
        ");
    }

    // 1) Link table: ensure JSON πεδία
    $table = $pref.'pmc_contact_company';
    if ($CI->db->table_exists($table)) {
        if (!$CI->db->field_exists('perms_json', $table)) {
            $CI->db->query("ALTER TABLE `{$table}` ADD `perms_json` TEXT NULL AFTER `notifications`");
        }
        if (!$CI->db->field_exists('email_notif_json', $table)) {
            $CI->db->query("ALTER TABLE `{$table}` ADD `email_notif_json` TEXT NULL AFTER `perms_json`");
        }
    }
}