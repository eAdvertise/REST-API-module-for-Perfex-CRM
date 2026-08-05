<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->field_exists('opening_balance', db_prefix() . 'payment_modes'))
{

    $CI->db->query("ALTER TABLE `".db_prefix()."payment_modes`
                            ADD COLUMN `opening_balance` decimal(15, 2) NULL AFTER `active`,
                            ADD COLUMN `payment_currency`  varchar(10) DEFAULT 'USD' AFTER `opening_balance` " );

}



if (!$CI->db->table_exists(db_prefix() . 'payment_modes_transfer'))
{
    $CI->db->query("CREATE TABLE `".db_prefix()."payment_modes_transfer` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                      `source_mode` varchar(100) DEFAULT NULL,
                      `target_mode` varchar(100) DEFAULT NULL,
                      `source_amount` decimal(15,2) DEFAULT NULL,
                      `target_amount` decimal(15,2) DEFAULT NULL,
                      `date` datetime DEFAULT NULL,
                      `staffid` int(11) DEFAULT NULL,
                      `description` varchar(500) DEFAULT NULL,
                      `transfer_date` date DEFAULT NULL,
                      PRIMARY KEY (`id`) USING BTREE,
                      KEY `id` (`id`) USING BTREE,
                      KEY `source_mode` (`source_mode`) USING BTREE,
                      KEY `target_mode` (`target_mode`) USING BTREE
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ROW_FORMAT=DYNAMIC;"
    );
}


