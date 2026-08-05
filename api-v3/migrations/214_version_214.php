<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_214 extends App_module_migration
{
    public function up()
    {
        $CI =& get_instance();
        
        // Version 2.1.4 migration
        // This version includes:
        // - Complete CRUD operations for Items (tblitems table)
        // - GET all tickets endpoint with pagination
        // - Global pagination support for all list endpoints
        // - Pagination parameters: ?page=X&per_page=Y (default: 20, max: 100)
        // - Standardized pagination response format with data and meta sections
        // - Updated Swagger/OpenAPI documentation with global pagination parameters
        // - Applied pagination to: Items, Tickets, Leads, Customers, Invoices, Estimates, Projects, Tasks
        
        // No database schema changes required for this version
        // Using existing tblitems table structure:
        // - id (int)
        // - description (longtext, required)
        // - long_description (mediumtext, optional)
        // - rate (decimal 15,2, required)
        // - tax (int, optional - references tax ID)
        // - tax2 (int, optional - references secondary tax ID)
        // - unit (varchar 40, optional)
        // - group_id (int, default 0)
    }
    
    public function down()
    {
        // No rollback needed for this version
        // All changes are API-level enhancements with no database modifications
    }
}
