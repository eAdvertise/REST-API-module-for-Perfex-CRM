<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_105 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();   
        if (!$CI->db->field_exists('ladder_product_setting', db_prefix() . 'commission_policy')) {
		    $CI->db->query('ALTER TABLE `' . db_prefix() . "commission_policy`
		    ADD COLUMN `ladder_product_setting` LONGTEXT NULL;");
		}       
        if (!$CI->db->field_exists('amount_to_calculate', db_prefix() . 'commission_policy')) {
		    $CI->db->query('ALTER TABLE `' . db_prefix() . "commission_policy`
		    ADD COLUMN `amount_to_calculate` VARCHAR(45) NOT NULL DEFAULT '0';");
		}
    }
}
