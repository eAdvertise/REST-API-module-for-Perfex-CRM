<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_126 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        add_option("webhook_cron_has_run_from_cli", 0);

        if (!$CI->db->table_exists(db_prefix().'scheduled_webhooks')) {
            $CI->db->query('CREATE TABLE `'.db_prefix().'scheduled_webhooks` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `webhook_id` int(11) NOT NULL,
                `request_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(request_data)),
                `rel_id` int(11) NOT NULL,
                `rel_type` varchar(15) NOT NULL,
                `action` varchar(15) NOT NULL,
                `secondary_id` int(11) NULL,
                `scheduled_at` datetime NOT NULL,
                `executed_at` datetime NULL DEFAULT NULL,
                `error_message` text NULL DEFAULT NULL,
                `status` varchar(15) NOT NULL DEFAULT "PENDING",
                PRIMARY KEY (`id`)) ENGINE = InnoDB DEFAULT CHARSET='.$CI->db->char_set.';');
        }

        if ($CI->db->table_exists(db_prefix() . 'webhooks_master')) {
            if (!$CI->db->field_exists('webhook_after_number', db_prefix() . 'webhooks_master')) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . 'webhooks_master` ADD `webhook_after_number` INT NULL');
            }
            if (!$CI->db->field_exists('webhook_after_type', db_prefix() . 'webhooks_master')) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . 'webhooks_master` ADD `webhook_after_type` VARCHAR(20) NULL');
            }
        }
    }
}