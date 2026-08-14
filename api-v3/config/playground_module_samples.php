<?php

defined('BASEPATH') or exit('No direct script access allowed');

$sample = function ($method, $endpoint, $description, array $data = []) {
    return ['method' => $method, 'endpoint' => $endpoint, 'headers' => 'authtoken: YOUR_API_KEY',
        'data' => $data ? json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '', 'description' => $description];
};

return [
    'warehouse_inventory' => $sample('GET', 'warehouse/inventory?page=1&per_page=25', 'List warehouse inventory balances'),
    'warehouse_create_receipt' => $sample('POST', 'warehouse/receipts', 'Create a warehouse goods receipt', ['warehouse_id' => 1, 'supplier_code' => 'SUP-1', 'newitems' => [['commodity_code' => 10, 'quantities' => 5]]]),
    'poa_create_receipt' => $sample('POST', 'paymentsonaccount/receipts', 'Create and allocate a receipt', ['client_id' => 12, 'amount' => 150, 'payment_mode' => '1', 'invoice_ids' => [35]]),
    'delivery_note_create' => $sample('POST', 'delivery_notes/notes', 'Create a delivery note', ['clientid' => 12, 'currency' => 1, 'date' => date('Y-m-d'), 'newitems' => [['description' => 'Delivered item', 'qty' => 2, 'rate' => 25]]]),
    'delivery_note_status' => $sample('PUT', 'delivery_notes/notes/42/status', 'Mark a delivery note delivered', ['status' => 4]),
    'commission_list' => $sample('GET', 'commission/commissions?page=1&per_page=25', 'List calculated commissions'),
    'commission_recalculate' => $sample('POST', 'commission/recalculate', 'Recalculate invoice commissions', ['invoice_ids' => [101, 102]]),
    'myshopify_sync' => $sample('POST', 'myshopify/sync', 'Run MyShopify reconciliation'),
    'myshopify_logs' => $sample('GET', 'myshopify/logs?status=error', 'Inspect failed synchronization logs'),
    'purchase_create_request' => $sample('POST', 'purchase/requests', 'Create a purchase request', ['currency' => 1, 'subtotal' => 100, 'newitems' => [['item_code' => 10, 'quantity' => 2, 'unit_price' => 50]]]),
    'purchase_order_payment' => $sample('POST', 'purchase/orders/42/payments', 'Add a purchase-order payment', ['amount' => 100, 'paymentmode' => 1, 'date' => date('Y-m-d')]),
    'guest_invoice_checkout' => $sample('POST', 'guest_invoices/checkout', 'Create and pay a guest invoice', ['email' => 'guest@example.com', 'amount' => 100, 'paymentmode' => 1]),
];
