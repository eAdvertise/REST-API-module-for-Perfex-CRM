<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_215 extends App_module_migration
{
    public function up()
    {
        $CI =& get_instance();
        
        // Version 2.1.5 - Security Patch
        // This version includes critical security fixes:
        // ============================================
        // 
        // CRITICAL FIXES:
        // - SQL Injection vulnerabilities in search functions (helpers/api_helper.php, models/Api_model.php)
        // - PHP Object Injection via unserialize() (models/Authentication_model.php)
        // - Playground controller public access (controllers/Playground.php)
        // - Postman controller public access (controllers/Postman.php)
        //
        // HIGH PRIORITY FIXES:
        // - CORS wildcard headers (controllers/Login.php)
        // - Hardcoded test credentials removed (config/rest.php)
        // - SSL verification enabled (libraries/Api_aeiou.php)
        //
        // OPTIONAL SECURITY FEATURES (User Configurable):
        // - Token expiration (default 10 years, user can configure)
        // - HTTPS enforcement (optional, disabled by default)
        // - Enhanced CORS configuration
        //
        // BACKWARDS COMPATIBILITY: 99.9%
        // - All existing API tokens continue working
        // - All endpoints function identically
        // - "Remember Me" autologin cookies need one-time re-login (security fix)
        //
        // NO DATABASE SCHEMA CHANGES REQUIRED
        // All fixes are code-level security improvements
        
        // Optional: Add security audit log table (for future use)
        // Uncomment if you want to track security events
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT="Security audit log for API module";
            ');
        }
        */
        
        // Add migration completion log
        log_activity('API Module Security Patch 2.1.5 Applied - Critical security vulnerabilities fixed');
    }
    
    public function down()
    {
        $CI =& get_instance();
        
        // No rollback needed for this security patch
        // Rolling back security fixes would be a security risk
        // 
        // Note: If api_security_log table was created, you can drop it here
        /*
        if ($CI->db->table_exists(db_prefix() . 'api_security_log')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'api_security_log`');
        }
        */
        
        log_activity('API Module Security Patch 2.1.5 Rollback Attempted - Security fixes remain in place');
    }
}
