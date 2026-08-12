<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// Add options for storing module settings.
add_option('myshopify_purchase_is_valid', 1);
add_option('shopify_access_token', '');
add_option('shopify_shop_url', '');
add_option('my_shopify_webhook_secret', '');
add_option('my_shopify_location_id', '');
add_option('my_shopify_warehouse_id', '');
add_option('my_shopify_last_sync', '');
add_option('my_shopify_sync_enabled', '1');
add_option('my_shopify_api_version', '2026-07');

/*
 * Older MySQL releases reject CURRENT_TIMESTAMP as a DATETIME default.
 * Probe the actual server capability instead of relying on a version string,
 * which can be unreliable on MariaDB and vendor-patched hosting builds.
 */
$probeTable = db_prefix() . 'myshopify_datetime_probe';
$supportsAutomaticDatetime = false;

try {
    $CI->db->query("DROP TABLE IF EXISTS `{$probeTable}`");
    $CI->db->query("
        CREATE TABLE `{$probeTable}` (
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    $supportsAutomaticDatetime = true;
} catch (Throwable $e) {
    // The legacy definitions below avoid automatic DATETIME defaults.
    $supportsAutomaticDatetime = false;
} finally {
    try {
        $CI->db->query("DROP TABLE IF EXISTS `{$probeTable}`");
    } catch (Throwable $e) {
        log_message('error', 'MyShopify: failed to remove datetime capability probe table: ' . $e->getMessage());
    }
}

$automaticCreatedAt = $supportsAutomaticDatetime
    ? '`created_at` DATETIME DEFAULT CURRENT_TIMESTAMP'
    : '`created_at` DATETIME DEFAULT NULL';

$automaticUpdatedAt = $supportsAutomaticDatetime
    ? '`updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    : '`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';

// Table: Products
$CI->db->query("
    CREATE TABLE IF NOT EXISTS `" . db_prefix() . "myshopify_products` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `shopify_id` BIGINT DEFAULT NULL,
        `title` VARCHAR(255) DEFAULT NULL,
        `price` DECIMAL(10,2) DEFAULT NULL,
        `stock` INT DEFAULT NULL,
        `image_url` VARCHAR(255) DEFAULT NULL,
        `shopify_url` VARCHAR(255) DEFAULT NULL,
        `created_by` INT(11) DEFAULT NULL,
        {$automaticCreatedAt},
        {$automaticUpdatedAt}
    );
");

// Table: Customers
$CI->db->query("
    CREATE TABLE IF NOT EXISTS `" . db_prefix() . "myshopify_customers` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `shopify_customer_id` BIGINT UNIQUE NOT NULL,
        `first_name` VARCHAR(100) DEFAULT NULL,
        `last_name` VARCHAR(100) DEFAULT NULL,
        `email` VARCHAR(150) DEFAULT NULL,
        `phone` VARCHAR(50) DEFAULT NULL,
        `created_at` DATETIME DEFAULT NULL,
        {$automaticUpdatedAt}
    );
");

// Table: Orders
$CI->db->query("
    CREATE TABLE IF NOT EXISTS `" . db_prefix() . "myshopify_orders` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `shopify_order_id` BIGINT UNIQUE NOT NULL,
        `order_number` VARCHAR(100) DEFAULT NULL,
        `customer_email` VARCHAR(150) DEFAULT NULL,
        `customer_name` VARCHAR(150) DEFAULT NULL,
        `total_price` DECIMAL(10,2) DEFAULT 0,
        `currency` VARCHAR(10) DEFAULT NULL,
        `financial_status` VARCHAR(100) DEFAULT NULL,
        `created_at` DATETIME DEFAULT NULL,
        `shipping_name` VARCHAR(255) DEFAULT NULL,
        `shipping_company` VARCHAR(255) DEFAULT NULL,
        `shipping_city` VARCHAR(100) DEFAULT NULL,
        `shipping_province` VARCHAR(100) DEFAULT NULL,
        `shipping_country` VARCHAR(100) DEFAULT NULL,
        `shipping_zip` VARCHAR(20) DEFAULT NULL,
        `shipping_phone` VARCHAR(50) DEFAULT NULL,
        `billing_name` VARCHAR(255) DEFAULT NULL,
        `billing_company` VARCHAR(255) DEFAULT NULL,
        `billing_city` VARCHAR(100) DEFAULT NULL,
        `billing_province` VARCHAR(100) DEFAULT NULL,
        `billing_country` VARCHAR(100) DEFAULT NULL,
        `billing_zip` VARCHAR(20) DEFAULT NULL,
        `billing_phone` VARCHAR(50) DEFAULT NULL,
        `subtotal_price` DECIMAL(10,2) DEFAULT 0,
        `total_tax` DECIMAL(10,2) DEFAULT 0,
        `total_discounts` DECIMAL(10,2) DEFAULT 0,
        `line_items` LONGTEXT,
        {$automaticUpdatedAt}
    );
");

// Table: Categories
$CI->db->query("
    CREATE TABLE IF NOT EXISTS `" . db_prefix() . "myshopify_categories` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `shopify_id` BIGINT UNIQUE NOT NULL,
        `name` VARCHAR(255) NOT NULL,
        `image_url` VARCHAR(255) DEFAULT NULL,
        {$automaticCreatedAt},
        {$automaticUpdatedAt}
    );
");

// Table: Discounts
$CI->db->query("
    CREATE TABLE IF NOT EXISTS `" . db_prefix() . "myshopify_discounts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `shopify_discount_id` BIGINT UNIQUE NOT NULL,
        `code` VARCHAR(100) NOT NULL,
        `price_rule_id` BIGINT,
        `title` VARCHAR(255) DEFAULT NULL,
        `value` VARCHAR(50) DEFAULT NULL,
        `value_type` VARCHAR(50) DEFAULT NULL,
        `starts_at` DATETIME,
        `ends_at` DATETIME,
        `usage_limit` INT DEFAULT NULL,
        {$automaticCreatedAt},
        {$automaticUpdatedAt}
    );
");

/*
 * Legacy fallback: updated_at is handled by the one automatic TIMESTAMP
 * column permitted by older servers. Preserve the original created_at
 * behavior for locally-created products, categories, and discounts with a
 * lightweight BEFORE INSERT trigger. If the hosting account lacks TRIGGER
 * permission, installation still succeeds and application inserts should
 * explicitly provide created_at.
 */
if (!$supportsAutomaticDatetime) {
    $createdAtTriggerTables = array(
        'myshopify_products',
        'myshopify_categories',
        'myshopify_discounts',
    );

    foreach ($createdAtTriggerTables as $tableSuffix) {
        $tableName = db_prefix() . $tableSuffix;
        $triggerName = db_prefix() . $tableSuffix . '_set_created_at';

        try {
            $CI->db->query("DROP TRIGGER IF EXISTS `{$triggerName}`");
            $CI->db->query("
                CREATE TRIGGER `{$triggerName}`
                BEFORE INSERT ON `{$tableName}`
                FOR EACH ROW
                SET NEW.`created_at` = COALESCE(NEW.`created_at`, CURRENT_TIMESTAMP)
            ");
        } catch (Throwable $e) {
            log_message(
                'error',
                'MyShopify: could not create trigger ' . $triggerName
                . '. Ensure inserts supply created_at explicitly. Database error: '
                . $e->getMessage()
            );
        }
    }
}

// Durable cross-system identities and timestamps. These tables make every
// webhook/cron operation idempotent and allow newest-write-wins conflict
// resolution (Perfex wins when timestamps are equal).
$CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "myshopify_product_map` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `shopify_product_id` BIGINT UNSIGNED NOT NULL,
    `shopify_variant_id` BIGINT UNSIGNED NOT NULL,
    `shopify_inventory_item_id` BIGINT UNSIGNED DEFAULT NULL,
    `perfex_item_id` INT UNSIGNED NOT NULL,
    `sku` VARCHAR(191) NOT NULL,
    `shopify_updated_at` DATETIME DEFAULT NULL,
    `perfex_updated_at` DATETIME DEFAULT NULL,
    `last_synced_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`), UNIQUE KEY `variant` (`shopify_variant_id`),
    KEY `sku` (`sku`), KEY `perfex_item` (`perfex_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "myshopify_customer_map` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `shopify_customer_id` BIGINT UNSIGNED DEFAULT NULL,
    `perfex_client_id` INT UNSIGNED NOT NULL,
    `perfex_contact_id` INT UNSIGNED DEFAULT NULL,
    `email` VARCHAR(191) NOT NULL,
    `shopify_updated_at` DATETIME DEFAULT NULL,
    `perfex_updated_at` DATETIME DEFAULT NULL,
    `last_synced_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`), UNIQUE KEY `email` (`email`),
    KEY `shopify_customer` (`shopify_customer_id`), KEY `perfex_client` (`perfex_client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "myshopify_category_map` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `shopify_collection_id` BIGINT UNSIGNED NOT NULL,
    `perfex_group_id` INT UNSIGNED NOT NULL,
    `shopify_updated_at` DATETIME DEFAULT NULL,
    `perfex_updated_at` DATETIME DEFAULT NULL,
    `last_synced_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`), UNIQUE KEY `collection` (`shopify_collection_id`),
    KEY `perfex_group` (`perfex_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "myshopify_order_map` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `shopify_order_id` BIGINT UNSIGNED NOT NULL,
    `perfex_invoice_id` INT UNSIGNED DEFAULT NULL,
    `financial_status` VARCHAR(40) DEFAULT NULL,
    `shopify_updated_at` DATETIME DEFAULT NULL,
    `processed_at` DATETIME DEFAULT NULL,
    `last_error` TEXT DEFAULT NULL,
    PRIMARY KEY (`id`), UNIQUE KEY `shopify_order` (`shopify_order_id`),
    KEY `perfex_invoice` (`perfex_invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "myshopify_sync_log` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `direction` VARCHAR(30) NOT NULL, `entity` VARCHAR(30) NOT NULL,
    `external_id` VARCHAR(191) DEFAULT NULL, `status` VARCHAR(20) NOT NULL,
    `message` TEXT DEFAULT NULL, `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`), KEY `created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
