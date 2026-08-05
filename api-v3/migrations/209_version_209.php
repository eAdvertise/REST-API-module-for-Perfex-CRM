<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_209 extends App_module_migration
{
    /** @var CI_DB_query_builder */
    protected $db;

    public function __construct()
    {
        parent::__construct();

        // Properly initialize the database once the migration is constructed
        $CI = &get_instance();
        $CI->load->database();
        $this->db = $CI->db;
    }
    public function up()
    {
        // Create API quotas table with foreign key relationship
        if (!$this->db->table_exists(db_prefix() . 'user_api_quotas')) {
            $this->db->query("
                CREATE TABLE `" . db_prefix() . "user_api_quotas` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `user_api_id` int(11) NOT NULL,
                    `api_key` varchar(255) NOT NULL,
                    `request_limit` int(11) NOT NULL DEFAULT 1000,
                    `time_window` int(11) NOT NULL DEFAULT 3600,
                    `burst_limit` int(11) NOT NULL DEFAULT 0,
                    `active` tinyint(1) NOT NULL DEFAULT 1,
                    `created_at` datetime NOT NULL,
                    `updated_at` datetime NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `user_api_id` (`user_api_id`),
                    KEY `api_key` (`api_key`),
                    KEY `active` (`active`),
                    CONSTRAINT `fk_user_api_quotas_user_api` 
                        FOREIGN KEY (`user_api_id`) 
                        REFERENCES `" . db_prefix() . "user_api` (`id`) 
                        ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        } else {
            // Check if user_api_id column exists, if not add it
            if (!$this->db->field_exists('user_api_id', db_prefix() . 'user_api_quotas')) {
                $this->db->query("ALTER TABLE `" . db_prefix() . "user_api_quotas` 
                    ADD `user_api_id` int(11) NOT NULL AFTER `id`");
                
                // Update existing records to link with user_api table
                $this->db->query("
                    UPDATE `" . db_prefix() . "user_api_quotas` uaq
                    INNER JOIN `" . db_prefix() . "user_api` ua ON CONVERT(uaq.api_key USING utf8mb4) COLLATE utf8mb4_general_ci = CONVERT(ua.token USING utf8mb4) COLLATE utf8mb4_general_ci
                    SET uaq.user_api_id = ua.id
                ");
                
                // Add foreign key constraint
                $this->db->query("
                    ALTER TABLE `" . db_prefix() . "user_api_quotas` 
                    ADD CONSTRAINT `fk_user_api_quotas_user_api` 
                    FOREIGN KEY (`user_api_id`) 
                    REFERENCES `" . db_prefix() . "user_api` (`id`) 
                    ON DELETE CASCADE ON UPDATE CASCADE
                ");
            }
        }
        
        // Create API usage logs table with foreign key relationship
        if (!$this->db->table_exists(db_prefix() . 'api_usage_logs')) {
            $this->db->query("
                CREATE TABLE `" . db_prefix() . "api_usage_logs` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `user_api_id` int(11) NOT NULL,
                    `api_key` varchar(255) NOT NULL,
                    `endpoint` varchar(255) NOT NULL,
                    `response_code` int(11) NOT NULL,
                    `response_time` decimal(10,4) NOT NULL DEFAULT 0.0000,
                    `timestamp` int(11) NOT NULL,
                    `ip_address` varchar(45) NOT NULL,
                    `user_agent` text,
                    PRIMARY KEY (`id`),
                    KEY `user_api_id` (`user_api_id`),
                    KEY `api_key` (`api_key`),
                    KEY `endpoint` (`endpoint`),
                    KEY `timestamp` (`timestamp`),
                    KEY `response_code` (`response_code`),
                    CONSTRAINT `fk_api_usage_logs_user_api` 
                        FOREIGN KEY (`user_api_id`) 
                        REFERENCES `" . db_prefix() . "user_api` (`id`) 
                        ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        } else {
            // Check if user_api_id column exists, if not add it
            if (!$this->db->field_exists('user_api_id', db_prefix() . 'api_usage_logs')) {
                $this->db->query("ALTER TABLE `" . db_prefix() . "api_usage_logs` 
                    ADD `user_api_id` int(11) NOT NULL AFTER `id`");
                
                // Update existing records to link with user_api table
                $this->db->query("
                    UPDATE `" . db_prefix() . "api_usage_logs` aul
                    INNER JOIN `" . db_prefix() . "user_api` ua ON CONVERT(aul.api_key USING utf8mb4) COLLATE utf8mb4_general_ci = CONVERT(ua.token USING utf8mb4) COLLATE utf8mb4_general_ci
                    SET aul.user_api_id = ua.id
                ");
                
                // Add foreign key constraint
                $this->db->query("
                    ALTER TABLE `" . db_prefix() . "api_usage_logs` 
                    ADD CONSTRAINT `fk_api_usage_logs_user_api` 
                    FOREIGN KEY (`user_api_id`) 
                    REFERENCES `" . db_prefix() . "user_api` (`id`) 
                    ON DELETE CASCADE ON UPDATE CASCADE
                ");
            }
        }
        
        // Insert default quota settings for existing API users if they don't exist
        $this->db->query("
            INSERT IGNORE INTO `" . db_prefix() . "user_api_quotas` 
            (`user_api_id`, `api_key`, `request_limit`, `time_window`, `burst_limit`, `active`, `created_at`, `updated_at`) 
            SELECT 
                id as user_api_id,
                token as api_key,
                1000 as request_limit,
                3600 as time_window,
                100 as burst_limit,
                1 as active,
                NOW() as created_at,
                NOW() as updated_at
            FROM `" . db_prefix() . "user_api`
            WHERE id NOT IN (SELECT user_api_id FROM `" . db_prefix() . "user_api_quotas`)
        ");
    }
    
    public function down()
    {
        // Drop tables
        $this->db->query("DROP TABLE IF EXISTS `" . db_prefix() . "api_usage_logs`");
        $this->db->query("DROP TABLE IF EXISTS `" . db_prefix() . "user_api_quotas`");
    }
}
