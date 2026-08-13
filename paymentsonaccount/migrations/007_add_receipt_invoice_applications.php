<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_receipt_invoice_applications
{
    public function up()
    {
        $CI = &get_instance();
        if (!$CI->db->table_exists(db_prefix().'receipt_invoice_applications')) {
            $CI->db->query("
                CREATE TABLE `".db_prefix()."receipt_invoice_applications` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `receipt_id` INT NOT NULL,
                    `invoice_id` INT NOT NULL,
                    `amount` DECIMAL(15,2) NOT NULL,
                    `payment_record_id` INT DEFAULT NULL,
                    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `receipt_id` (`receipt_id`),
                    KEY `invoice_id` (`invoice_id`),
                    KEY `payment_record_id` (`payment_record_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }
    }

    public function down()
    {
        if ($CI->db->table_exists(db_prefix().'receipt_invoice_applications')) {
            $CI->db->query("DROP TABLE `".db_prefix()."receipt_invoice_applications`;");
        }
    }
}
