<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_102 extends App_module_migration
{

    public function up()
    {

        $CI = &get_instance();

        if (!$CI->db->table_exists(db_prefix() . 'payment_modes_income'))
        {
            $CI->db->query("CREATE TABLE `".db_prefix()."payment_modes_income` (
                         `id` int(11) NOT NULL AUTO_INCREMENT,
                          `source_mode` varchar(100) DEFAULT NULL,
                          `client_id` int(11) DEFAULT NULL,
                          `staff_id` int(11) DEFAULT NULL,
                          `amount` decimal(15,2) DEFAULT NULL,
                          `date` date DEFAULT NULL,
                          `description` varchar(500) DEFAULT NULL,
                          `added_from` int(11) DEFAULT NULL,
                          `added_date` datetime DEFAULT NULL,
                          PRIMARY KEY (`id`) USING BTREE,
                          KEY `client_id` (`client_id`) USING BTREE,
                          KEY `staff_id` (`staff_id`) USING BTREE
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ROW_FORMAT=DYNAMIC;"
            );
        }

    }

}
