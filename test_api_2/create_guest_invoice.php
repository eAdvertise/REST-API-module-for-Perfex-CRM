<?php
declare(strict_types=1);

// =================== CONFIG ===================
$PERFEX_BASE_URL = 'https://snobprod.eadcloud.eu'; // no trailing slash (or https://domain.com/crm if in subfolder)
$API_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyIjoiIiwibmFtZSI6IiIsIkFQSV9USU1FIjoxNzYxMjg4MjcxfQ.HbezRoVqMFqhMGEdwGbkqHPw_ZfcrasKybRGq7H1HZo';
$API_ENDPOINT = $PERFEX_BASE_URL . '/api/guest_invoices/checkout';
// ==============================================

header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
  http_response_code(400);
  echo json_encode(['status' => false, 'message' => 'Invalid JSON payload']);
  exit;
}

$ch = curl_init($API_ENDPOINT);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_HTTPHEADER => [
    'Content-Type: application/json',
    'authtoken: ' . $API_TOKEN,
  ],
  CURLOPT_POSTFIELDS => json_encode($data),
  CURLOPT_TIMEOUT => 30,
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
    'status' => false,
    'message' => 'Non-JSON response from Perfex (likely a PHP error page).',
    'http_code' => $code,
    'raw' => $response,
  ]);
  exit;
}

http_response_code(($code >= 200 && $code < 600) ? $code : 500);
echo $response;
