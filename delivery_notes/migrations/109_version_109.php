<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_109 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'invoices';
        if (!$CI->db->field_exists('delivery_noteid', $table)) {
            $CI->db->query("ALTER TABLE `$table` ADD `delivery_noteid` VARCHAR(200) DEFAULT NULL");
        } else {
            $CI->db->query("ALTER TABLE `$table` CHANGE `delivery_noteid` `delivery_noteid` VARCHAR(200) NULL DEFAULT NULL");
        }
        require(__DIR__ . '/../install.php');
    }

    public function down()
    {
    }
}