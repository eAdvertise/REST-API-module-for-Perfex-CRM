<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * v3.0.0 upgrade migration
 *
 * Everything here is idempotent and mirrors the fresh-install blocks in
 * install.php, so upgraded installs get the same schema/options:
 *  - api_webhook_queue (async webhook delivery)
 *  - api_idempotency_keys (Idempotency-Key support)
 *  - user_api.staff_id (opt-in staff-level visibility)
 *  - v3 option seeds (webhook delivery/SSRF/TLS, MCP toggle, visibility, throttle)
 */
class Migration_Version_300 extends App_module_migration
{
    /** @var CI_DB_query_builder */
    protected $db;

    public function __construct()
    {
        parent::__construct();

        $CI = &get_instance();
        $CI->load->database();
        $this->db = $CI->db;
    }

    public function up()
    {
        // Async webhook delivery queue
        if (!$this->db->table_exists(db_prefix() . 'api_webhook_queue')) {
            $this->db->query("
                CREATE TABLE `" . db_prefix() . "api_webhook_queue` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `webhook_id` INT(11) UNSIGNED NOT NULL,
                    `event` VARCHAR(100) NOT NULL,
                    `payload` LONGTEXT NOT NULL,
                    `status` ENUM('pending','processing','delivered','failed') NOT NULL DEFAULT 'pending',
                    `attempts` INT(11) NOT NULL DEFAULT 0,
                    `max_attempts` INT(11) NOT NULL DEFAULT 3,
                    `next_attempt_at` DATETIME NOT NULL,
                    `claim_token` VARCHAR(64) DEFAULT NULL,
                    `claimed_at` DATETIME DEFAULT NULL,
                    `created_at` DATETIME NOT NULL,
                    `completed_at` DATETIME DEFAULT NULL,
                    `last_error` TEXT DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_status_next` (`status`, `next_attempt_at`),
                    KEY `idx_claim` (`claim_token`),
                    KEY `idx_webhook` (`webhook_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }

        // Idempotency-Key storage
        if (!$this->db->table_exists(db_prefix() . 'api_idempotency_keys')) {
            $this->db->query("
                CREATE TABLE `" . db_prefix() . "api_idempotency_keys` (
                    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `idem_key` VARCHAR(191) NOT NULL,
                    `api_key` VARCHAR(255) NOT NULL DEFAULT '',
                    `method` VARCHAR(10) NOT NULL DEFAULT 'post',
                    `endpoint` VARCHAR(255) NOT NULL DEFAULT '',
                    `response_code` INT(11) NOT NULL DEFAULT 200,
                    `response_body` LONGTEXT NULL,
                    `created_at` DATETIME NOT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `idx_idem_lookup` (`idem_key`(100), `api_key`(64)),
                    KEY `idx_created` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }

        // Optional token-to-staff link for staff-level visibility
        if ($this->db->table_exists(db_prefix() . 'user_api')
            && !$this->db->field_exists('staff_id', db_prefix() . 'user_api')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'user_api` ADD `staff_id` INT(11) NULL DEFAULT NULL AFTER `user`');
        }

        // v3 option seeds
        if (function_exists('add_option')) {
            $seeds = [
                'api_webhook_delivery_mode' => 'immediate',
                'api_webhook_ssrf_strict'   => '0',
                'api_webhook_ssl_verify'    => '1',
                'api_mcp_enabled'           => '0',
                'api_staff_visibility'      => '0',
                'api_auth_throttle_limit'   => '20',
            ];
            foreach ($seeds as $name => $value) {
                if (get_option($name) === '') {
                    add_option($name, $value);
                }
            }
        }
    }
}
