<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_Client_Payment_Modes_Table extends App_module_migration
{
    public function up()
    {
        $CI =& get_instance();
        $tbl = db_prefix() . 'poa_client_payment_modes';

        if (!$CI->db->table_exists($tbl)) {
            $CI->db->query("CREATE TABLE IF NOT EXISTS `{$tbl}` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `client_id` INT NOT NULL,
              `payment_mode_id` INT NOT NULL,
              `created_at` DATETIME NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq_client_mode` (`client_id`,`payment_mode_id`),
              KEY `idx_client_id` (`client_id`),
              KEY `idx_mode_id` (`payment_mode_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
        }
    }
}

