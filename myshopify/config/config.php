<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Shopify cannot send a Perfex CSRF token. Authenticity is instead enforced
// by the mandatory X-Shopify-Hmac-Sha256 verification in the controller.
if (!isset($config['csrf_exclude_uris']) || !is_array($config['csrf_exclude_uris'])) {
    $config['csrf_exclude_uris'] = [];
}
$config['csrf_exclude_uris'][] = 'myshopify/webhook';
