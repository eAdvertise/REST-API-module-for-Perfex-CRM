<?php
 
defined('BASEPATH') or exit('No direct script access allowed');
 
$CI =& get_instance();
 
$constraint_exists = function ($table, $constraint) use ($CI) {
    $result = $CI->db->query("
        SELECT 1
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = '" . db_prefix() . $table . "'
          AND CONSTRAINT_NAME = '" . $constraint . "'
        LIMIT 1
    ");
    return $result && $result->num_rows() > 0;
};

// Whether a table is already InnoDB. Used to skip the ALTER ... ENGINE=InnoDB
// rewrites below when they would be a no-op - MySQL rebuilds the whole table
// on ENGINE=InnoDB even when it is already InnoDB, which is expensive on large
// tables (e.g. api_usage_logs) and must not run on every admin-side self-heal.
$table_is_innodb = function ($table) use ($CI) {
    $result = $CI->db->query("
        SELECT ENGINE
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = '" . db_prefix() . $table . "'
        LIMIT 1
    ");
    if (!$result || $result->num_rows() === 0) {
        return false;
    }
    $engine = $result->row()->ENGINE;
    return is_string($engine) && strtoupper($engine) === 'INNODB';
};

/**
 * TABLE: user_api
 */
if (!$CI->db->table_exists(db_prefix() . 'user_api')) {
    $CI->db->query('
        CREATE TABLE `' . db_prefix() . 'user_api` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user` VARCHAR(50) NOT NULL,
            `name` VARCHAR(50) NOT NULL,
            `token` VARCHAR(255) NOT NULL,
            `expiration_date` DATETIME NULL,
            `permission_enable` TINYINT(4) NOT NULL DEFAULT 0,
            `request_limit` INT(11) NOT NULL DEFAULT 1000,
            `time_window` INT(11) NOT NULL DEFAULT 3600,
            `burst_limit` INT(11) NOT NULL DEFAULT 0,
            `quota_active` TINYINT(1) NOT NULL DEFAULT 1,
            `quota_created_at` DATETIME NULL,
            `quota_updated_at` DATETIME NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ');
} else {
    if (!$CI->db->field_exists('permission_enable', db_prefix() . 'user_api')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'user_api` ADD `permission_enable` TINYINT(4) NOT NULL DEFAULT 0');
    }
 
    if ($CI->db->field_exists('password', db_prefix() . 'user_api')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'user_api` DROP `password`');
    }
 
    if (!$CI->db->field_exists('request_limit', db_prefix() . 'user_api')) {
        $CI->db->query("
            ALTER TABLE `" . db_prefix() . "user_api`
            ADD `request_limit` INT(11) NOT NULL DEFAULT 1000 AFTER `permission_enable`,
            ADD `time_window` INT(11) NOT NULL DEFAULT 3600 AFTER `request_limit`,
            ADD `burst_limit` INT(11) NOT NULL DEFAULT 0 AFTER `time_window`,
            ADD `quota_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `burst_limit`,
            ADD `quota_created_at` DATETIME NULL AFTER `quota_active`,
            ADD `quota_updated_at` DATETIME NULL AFTER `quota_created_at`
        ");
    }
}
 
if ($CI->db->table_exists(db_prefix() . 'user_api')) {
    if (!$table_is_innodb('user_api')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'user_api` ENGINE=InnoDB');
    }

    // Drop foreign key constraint from api_usage_logs before modifying user_api.id
    if ($constraint_exists('api_usage_logs', 'fk_api_usage_logs_user_api')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'api_usage_logs` DROP FOREIGN KEY `fk_api_usage_logs_user_api`');
    }
    
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'user_api` MODIFY `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT');
    
    // Re-add the foreign key constraint if api_usage_logs table exists and has user_api_id column
    if ($CI->db->table_exists(db_prefix() . 'api_usage_logs') && 
        $CI->db->field_exists('user_api_id', db_prefix() . 'api_usage_logs') &&
        !$constraint_exists('api_usage_logs', 'fk_api_usage_logs_user_api')) {
        
        // Step 1: Update NULL user_api_id values by matching with api_key
        $CI->db->query("
            UPDATE `" . db_prefix() . "api_usage_logs` aul
            INNER JOIN `" . db_prefix() . "user_api` ua ON CONVERT(aul.api_key USING utf8mb4) COLLATE utf8mb4_general_ci = CONVERT(ua.token USING utf8mb4) COLLATE utf8mb4_general_ci
            SET aul.user_api_id = ua.id
            WHERE aul.user_api_id IS NULL
        ");
        
        // Step 2: Delete orphaned records (no matching user_api or still NULL)
        $CI->db->query("
            DELETE aul
            FROM `" . db_prefix() . "api_usage_logs` aul
            LEFT JOIN `" . db_prefix() . "user_api` ua ON aul.user_api_id = ua.id
            WHERE ua.id IS NULL OR aul.user_api_id IS NULL
        ");
        
        // Step 3: Now safe to modify column to NOT NULL
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'api_usage_logs` MODIFY `user_api_id` INT(11) UNSIGNED NOT NULL');
        
        // Step 4: Re-add the constraint
        $CI->db->query("
            ALTER TABLE `" . db_prefix() . "api_usage_logs`
            ADD CONSTRAINT `fk_api_usage_logs_user_api`
            FOREIGN KEY (`user_api_id`)
            REFERENCES `" . db_prefix() . "user_api` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE
        ");
    }
}
 
/**
 * TABLE: user_api_permissions
 */
if (!$CI->db->table_exists(db_prefix() . 'user_api_permissions')) {
    $CI->db->query('
        CREATE TABLE `' . db_prefix() . 'user_api_permissions` (
            `api_id` INT(11) UNSIGNED NOT NULL,
            `feature` VARCHAR(50) NOT NULL,
            `capability` VARCHAR(50) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ');
}
 
/**
 * TABLE: api_usage_logs
 */
if (!$CI->db->table_exists(db_prefix() . 'api_usage_logs')) {
    $CI->db->query("
        CREATE TABLE `" . db_prefix() . "api_usage_logs` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_api_id` INT(11) UNSIGNED NOT NULL,
            `api_key` VARCHAR(255) NOT NULL,
            `endpoint` VARCHAR(255) NOT NULL,
            `response_code` INT(11) NOT NULL,
            `response_time` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `timestamp` INT(11) NOT NULL,
            `ip_address` VARCHAR(45) NOT NULL,
            `user_agent` TEXT NULL,
            `rate_limit_checked` TINYINT(1) NOT NULL DEFAULT 1,
            `rate_limit_type` VARCHAR(50) NULL,
            `rate_limit_limit` INT(11) NULL,
            `rate_limit_current` INT(11) NULL,
            `rate_limit_exceeded` TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            KEY `user_api_id` (`user_api_id`),
            KEY `api_key` (`api_key`),
            KEY `endpoint` (`endpoint`),
            KEY `timestamp` (`timestamp`),
            KEY `response_code` (`response_code`),
            KEY `idx_rate_limit_ip` (`ip_address`, `timestamp`, `endpoint`),
            KEY `idx_rate_limit_key` (`api_key`, `timestamp`, `endpoint`),
            KEY `idx_rate_exceeded` (`rate_limit_exceeded`, `timestamp`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
 
    $CI->db->query("
        ALTER TABLE `" . db_prefix() . "api_usage_logs`
        ADD CONSTRAINT `fk_api_usage_logs_user_api`
        FOREIGN KEY (`user_api_id`)
        REFERENCES `" . db_prefix() . "user_api` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
    ");
} else {
    if (!$table_is_innodb('api_usage_logs')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'api_usage_logs` ENGINE=InnoDB');
    }

    if (!$constraint_exists('api_usage_logs', 'fk_api_usage_logs_user_api')) {
        if (!$CI->db->field_exists('user_api_id', db_prefix() . 'api_usage_logs')) {
            $CI->db->query("
                ALTER TABLE `" . db_prefix() . "api_usage_logs`
                ADD `user_api_id` INT(11) UNSIGNED NULL AFTER `id`
            ");
        } else {
            $CI->db->query("
                ALTER TABLE `" . db_prefix() . "api_usage_logs`
                MODIFY `user_api_id` INT(11) UNSIGNED NULL
            ");
        }
 
        $CI->db->query("
            UPDATE `" . db_prefix() . "api_usage_logs` aul
            INNER JOIN `" . db_prefix() . "user_api` ua ON CONVERT(aul.api_key USING utf8mb4) COLLATE utf8mb4_general_ci = CONVERT(ua.token USING utf8mb4) COLLATE utf8mb4_general_ci
            SET aul.user_api_id = ua.id
            WHERE aul.user_api_id IS NULL
        ");
 
        $CI->db->query("
            DELETE aul
            FROM `" . db_prefix() . "api_usage_logs` aul
            LEFT JOIN `" . db_prefix() . "user_api` ua ON aul.user_api_id = ua.id
            WHERE ua.id IS NULL OR aul.user_api_id IS NULL
        ");
 
        $CI->db->query("
            ALTER TABLE `" . db_prefix() . "api_usage_logs`
            MODIFY `user_api_id` INT(11) UNSIGNED NOT NULL
        ");
 
        $CI->db->query("
            ALTER TABLE `" . db_prefix() . "api_usage_logs`
            ADD CONSTRAINT `fk_api_usage_logs_user_api`
            FOREIGN KEY (`user_api_id`)
            REFERENCES `" . db_prefix() . "user_api` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE
        ");
    }
 
    if (!$CI->db->field_exists('rate_limit_checked', db_prefix() . 'api_usage_logs')) {
        $CI->db->query("
            ALTER TABLE `" . db_prefix() . "api_usage_logs`
            ADD `rate_limit_checked` TINYINT(1) NOT NULL DEFAULT 1 AFTER `user_agent`,
            ADD `rate_limit_type` VARCHAR(50) NULL AFTER `rate_limit_checked`,
            ADD `rate_limit_limit` INT(11) NULL AFTER `rate_limit_type`,
            ADD `rate_limit_current` INT(11) NULL AFTER `rate_limit_limit`,
            ADD `rate_limit_exceeded` TINYINT(1) NOT NULL DEFAULT 0 AFTER `rate_limit_current`,
            ADD KEY `idx_rate_limit_ip` (`ip_address`, `timestamp`, `endpoint`),
            ADD KEY `idx_rate_limit_key` (`api_key`, `timestamp`, `endpoint`),
            ADD KEY `idx_rate_exceeded` (`rate_limit_exceeded`, `timestamp`)
        ");
    }
}
 
/**
 * TABLE: api_limit
 */
if (!$CI->db->table_exists(db_prefix() . 'api_limit')) {
    $CI->db->query("
        CREATE TABLE `" . db_prefix() . "api_limit` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `uri` VARCHAR(255) NOT NULL,
            `class` VARCHAR(255) NOT NULL,
            `method` VARCHAR(255) NOT NULL,
            `ip_address` VARCHAR(45) NOT NULL,
            `time` INT(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `uri` (`uri`),
            KEY `ip_address` (`ip_address`),
            KEY `time` (`time`),
            KEY `idx_rate_check` (`ip_address`, `time`, `uri`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
}
 
/**
 * TABLE: api_webhooks
 */
if (!$CI->db->table_exists(db_prefix() . 'api_webhooks')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'api_webhooks` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
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
        `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `date_updated` TIMESTAMP NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        INDEX `idx_active` (`active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
} else {
    if (!$table_is_innodb('api_webhooks')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'api_webhooks` ENGINE=InnoDB');
    }

    // Drop foreign key constraints from api_webhook_logs before modifying api_webhooks.id
    // Check both possible constraint names (auto-generated and custom)
    if ($CI->db->table_exists(db_prefix() . 'api_webhook_logs')) {
        // Drop auto-generated constraint name
        if ($constraint_exists('api_webhook_logs', 'tblapi_webhook_logs_ibfk_1')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'api_webhook_logs` DROP FOREIGN KEY `tblapi_webhook_logs_ibfk_1`');
        }
        // Drop custom constraint name
        if ($constraint_exists('api_webhook_logs', 'fk_api_webhook_logs_webhook')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'api_webhook_logs` DROP FOREIGN KEY `fk_api_webhook_logs_webhook`');
        }
    }
    
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'api_webhooks` MODIFY `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT');
    
    // Re-add the foreign key constraint if needed
    if ($CI->db->table_exists(db_prefix() . 'api_webhook_logs') && 
        $CI->db->field_exists('webhook_id', db_prefix() . 'api_webhook_logs') &&
        !$constraint_exists('api_webhook_logs', 'fk_api_webhook_logs_webhook')) {
        
        // Ensure webhook_id is the correct type
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'api_webhook_logs` MODIFY `webhook_id` INT(11) UNSIGNED NOT NULL');
        
        // Re-add the constraint
        $CI->db->query("
            ALTER TABLE `" . db_prefix() . "api_webhook_logs`
            ADD CONSTRAINT `fk_api_webhook_logs_webhook`
            FOREIGN KEY (`webhook_id`)
            REFERENCES `" . db_prefix() . "api_webhooks` (`id`)
            ON DELETE CASCADE
        ");
    }
}
 
/**
 * TABLE: api_webhook_logs
 */
if (!$CI->db->table_exists(db_prefix() . 'api_webhook_logs')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'api_webhook_logs` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `webhook_id` INT(11) UNSIGNED NOT NULL,
        `event` VARCHAR(100) NOT NULL,
        `url` TEXT NOT NULL,
        `payload` LONGTEXT NOT NULL COMMENT "JSON payload sent",
        `response_code` INT(11) DEFAULT NULL,
        `response_body` TEXT DEFAULT NULL,
        `error_message` TEXT DEFAULT NULL,
        `attempt_number` INT(11) DEFAULT 1,
        `status` ENUM("pending", "success", "failed") DEFAULT "pending",
        `triggered_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_webhook_id` (`webhook_id`),
        INDEX `idx_status` (`status`),
        INDEX `idx_triggered_at` (`triggered_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
 
    $CI->db->query('
        ALTER TABLE `' . db_prefix() . 'api_webhook_logs`
        ADD CONSTRAINT `fk_api_webhook_logs_webhook`
        FOREIGN KEY (`webhook_id`)
        REFERENCES `' . db_prefix() . 'api_webhooks` (`id`)
        ON DELETE CASCADE
    ');
} else {
    if (!$table_is_innodb('api_webhook_logs')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'api_webhook_logs` ENGINE=InnoDB');
    }

    if (!$constraint_exists('api_webhook_logs', 'fk_api_webhook_logs_webhook')) {
        if ($CI->db->field_exists('id', db_prefix() . 'api_webhook_logs')) {
            $CI->db->query('
                ALTER TABLE `' . db_prefix() . 'api_webhook_logs`
                MODIFY `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT
            ');
        }
 
        if (!$CI->db->field_exists('webhook_id', db_prefix() . 'api_webhook_logs')) {
            $CI->db->query('
                ALTER TABLE `' . db_prefix() . 'api_webhook_logs`
                ADD `webhook_id` INT(11) UNSIGNED NULL AFTER `id`
            ');
        } else {
            $CI->db->query('
                ALTER TABLE `' . db_prefix() . 'api_webhook_logs`
                MODIFY `webhook_id` INT(11) UNSIGNED NULL
            ');
        }
 
        $CI->db->query('
            DELETE awl
            FROM `' . db_prefix() . 'api_webhook_logs` awl
            LEFT JOIN `' . db_prefix() . 'api_webhooks` aw ON awl.webhook_id = aw.id
            WHERE aw.id IS NULL OR awl.webhook_id IS NULL
        ');
 
        $CI->db->query('
            ALTER TABLE `' . db_prefix() . 'api_webhook_logs`
            MODIFY `webhook_id` INT(11) UNSIGNED NOT NULL
        ');
 
        $CI->db->query('
            ALTER TABLE `' . db_prefix() . 'api_webhook_logs`
            ADD CONSTRAINT `fk_api_webhook_logs_webhook`
            FOREIGN KEY (`webhook_id`)
            REFERENCES `' . db_prefix() . 'api_webhooks` (`id`)
            ON DELETE CASCADE
        ');
    }
}
 
/**
 * TABLE: api_webhook_queue (v3 - async webhook delivery)
 */
if (!$CI->db->table_exists(db_prefix() . 'api_webhook_queue')) {
    $CI->db->query("
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

/**
 * TABLE: api_idempotency_keys (v3 - Idempotency-Key support on POST)
 */
if (!$CI->db->table_exists(db_prefix() . 'api_idempotency_keys')) {
    $CI->db->query("
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

/**
 * Webhooks v3 option seeds (visible/editable defaults; the service also
 * falls back to safe values when the options are missing)
 */
if (function_exists('add_option')) {
    if (get_option('api_webhook_delivery_mode') === '') {
        add_option('api_webhook_delivery_mode', 'immediate');
    }
    if (get_option('api_webhook_ssrf_strict') === '') {
        add_option('api_webhook_ssrf_strict', '0');
    }
    if (get_option('api_webhook_ssl_verify') === '') {
        add_option('api_webhook_ssl_verify', '1');
    }
    // MCP server is opt-in (disabled by default)
    if (get_option('api_mcp_enabled') === '') {
        add_option('api_mcp_enabled', '0');
    }
    // Staff-level data visibility is opt-in (default off = full visibility)
    if (get_option('api_staff_visibility') === '') {
        add_option('api_staff_visibility', '0');
    }
    // Failed-auth throttling: attempts per 15 minutes per IP ('0' disables)
    if (get_option('api_auth_throttle_limit') === '') {
        add_option('api_auth_throttle_limit', '20');
    }
    // Third-party custom-table endpoints: opt-in allowlist (empty = nothing exposed)
    if (get_option('api_thirdparty_allowed_tables') === '') {
        add_option('api_thirdparty_allowed_tables', '');
    }
}

/**
 * v3: optional link between an API token and a staff member, used by the
 * opt-in staff-level visibility mode
 */
if ($CI->db->table_exists(db_prefix() . 'user_api')
    && !$CI->db->field_exists('staff_id', db_prefix() . 'user_api')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'user_api` ADD `staff_id` INT(11) NULL DEFAULT NULL AFTER `user`');
}

/**
 * Set default quota values
 */
$CI->db->query("
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

/**
 * TABLE: api_security_log (Version 2.1.5 - Optional Security Audit Table)
 * This table is OPTIONAL and can be used to track security events
 * Uncomment to enable security event logging
 */
/*
if (!$CI->db->table_exists(db_prefix() . 'api_security_log')) {
    $CI->db->query('
        CREATE TABLE `' . db_prefix() . 'api_security_log` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `event_type` VARCHAR(50) NOT NULL COMMENT "sql_injection_attempt, failed_auth, rate_limit_exceeded, etc.",
            `severity` ENUM("low", "medium", "high", "critical") NOT NULL DEFAULT "medium",
            `ip_address` VARCHAR(45) NOT NULL,
            `user_api_id` INT(11) UNSIGNED NULL COMMENT "Reference to user_api.id if authenticated",
            `endpoint` VARCHAR(255) NULL COMMENT "API endpoint accessed",
            `request_method` VARCHAR(10) NULL COMMENT "GET, POST, PUT, DELETE, etc.",
            `user_agent` TEXT NULL,
            `payload` TEXT NULL COMMENT "Suspicious payload or relevant data",
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_event_type` (`event_type`),
            INDEX `idx_severity` (`severity`),
            INDEX `idx_ip_address` (`ip_address`),
            INDEX `idx_created_at` (`created_at`),
            FOREIGN KEY (`user_api_id`) REFERENCES `' . db_prefix() . 'user_api`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT="Security audit log for API module - Version 2.1.5";
    ');
}
*/

/**
 * Record the installed schema version.
 *
 * install.php runs on activation AND via the admin-side self-heal in api.php.
 * Stamping the version here lets the self-heal detect when the schema is
 * already current (stored version >= module version) so it does not re-run
 * install.php on every admin page load after a file-replace upgrade.
 *
 * Guard 1: only stamp once the key additive schema is actually present. In
 * production db_debug is off, so a failed ALTER/CREATE returns false without
 * throwing; stamping unconditionally would mark a still-broken schema "current"
 * and short-circuit the self-heal forever. Guard 2: only write when the value
 * changes, so a no-op reactivation never attempts a duplicate option row.
 */
if (function_exists('update_option')) {
    $api_schema_ok =
        $CI->db->table_exists(db_prefix() . 'user_api')
        && $CI->db->field_exists('staff_id', db_prefix() . 'user_api')
        && $CI->db->table_exists(db_prefix() . 'api_webhook_queue')
        && $CI->db->table_exists(db_prefix() . 'api_idempotency_keys');

    $api_schema_target = defined('API_MODULE_VERSION') ? API_MODULE_VERSION : '3.0.3';

    if ($api_schema_ok && get_option('api_schema_version') !== $api_schema_target) {
        if (!update_option('api_schema_version', $api_schema_target)) {
            if (function_exists('add_option')) {
                add_option('api_schema_version', $api_schema_target);
            }
        }
    }
    unset($api_schema_ok, $api_schema_target);
}