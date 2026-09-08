<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!class_exists('Migration_Version_200', false)) {
    class Migration_Version_200 extends App_module_migration
    {
        public function up()
        {
            // Version 2.0.0 contains application changes only. Perfex still needs
            // this migration to advance its stored module database version.
        }
    }
}
