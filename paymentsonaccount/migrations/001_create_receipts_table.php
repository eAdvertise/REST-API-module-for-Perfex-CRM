<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_Receipts_Table
{
    public function up()
    {
		$CI = &get_instance();
		$CI->db->query("
            CREATE TABLE IF NOT EXISTS `tblreceipts` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `receipt_number` VARCHAR(50) NOT NULL,
                `client_id` INT NOT NULL,
                `total_amount` DECIMAL(15,2) NOT NULL,
                `date_created` DATETIME NOT NULL,
                `invoices_applied` TEXT NULL,
                `staff_id` INT NULL,
                `payment_method_id` INT NULL,
                `note` TEXT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Initialize options
        if (get_option('next_receipt_number') === false) {
            add_option('next_receipt_number', 1);
        }

        if (get_option('receipt_number_prefix') === false) {
            add_option('receipt_number_prefix', 'REC-');
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('tblreceipts', true);
        delete_option('next_receipt_number');
        delete_option('receipt_number_prefix');
    }
}
