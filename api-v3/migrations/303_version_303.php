<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * v3.0.3 upgrade migration
 *
 * Code-only release - no schema or option changes. Everything below is either
 * new controller/helper/view code or reuses existing core tables:
 *
 *  - Task comments CRUD over the API (uses the existing core tbltask_comments):
 *      GET    /api/tasks/{id}/comments
 *      POST   /api/tasks/{id}/comments
 *      PUT    /api/tasks/{task_id}/comments/{comment_id}
 *      DELETE /api/tasks/{task_id}/comments/{comment_id}
 *    gated by the existing Tasks get/post/put/delete permissions.
 *
 *  - Upgrade/uninstall lifecycle hardening (api.php, install.php, uninstall.php):
 *      * schema self-heal so file-overwrite upgrades apply pending changes
 *        automatically on the next admin load (fixes "Unknown column" after a
 *        files-only upgrade), throttled and admin-gated;
 *      * uninstall.php removes only the module's own options and keeps every
 *        data table (tokens, permissions, webhooks, logs).
 *
 *  - Permission completeness + UX: Notes and Knowledge Base permission rows are
 *    now shown on the token screen (Knowledge Base groups fold onto the standard
 *    get/post/put/delete), and the screen gained Select all / Read-only / Clear
 *    all controls plus per-feature toggles.
 *
 * up() is intentionally a no-op; it keeps the migration version in lockstep with
 * the module version (3.0.3 -> 303).
 */
class Migration_Version_303 extends App_module_migration
{
    public function up()
    {
        // No database changes in this release.
    }
}
