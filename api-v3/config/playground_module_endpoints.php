<?php

defined('BASEPATH') or exit('No direct script access allowed');

$category = function ($name, $description, array $rows) {
    return ['name' => $name, 'description' => $description, 'endpoints' => array_map(function ($row) {
        return ['method' => $row[0], 'path' => $row[1], 'description' => $row[2]];
    }, $rows)];
};

$crud = function ($base, $label, $write = true) {
    $rows = [['GET', $base, 'List ' . $label], ['GET', $base . '/{id}', 'Get ' . $label . ' by ID']];
    if ($write) {
        array_splice($rows, 1, 0, [['POST', $base, 'Create ' . $label]]);
        $rows[] = ['PUT', $base . '/{id}', 'Update ' . $label];
        $rows[] = ['DELETE', $base . '/{id}', 'Delete ' . $label];
    }
    return $rows;
};

$catalog = [];
$warehouseRows = [['GET', '/warehouse', 'Discover Warehouse resources']];
foreach ([
    'warehouses', 'items', 'receipts', 'deliveries', 'transfers', 'adjustments', 'commodity_types', 'commodity_groups',
    'sub_groups', 'units', 'sizes', 'styles', 'bodies', 'colors', 'brands', 'models', 'series', 'inventory_minimums',
    'serial_numbers', 'stock_takes', 'packing_lists', 'order_returns', 'approval_settings', 'approval_details',
    'warehouse_custom_fields', 'staff_warehouses', 'activity_logs', 'delivery_activity_logs', 'transaction_details',
    'packing_list_details', 'stock_take_details', 'return_details', 'receipt_details', 'delivery_details',
    'adjustment_details', 'delivery_order_links', 'item_relations', 'omni_shipments',
] as $resource) $warehouseRows = array_merge($warehouseRows, $crud('/warehouse/' . $resource, 'warehouse ' . str_replace('_', ' ', $resource)));
$warehouseRows = array_merge($warehouseRows, $crud('/warehouse/inventory', 'inventory balance', false));
$catalog['warehouse_module'] = $category('Warehouse', 'Warehouse master data, stock documents and read-only inventory balances', $warehouseRows);

$catalog['payments_on_account_module'] = $category('Payments On Account', 'Receipts, allocations, email/PDF, client settings, statements and reports', [
    ['GET','/paymentsonaccount','Discover endpoints'], ['GET','/paymentsonaccount/receipts','List receipts'], ['POST','/paymentsonaccount/receipts','Create receipt'],
    ['GET','/paymentsonaccount/receipts/{id}','Get receipt'], ['PUT','/paymentsonaccount/receipts/{id}','Update receipt'], ['DELETE','/paymentsonaccount/receipts/{id}','Delete receipt'],
    ['GET','/paymentsonaccount/receipts/{id}/applications','List applications'], ['POST','/paymentsonaccount/receipts/{id}/applications','Apply receipt to invoices'],
    ['DELETE','/paymentsonaccount/receipts/{id}/applications/{payment_id}','Delete application'], ['POST','/paymentsonaccount/receipts/{id}/email','Email receipt'], ['GET','/paymentsonaccount/receipts/{id}/pdf','Get receipt PDF'],
    ['GET','/paymentsonaccount/clients/{id}/unpaid-invoices','List unpaid invoices'], ['GET','/paymentsonaccount/clients/{id}/payment-modes','Get client payment modes'], ['PUT','/paymentsonaccount/clients/{id}/payment-modes','Update client payment modes'],
    ['GET','/paymentsonaccount/clients/{id}/statement','Get client statement'], ['GET','/paymentsonaccount/reports/receipts','Receipts report'], ['GET','/paymentsonaccount/reports/credits','Credits report'],
]);

$deliveryRows = [['GET','/delivery_notes','Discover endpoints'], ['GET','/delivery_notes/statuses','List statuses']];
$deliveryRows = array_merge($deliveryRows, $crud('/delivery_notes/notes', 'delivery note'), [
    ['PUT','/delivery_notes/notes/{id}/status','Change status'], ['POST','/delivery_notes/notes/{id}/email','Email delivery note'], ['GET','/delivery_notes/notes/{id}/pdf','Get PDF'],
    ['POST','/delivery_notes/notes/{id}/copy','Copy delivery note'], ['POST','/delivery_notes/notes/{id}/convert-to-invoice','Convert to invoice'],
    ['POST','/delivery_notes/from-invoice/{id}','Create from invoice'], ['POST','/delivery_notes/from-estimate/{id}','Create from estimate'], ['POST','/delivery_notes/from-purchase-order/{id}','Create from purchase order'],
]);
$catalog['delivery_notes_module'] = $category('Delivery Notes', 'Delivery note lifecycle, delivery and document conversions', $deliveryRows);

$commissionRows = [['GET','/commission','Discover endpoints'], ['GET','/commission/commissions','List calculated commissions'], ['GET','/commission/commissions/{id}','Get calculated commission']];
foreach (['policies','applicable-staff','applicable-clients','hierarchies','salesadmin-groups','receipts'] as $resource) $commissionRows = array_merge($commissionRows, $crud('/commission/' . $resource, str_replace('-', ' ', $resource)));
$commissionRows = array_merge($commissionRows, [['GET','/commission/receipts/{id}/pdf','Get receipt PDF'],['POST','/commission/receipts/{id}/email','Email receipt'],['GET','/commission/chart','Get chart data'],['POST','/commission/recalculate','Recalculate commissions']]);
$catalog['commission_module'] = $category('Sales Commission', 'Policies, applicability, hierarchies, receipts and reporting', $commissionRows);

$shopifyRows = [['GET','/myshopify','Discover endpoints']];
foreach (['products','customers','orders','categories','discounts'] as $resource) $shopifyRows = array_merge($shopifyRows, $crud('/myshopify/' . $resource, 'Shopify ' . $resource, false));
$shopifyRows = array_merge($shopifyRows, [['GET','/myshopify/maps/{type}','List identity mappings'],['GET','/myshopify/logs','List sync logs'],['GET','/myshopify/logs/{id}','Get sync log'],['POST','/myshopify/sync','Run reconciliation'],['POST','/myshopify/webhooks/register','Register Shopify webhooks'],['POST','/myshopify/push/customer/{id}','Push customer'],['POST','/myshopify/push/item/{id}','Push item'],['POST','/myshopify/push/inventory','Push inventory'],['POST','/myshopify/push/categories','Push categories']]);
$catalog['myshopify_module'] = $category('MyShopify', 'Imported Shopify data, mappings, logs and synchronization actions', $shopifyRows);

$purchaseRows = [['GET','/purchase','Discover endpoints']];
foreach (['vendors','requests','quotations','orders','contracts','invoices','debit-notes','order-returns','vendor-categories','units','commodity-groups','sub-groups','approval-settings','vendor-items'] as $resource) $purchaseRows = array_merge($purchaseRows, $crud('/purchase/' . $resource, 'purchase ' . str_replace('-', ' ', $resource)));
foreach (['requests','quotations','orders'] as $resource) $purchaseRows = array_merge($purchaseRows, [['PUT','/purchase/' . $resource . '/{id}/status','Change workflow status'],['GET','/purchase/' . $resource . '/{id}/pdf','Get document PDF']]);
foreach (['orders','invoices'] as $resource) $purchaseRows = array_merge($purchaseRows, [['GET','/purchase/' . $resource . '/{id}/payments','List payments'],['POST','/purchase/' . $resource . '/{id}/payments','Add payment']]);
$purchaseRows[] = ['GET','/purchase/vendors/{id}/statement','Get vendor statement'];
$catalog['purchase_module'] = $category('Purchase Management', 'Vendors and the complete procurement document workflow', $purchaseRows);

$catalog['guest_invoices_module'] = $category('Guest Invoices', 'Guest customer invoice creation and combined checkout', [
    ['POST','/guest_invoices','Create guest invoice'], ['POST','/guest_invoices/checkout','Create invoice, payment and optionally email PDFs'],
]);

return $catalog;
