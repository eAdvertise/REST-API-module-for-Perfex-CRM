<?php
//modules/contactsplus/install.php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Contacts Plus - install (safe, no FKs)
 */
$CI = &get_instance();

$pref = db_prefix();
$eng  = 'ENGINE=InnoDB DEFAULT CHARSET=utf8';

// 1) Κάνε το email nullable στο core tblcontacts (για no-email contacts στο core modal αν χρειαστεί)
$CI->db->query("ALTER TABLE `{$pref}contacts` MODIFY `email` VARCHAR(100) NULL");

// 2) Δημιουργία master πίνακα επαφών του module (pmc_contacts)
if (!$CI->db->table_exists($pref.'pmc_contacts')) {
    $CI->db->query("
        CREATE TABLE `{$pref}pmc_contacts` (
          `id` INT(11) NOT NULL AUTO_INCREMENT,
          `firstname` VARCHAR(100) NOT NULL,
          `lastname` VARCHAR(100) NULL,
          `email` VARCHAR(191) NULL,
          `phone` VARCHAR(50) NULL,
          `position` VARCHAR(100) NULL,
          `notes` TEXT NULL,
          `has_portal_access` TINYINT(1) NOT NULL DEFAULT '0',
          `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
          `created_at` DATETIME NULL DEFAULT NULL,
          `updated_at` DATETIME NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
}

// 3) Δημιουργία πίνακα συσχέτισης επαφής<->εταιρείας (pmc_contact_company) χωρίς FKs
$tbl = $pref.'pmc_contact_company';
$CI->db->query("SET FOREIGN_KEY_CHECKS=0");
$CI->db->query("CREATE TABLE IF NOT EXISTS `{$tbl}` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `contact_id` INT(11) NOT NULL,
  `client_id` INT(11) NOT NULL,
  `role` VARCHAR(100) NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT '0',
  `billing` TINYINT(1) NOT NULL DEFAULT '0',
  `notifications` TINYINT(1) NOT NULL DEFAULT '1',
  `perms_json` TEXT NULL,
  `email_notif_json` TEXT NULL,
  `created_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_contact_client` (`contact_id`,`client_id`),
  KEY `idx_contact_id` (`contact_id`),
  KEY `idx_client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
$CI->db->query("SET FOREIGN_KEY_CHECKS=1");

// 4) Permission
if (function_exists('register_staff_capability')) {
    register_staff_capability('contactsplus', 'contactsplus_manage', _l('contactsplus_perm_manage'));
}

// 5) Ensure pmc_contacts_bridge exists (διορθωμένο με $pref/$eng)
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
