<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Some Perfex installations discover/include a module migration more than
 * once during the same request. Migration classes use global names, so an
 * unguarded second include causes a fatal class redeclaration. Keep the class
 * name Perfex expects, but make loading this file idempotent.
 */
if (!class_exists('Migration_Version_200', false)) {
    class Migration_Version_200 extends App_module_migration
    {
        public function up()
        {
            $CI = &get_instance();
            if ($CI->db->table_exists(db_prefix() . 'myshopify_product_map')) {
                return;
            }

            require APP_MODULES_PATH . 'myshopify/install.php';
        }
    }
}
