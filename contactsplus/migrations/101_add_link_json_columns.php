<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration 1.0.1
 * Προσθήκη στηλών perms_json & email_notif_json στον πίνακα link (tblpmc_contact_company)
 * Idempotent: δεν σκάει αν ήδη υπάρχουν.
 */
function contactsplus_migration_101()
{
    $CI = &get_instance();
    $table = db_prefix() . 'pmc_contact_company';

    if (!$CI->db->field_exists('perms_json', $table)) {
        $CI->db->query("ALTER TABLE `{$table}` ADD `perms_json` TEXT NULL AFTER `notifications`");
    }

    if (!$CI->db->field_exists('email_notif_json', $table)) {
        $CI->db->query("ALTER TABLE `{$table}` ADD `email_notif_json` TEXT NULL AFTER `perms_json`");
    }
}
