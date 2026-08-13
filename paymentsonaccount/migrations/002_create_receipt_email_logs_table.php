<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_Receipt_Email_Logs_Table
{
    public function up()
    {
        $CI = &get_instance();
		$CI->db->query("
            CREATE TABLE IF NOT EXISTS `tblreceiptemaillogs` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `receipt_id` INT NOT NULL,
                `staff_id` INT NULL,
                `recipient` VARCHAR(255) NOT NULL,
                `date_sent` DATETIME NOT NULL,
                `status` VARCHAR(50) NOT NULL,
                `message` TEXT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down()
    {
        $this->dbforge->drop_table('tblreceiptemaillogs', true);
    }
}
