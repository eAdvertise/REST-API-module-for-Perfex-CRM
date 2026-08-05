<?php
defined('BASEPATH') || exit('No direct script access allowed');

add_option('webhooks_enabled', 1);

// My files
$my_files_list = [
    APPPATH . 'config/my_hooks.php' => module_dir_path(WEBHOOKS_MODULE, '/resources/application/config/my_hooks.php'),
];

// Copy each file in $my_files_list to its actual path if it doesn't already exist
foreach ($my_files_list as $actual_path => $resource_path) {
    if (!file_exists($actual_path)) {
        copy($resource_path, $actual_path);
    }
}

// Backup files
$backup_files_list = [
	APPPATH . 'models/Contracts_model.php' => module_dir_path(WEBHOOKS_MODULE, '/resources/application/models/Contracts_model.php'),
];

foreach ($backup_files_list as $actual_path => $resource_path) {
	if (file_exists($actual_path) && !file_exists($actual_path . '.backup')) {
		rename($actual_path, $actual_path . '.backup');
	}
	if (!file_exists($actual_path)) {
		copy($resource_path, $actual_path);
	}
}


if (!$CI->db->table_exists(db_prefix().'webhooks_master')) {
    $CI->db->query('CREATE TABLE `'.db_prefix().'webhooks_master` (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `name` VARCHAR(200) NOT NULL ,
    `webhook_for` VARCHAR(50) NOT NULL ,
    `webhook_action` TEXT NOT NULL ,
    `request_url` TEXT NOT NULL ,
    `active` TINYINT NOT NULL DEFAULT "1",
    `request_method` VARCHAR(100) NOT NULL ,
    `request_format` VARCHAR(20) NOT NULL ,
    `request_header` TEXT NOT NULL ,
    `request_body` TEXT NOT NULL ,
    `debug_mode` TINYINT NOT NULL DEFAULT "0",
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `webhook_after_number` int DEFAULT NULL,
    `webhook_after_type` varchar(20) DEFAULT NULL,
    PRIMARY KEY (`id`)) ENGINE = InnoDB DEFAULT CHARSET='.$CI->db->char_set.';');
}

if (!$CI->db->table_exists(db_prefix().'webhooks_debug_log')) {
    $CI->db->query('CREATE TABLE `'.db_prefix().'webhooks_debug_log` (
        `id` INT NOT NULL AUTO_INCREMENT ,
        `webhook_action_name` VARCHAR(200) NOT NULL ,
        `request_url` TEXT NOT NULL ,
        `webhook_for` VARCHAR(50) NOT NULL ,
        `webhook_action` TEXT NOT NULL ,
        `request_method` VARCHAR(100) NOT NULL ,
        `request_format` VARCHAR(20) NOT NULL ,
        `request_header` TEXT NOT NULL ,
        `request_body` TEXT NOT NULL ,
        `response_code` VARCHAR(4) Not NULL,
        `response_data` text Not NULL,
        `recorded_at` datetime NULL,
        PRIMARY KEY (`id`)) ENGINE = InnoDB DEFAULT CHARSET='.$CI->db->char_set.';');
}

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

$CI->load->helper('webhooks/webhooks');
$hookOptions = get_hooks_list();
$content = '';
write_file(TEMP_FOLDER . $hookOptions['hook_content'] . '.lic', $content);

/*End of file install.php */
