<?php
declare(strict_types=1);

// =================== CONFIG ===================
// Do not hardcode API tokens in this file. Configure these values in the
// webserver/PHP-FPM environment instead, for example:
// PERFEX_BASE_URL=https://example.com
// PERFEX_API_TOKEN=your-token
$perfexBaseUrl = rtrim((string)getenv('PERFEX_BASE_URL'), '/');
$apiToken      = (string)getenv('PERFEX_API_TOKEN');

if ($perfexBaseUrl === '' || $apiToken === '') {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status'  => false,
        'message' => 'Missing PERFEX_BASE_URL or PERFEX_API_TOKEN environment configuration.',
    ]);
    exit;
}

$apiEndpoint = $perfexBaseUrl . '/api/guest_invoices/checkout';
// ==============================================

header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Invalid JSON payload']);
    exit;
}

$ch = curl_init($apiEndpoint);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'authtoken: ' . $apiToken,
    ],
    CURLOPT_POSTFIELDS     => json_encode($data),
    CURLOPT_TIMEOUT        => 30,
]);

$response = curl_exec($ch);
$err = curl_error($ch);
$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => 'cURL error', 'error' => $err]);
    exit;
}

$trim = ltrim($response);
if ($trim !== '' && $trim[0] === '<') {
    http_response_code(500);
    echo json_encode([
        'status'    => false,
        'message'   => 'Non-JSON response from Perfex (likely a PHP error page).',
        'http_code' => $code,
        'raw'       => $response,
    ]);
    exit;
}

http_response_code(($code >= 200 && $code < 600) ? $code : 500);
echo $response;
