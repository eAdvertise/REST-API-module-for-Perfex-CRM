<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * ContactsPlus Module - uninstall
 * Drops only pmc_* tables and removes options if any.
 */
$CI = &get_instance();

if ($CI->db->table_exists(db_prefix().'pmc_contacts_bridge')) {
    $CI->db->query("DROP TABLE `".db_prefix()."pmc_contacts_bridge`");
}
if ($CI->db->table_exists(db_prefix().'pmc_contact_company')) {
    $CI->db->query("DROP TABLE `".db_prefix()."pmc_contact_company`");
}
if ($CI->db->table_exists(db_prefix().'pmc_contacts')) {
    $CI->db->query("DROP TABLE `".db_prefix()."pmc_contacts`");
}
delete_option('contactsplus_module_version');