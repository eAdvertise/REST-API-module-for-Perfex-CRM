<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_216 extends App_module_migration
{
    public function up()
    {
        // Version 2.1.6 - Items API permissions UI fix
        // =============================================
        //
        // BUGFIX: The Items module in API Settings only exposed "List" and "Search"
        // (secondPermissionsArray), so admins could not grant post/put/delete in the UI.
        // PUT /api/items/:id and other write operations require capabilities that were
        // never shown as checkboxes, causing 403 even when documented endpoints exist.
        //
        // CHANGE: helpers/api_helper.php — items now uses $firstPermissionsArray
        // (get, search_get, post, put, delete) like invoices, customers, etc.
        //
        // NO DATABASE SCHEMA CHANGES.
        // Existing rows in user_api_permissions are unchanged; admins can now select
        // Create / Update / Delete for Items in Setup → API and save.
    }

    public function down()
    {
        // No schema rollback
    }
}
