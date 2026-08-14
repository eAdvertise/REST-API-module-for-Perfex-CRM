<?php
/** eAD-CRM API v3 optional-module examples (PHP / cURL). */
const BASE = 'https://yourdomain.com/api';
const TOKEN = 'YOUR_API_TOKEN';
function eadcrm_module_request(string $method, string $path, ?array $payload = null): array {
    $headers = ['authtoken: ' . TOKEN, 'Accept: application/json'];
    $options = [CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => $method];
    if ($payload !== null) { $headers[] = 'Content-Type: application/json'; $options[CURLOPT_POSTFIELDS] = json_encode($payload); }
    $options[CURLOPT_HTTPHEADER] = $headers; $ch = curl_init(BASE . $path); curl_setopt_array($ch, $options);
    $body = curl_exec($ch); $status = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    return ['status' => $status, 'data' => json_decode((string) $body, true)];
}
print_r(eadcrm_module_request('GET', '/warehouse/inventory?page=1&per_page=25'));
print_r(eadcrm_module_request('POST', '/paymentsonaccount/receipts', ['client_id'=>12,'amount'=>150,'payment_mode'=>'1','invoice_ids'=>[35]]));
print_r(eadcrm_module_request('POST', '/delivery_notes/notes', ['clientid'=>12,'currency'=>1,'date'=>'2026-08-14','newitems'=>[['description'=>'Delivered item','qty'=>2,'rate'=>25]]]));
print_r(eadcrm_module_request('GET', '/commission/commissions?page=1&per_page=25'));
print_r(eadcrm_module_request('POST', '/myshopify/sync'));
print_r(eadcrm_module_request('POST', '/purchase/requests', ['currency'=>1,'subtotal'=>100,'newitems'=>[['item_code'=>10,'quantity'=>2,'unit_price'=>50]]]));
print_r(eadcrm_module_request('POST', '/guest_invoices/checkout', ['email'=>'guest@example.com','amount'=>100,'paymentmode'=>1]));
