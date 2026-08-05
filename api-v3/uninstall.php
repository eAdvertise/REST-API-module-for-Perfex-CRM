<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * API module uninstall handler.
 *
 * Perfex includes this file when the module is removed from Setup > Modules.
 *
 * Policy (deliberate): uninstalling removes ONLY the module's own options
 * (license/verification state, settings, and cached update info). Every data
 * table is intentionally KEPT - user_api tokens, permissions, webhooks,
 * usage logs, queues and idempotency keys - so that:
 *
 *   1. an accidental uninstall never destroys customer API credentials, and
 *   2. a later reinstall reuses the existing (idempotent) schema seamlessly.
 *
 * install.php is fully idempotent, so reinstalling on top of the kept tables
 * is safe. A full data wipe, if ever required, must be done manually.
 */

if (function_exists('delete_option')) {
    $api_module_options = [
        // Settings / configuration
        'api_enable_transformers',
        'api_middleware_config',
        'api_mcp_enabled',
        'api_staff_visibility',
        'api_webhook_ssrf_strict',
        'api_webhook_ssl_verify',
        'api_webhook_delivery_mode',
        'api_auth_throttle_limit',
        'api_thirdparty_allowed_tables',
        'api_webhook_queue_last_run',

        // Self-update cache
        'api_update_info',
        'api_update_last_check',
        'api_update_lock',

        // Schema self-heal tracking (added in 3.0.3)
        'api_schema_version',
        'api_schema_heal_attempt',

        // License / verification state (also cleared on deactivate)
        'api_verification_id',
        'api_last_verification',
        'api_supported_until',
    ];

    foreach ($api_module_options as $api_option_name) {
        delete_option($api_option_name);
    }

    unset($api_module_options, $api_option_name);
}
