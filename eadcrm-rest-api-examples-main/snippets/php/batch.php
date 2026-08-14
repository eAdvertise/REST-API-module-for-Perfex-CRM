<?php
/**
 * eAD-CRM REST API — Batch examples (PHP / cURL)
 * Module: https://www.eadvertise.eu/
 *
 * Batch runs up to 50 operations in one request using the same tool names
 * as MCP. It POSTs a JSON body (Content-Type: application/json).
 *
 * No external dependencies — uses the built-in cURL extension.
 */

const BASE  = 'https://yourdomain.com/api';
const TOKEN = 'YOUR_API_TOKEN';

/** JSON request helper (sends a JSON body). */
function eadcrm_request_json(string $method, string $path, array $payload = []): array
{
    $ch = curl_init(BASE . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => [
            'authtoken: ' . TOKEN,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload),
    ]);
    $body   = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['status' => $status, 'data' => json_decode((string) $body, true)];
}

// Run several operations in a single round-trip
print_r(eadcrm_request_json('POST', '/batch', [
    'operations' => [
        ['tool' => 'customers_create', 'args' => ['company' => 'Acme LTD']],
        ['tool' => 'invoices_get',     'args' => ['id' => 1]],
    ],
]));
