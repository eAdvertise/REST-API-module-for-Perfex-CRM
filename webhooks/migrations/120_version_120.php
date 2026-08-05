<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_120 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        if($CI->db->table_exists(db_prefix().'webhooks_master')) {
            $CI->db->query('ALTER TABLE `'.db_prefix().'webhooks_master` CHANGE `webhook_action` `webhook_action` TEXT NOT NULL');
            $CI->db->query('ALTER TABLE `'.db_prefix().'webhooks_master` CHANGE `request_url` `request_url` TEXT NOT NULL');
            $CI->db->query('ALTER TABLE `'.db_prefix().'webhooks_master` CHANGE `request_header` `request_header` TEXT NOT NULL');
            $CI->db->query('ALTER TABLE `'.db_prefix().'webhooks_master` CHANGE `request_body` `request_body` TEXT NOT NULL');
        }

        if($CI->db->table_exists(db_prefix().'webhooks_debug_log')) {
            $CI->db->query('ALTER TABLE `'.db_prefix().'webhooks_debug_log` CHANGE `webhook_action` `webhook_action` TEXT NOT NULL');
            $CI->db->query('ALTER TABLE `'.db_prefix().'webhooks_debug_log` CHANGE `request_url` `request_url` TEXT NOT NULL');
            $CI->db->query('ALTER TABLE `'.db_prefix().'webhooks_debug_log` CHANGE `request_header` `request_header` TEXT NOT NULL');
            $CI->db->query('ALTER TABLE `'.db_prefix().'webhooks_debug_log` CHANGE `request_body` `request_body` TEXT NOT NULL'); 
        }
    }
}