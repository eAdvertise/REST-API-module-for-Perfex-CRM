<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_213 extends App_module_migration
{
    public function up()
    {
        $CI =& get_instance();
        
        // Version 2.1.3 migration
        // This version includes:
        // - Dynamic Custom Table Endpoints for third-party modules
        // - CRUD operations for any custom database table
        // - Column validation for insert/update operations
        
        // No database schema changes required for this version
        // The feature allows API access to existing custom tables created by third-party modules
    }
    
    public function down()
    {
        // No rollback needed for this version
    }
}
