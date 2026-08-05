<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_210 extends App_module_migration
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
        // Add quota fields to user_api table
        if (!$this->db->field_exists('request_limit', db_prefix() . 'user_api')) {
            $this->db->query("
                ALTER TABLE `" . db_prefix() . "user_api` 
                ADD `request_limit` int(11) NOT NULL DEFAULT 1000 AFTER `permission_enable`,
                ADD `time_window` int(11) NOT NULL DEFAULT 3600 AFTER `request_limit`,
                ADD `burst_limit` int(11) NOT NULL DEFAULT 0 AFTER `time_window`,
                ADD `quota_active` tinyint(1) NOT NULL DEFAULT 1 AFTER `burst_limit`,
                ADD `quota_created_at` datetime NULL AFTER `quota_active`,
                ADD `quota_updated_at` datetime NULL AFTER `quota_created_at`
            ");
        }

        // Migrate existing quota data to user_api table
        if ($this->db->table_exists(db_prefix() . 'user_api_quotas')) {
            $this->db->query("
                UPDATE `" . db_prefix() . "user_api` ua
                INNER JOIN `" . db_prefix() . "user_api_quotas` uaq ON CONVERT(ua.token USING utf8mb4) COLLATE utf8mb4_general_ci = CONVERT(uaq.api_key USING utf8mb4) COLLATE utf8mb4_general_ci
                SET 
                    ua.request_limit = uaq.request_limit,
                    ua.time_window = uaq.time_window,
                    ua.burst_limit = uaq.burst_limit,
                    ua.quota_active = uaq.active,
                    ua.quota_created_at = uaq.created_at,
                    ua.quota_updated_at = uaq.updated_at
            ");
        }

        // Set default values for users without quota settings
        $this->db->query("
            UPDATE `" . db_prefix() . "user_api` 
            SET 
                request_limit = 1000,
                time_window = 3600,
                burst_limit = 100,
                quota_active = 1,
                quota_created_at = NOW(),
                quota_updated_at = NOW()
            WHERE request_limit IS NULL OR request_limit = 0
        ");

        // Drop the separate user_api_quotas table (no longer needed)
        $this->db->query("DROP TABLE IF EXISTS `" . db_prefix() . "user_api_quotas`");
    }
    
    public function down()
    {
        // Recreate user_api_quotas table
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
                KEY `active` (`active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        // Migrate data back to separate table
        $this->db->query("
            INSERT INTO `" . db_prefix() . "user_api_quotas` 
            (`user_api_id`, `api_key`, `request_limit`, `time_window`, `burst_limit`, `active`, `created_at`, `updated_at`)
            SELECT 
                id as user_api_id,
                token as api_key,
                request_limit,
                time_window,
                burst_limit,
                quota_active as active,
                quota_created_at as created_at,
                quota_updated_at as updated_at
            FROM `" . db_prefix() . "user_api`
            WHERE request_limit IS NOT NULL
        ");

        // Remove quota fields from user_api table
        $this->db->query("
            ALTER TABLE `" . db_prefix() . "user_api` 
            DROP COLUMN `request_limit`,
            DROP COLUMN `time_window`,
            DROP COLUMN `burst_limit`,
            DROP COLUMN `quota_active`,
            DROP COLUMN `quota_created_at`,
            DROP COLUMN `quota_updated_at`
        ");
    }
}
