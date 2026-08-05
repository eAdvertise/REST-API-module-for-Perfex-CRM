<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * v3.0.1 upgrade migration
 *
 * Code-only bug-fix release - no schema or option changes:
 *  - Estimates_model::get_attachments() missing $playground parameter
 *    (DELETE /api/estimates/{id} "Undefined variable $playground" fatal).
 *  - Parse_Input_Stream used the Laravel Log facade (fatal under CodeIgniter);
 *    now uses CI log_message().
 *  - All 8 controllers loaded the library as 'parse_input_stream' instead of
 *    'Parse_Input_Stream' (failed on case-sensitive/Linux filesystems).
 *  - Expenses PUT attachment: is_uploaded_file()-aware move so files parsed from
 *    a raw PUT body persist, honest attachments_saved reporting, and a field-only
 *    PUT no longer wipes existing attachments.
 *
 * up() is intentionally a no-op; it exists so the module migration version stays
 * in lockstep with the module version (3.0.1 -> 301).
 */
class Migration_Version_301 extends App_module_migration
{
    public function up()
    {
        // No database changes in this release.
    }
}
