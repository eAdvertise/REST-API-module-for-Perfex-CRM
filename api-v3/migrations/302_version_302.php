<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * v3.0.2 upgrade migration
 *
 *  - SECURITY: the Third-Party Custom Tables endpoints
 *    (GET/POST/PUT/DELETE /api/thirdparty/customtable/...) are now restricted to an
 *    opt-in allowlist (Setup -> API -> Settings), with a hardcoded denylist of core /
 *    credential tables that is always enforced (tbloptions, tblstaff, tbluser_api,
 *    roles, sessions, migrations, ...). Seeds the (empty) allowlist option, so the
 *    feature is disabled by default until an admin explicitly lists tables.
 *  - FEATURE: POST /api/invoices/{id}/send emails an invoice through the CRM's own
 *    template, PDF and SMTP, gated by a dedicated Invoices "Send email" permission.
 */
class Migration_Version_302 extends App_module_migration
{
    public function up()
    {
        if (function_exists('add_option')
            && function_exists('option_exists')
            && !option_exists('api_thirdparty_allowed_tables')) {
            add_option('api_thirdparty_allowed_tables', '');
        }
    }
}
