<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_106 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();          
        if (!$CI->db->field_exists('commission_type', db_prefix() . 'commission_policy')) {
		    $CI->db->query('ALTER TABLE `' . db_prefix() . "commission_policy`
		    ADD COLUMN `commission_type` VARCHAR(45) NOT NULL DEFAULT 'percentage';");
		}
    }
}
