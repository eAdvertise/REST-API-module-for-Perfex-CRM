<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_211 extends App_module_migration
{
    public function up()
    {
        $CI =& get_instance();
        
        if (!$CI->db->table_exists(db_prefix() . 'api_webhooks')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'api_webhooks` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `url` TEXT NOT NULL,
                `events` TEXT NOT NULL COMMENT "Comma-separated list of events",
                `secret` VARCHAR(255) DEFAULT NULL COMMENT "Secret for webhook signature",
                `active` TINYINT(1) DEFAULT 1,
                `headers` TEXT DEFAULT NULL COMMENT "JSON object of custom headers",
                `timeout` INT(11) DEFAULT 30 COMMENT "Request timeout in seconds",
                `retry_count` INT(11) DEFAULT 3 COMMENT "Number of retries on failure",
                `last_triggered` DATETIME DEFAULT NULL,
                `success_count` INT(11) DEFAULT 0,
                `failure_count` INT(11) DEFAULT 0,
                `date_created` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `date_updated` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_active` (`active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        }
        
        if (!$CI->db->table_exists(db_prefix() . 'api_webhook_logs')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'api_webhook_logs` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `webhook_id` INT(11) NOT NULL,
                `event` VARCHAR(100) NOT NULL,
                `url` TEXT NOT NULL,
                `payload` LONGTEXT NOT NULL COMMENT "JSON payload sent",
                `response_code` INT(11) DEFAULT NULL,
                `response_body` TEXT DEFAULT NULL,
                `error_message` TEXT DEFAULT NULL,
                `attempt_number` INT(11) DEFAULT 1,
                `status` ENUM("pending", "success", "failed") DEFAULT "pending",
                `triggered_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_webhook_id` (`webhook_id`),
                INDEX `idx_status` (`status`),
                INDEX `idx_triggered_at` (`triggered_at`),
                FOREIGN KEY (`webhook_id`) REFERENCES `' . db_prefix() . 'api_webhooks`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        }
    }
    
    public function down()
    {
        $CI =& get_instance();
        $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'api_webhook_logs`');
        $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'api_webhooks`');
    }
}
