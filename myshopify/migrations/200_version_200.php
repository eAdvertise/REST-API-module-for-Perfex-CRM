<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_200 extends App_module_migration
{
    public function up()
    {
        // The installer is deliberately idempotent (CREATE IF NOT EXISTS and
        // add_option), so existing 1.x installations receive all 2.0 maps and
        // settings without losing their imported Shopify cache.
        require APP_MODULES_PATH . 'myshopify/install.php';
    }
}
