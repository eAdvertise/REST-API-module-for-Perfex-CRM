<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Shopify <-> Perfex synchronization boundary.
 *
 * Identity rules: customers by normalized email and product variants by SKU.
 * Conflict rule: the newest updated_at wins; Perfex wins equal timestamps.
 * Order rule: only paid orders create an invoice, exactly once.
 */
class Myshopify_sync_service
{
    private $CI;
    private $inbound = false;
    private $apiVersion = '2026-07';
    private $lastResponseHeaders = [];

    public function __construct()
    {
        $this->CI = &get_instance();
        $configuredVersion = (string) get_option('my_shopify_api_version');
        if (preg_match('/^20\d{2}-(01|04|07|10)$/', $configuredVersion)) {
            $this->apiVersion = $configuredVersion;
        }
    }

    public function handleWebhook($topic, array $payload)
    {
        $this->inbound = true;
        switch ($topic) {
            case 'customers/create':
            case 'customers/update':
                return $this->syncCustomerFromShopify($payload);
            case 'products/create':
            case 'products/update':
                return $this->syncProductFromShopify($payload);
            case 'orders/create':
            case 'orders/updated':
            case 'orders/paid':
                return $this->syncPaidOrder($payload);
            case 'collections/create':
            case 'collections/update':
                return $this->syncCategoryFromShopify($payload);
            case 'inventory_levels/update':
                return $this->syncInventoryLevelFromShopify($payload);
        }
        return null;
    }

    public function reconcile()
    {
        if (!$this->configured()) {
            return false;
        }
        // Pull updated Shopify records first. Local hooks provide immediate
        // outbound sync; this periodic pass repairs missed webhooks.
        $since = get_option('my_shopify_last_sync');
        $query = ['limit' => 250];
        if ($since) {
            $query['updated_at_min'] = gmdate('c', strtotime($since) - 60);
        }
        foreach ($this->paginate('products.json', 'products', $query) as $product) {
            $this->syncProductFromShopify($product);
        }
        foreach ($this->paginate('customers.json', 'customers', $query) as $customer) {
            $this->syncCustomerFromShopify($customer);
        }
        foreach ($this->paginate('orders.json', 'orders', $query + ['status' => 'any']) as $order) {
            $this->syncPaidOrder($order);
        }
        foreach (['custom_collections', 'smart_collections'] as $resource) {
            foreach ($this->paginate($resource . '.json', $resource, $query) as $collection) {
                $this->syncCategoryFromShopify($collection);
            }
        }
        $this->pushPerfexCategories();
        update_option('my_shopify_last_sync', gmdate('Y-m-d H:i:s'));
        return true;
    }

    public function registerWebhooks()
    {
        if (!$this->configured()) return false;
        $address = site_url('myshopify/webhook');
        $existing = $this->request('GET', 'webhooks.json?limit=250');
        $registered = [];
        foreach (($existing['webhooks'] ?? []) as $webhook) {
            if (rtrim((string) ($webhook['address'] ?? ''), '/') === rtrim($address, '/')) {
                $registered[strtolower((string) ($webhook['topic'] ?? ''))] = true;
            }
        }
        $topics = ['customers/create', 'customers/update', 'products/create', 'products/update',
            'orders/create', 'orders/updated', 'orders/paid', 'collections/create', 'collections/update',
            'inventory_levels/update'];
        foreach ($topics as $topic) {
            if (isset($registered[$topic])) continue;
            $this->request('POST', 'webhooks.json', ['webhook' => ['topic' => $topic, 'address' => $address, 'format' => 'json']]);
        }
        return true;
    }

    public function syncCustomerFromShopify(array $customer)
    {
        $email = strtolower(trim((string) ($customer['email'] ?? '')));
        if ($email === '') {
            return false;
        }
        $shopTime = $this->date($customer['updated_at'] ?? $customer['created_at'] ?? 'now');
        $map = $this->CI->db->where('email', $email)->get(db_prefix() . 'myshopify_customer_map')->row_array();
        $contact = $this->CI->db->where('LOWER(email) = ' . $this->CI->db->escape($email), null, false)
            ->get(db_prefix() . 'contacts')->row_array();
        $perfexTime = $this->date($map['perfex_updated_at'] ?? ($contact['datecreated'] ?? '@0'));

        if ($contact && $perfexTime >= $shopTime) {
            $this->saveCustomerMap($customer, (int) $contact['userid'], (int) $contact['id'], $email, $shopTime, $perfexTime);
            return $this->pushCustomer((int) $contact['userid'], true);
        }

        $address = $customer['default_address'] ?? [];
        $clientData = [
            'company' => trim((string) ($address['company'] ?? '')) ?: trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? '')) ?: $email,
            'phonenumber' => (string) ($customer['phone'] ?? $address['phone'] ?? ''),
            'address' => trim((string) ($address['address1'] ?? '') . ' ' . (string) ($address['address2'] ?? '')),
            'city' => (string) ($address['city'] ?? ''), 'state' => (string) ($address['province'] ?? ''),
            'zip' => (string) ($address['zip'] ?? ''), 'country' => $this->countryId($address['country_code'] ?? ''),
        ];
        $this->CI->load->model('clients_model');
        if ($contact) {
            $clientId = (int) $contact['userid'];
            $this->CI->clients_model->update($clientData, $clientId, true);
            $contactId = (int) $contact['id'];
            $this->CI->db->where('id', $contactId)->update(db_prefix() . 'contacts', [
                'firstname' => (string) ($customer['first_name'] ?? ''), 'lastname' => (string) ($customer['last_name'] ?? ''),
                'phonenumber' => (string) ($customer['phone'] ?? ''), 'last_login' => null,
            ]);
        } else {
            $contactData = [
                'firstname' => (string) ($customer['first_name'] ?? 'Shopify'),
                'lastname' => (string) ($customer['last_name'] ?? 'Customer'),
                'email' => $email, 'phonenumber' => (string) ($customer['phone'] ?? ''),
                'is_primary' => 1, 'donotsendwelcomeemail' => true,
            ];
            $clientId = (int) $this->CI->clients_model->add($clientData, true);
            $contactId = $clientId > 0 ? (int) $this->CI->clients_model->add_contact($contactData, $clientId, true) : 0;
        }
        $now = $this->date('now');
        $this->saveCustomerMap($customer, $clientId, $contactId, $email, $shopTime, $now);
        $this->log('shopify_to_perfex', 'customer', $customer['id'] ?? $email, 'success', 'Customer linked by email');
        return $clientId;
    }

    public function pushCustomer($clientId, $force = false)
    {
        if (($this->inbound && !$force) || !$this->configured()) {
            return false;
        }
        $contact = $this->CI->db->where('userid', $clientId)->where('is_primary', 1)->get(db_prefix() . 'contacts')->row_array();
        $client = $this->CI->db->where('userid', $clientId)->get(db_prefix() . 'clients')->row_array();
        if (!$contact || !$client || empty($contact['email'])) {
            return false;
        }
        $email = strtolower(trim($contact['email']));
        $map = $this->CI->db->where('email', $email)->get(db_prefix() . 'myshopify_customer_map')->row_array();
        $perfexTime = $this->date('now');
        // This method is invoked by a Perfex update hook, therefore "now" is
        // the authoritative local timestamp. Equal timestamps favor Perfex.
        $body = ['customer' => ['email' => $email, 'first_name' => $contact['firstname'], 'last_name' => $contact['lastname'],
            'phone' => $contact['phonenumber'] ?: null, 'addresses' => [[
                'company' => $client['company'], 'address1' => $client['address'], 'city' => $client['city'],
                'province' => $client['state'], 'zip' => $client['zip'],
            ]]]];
        if ($map && !empty($map['shopify_customer_id'])) {
            $body['customer']['id'] = (int) $map['shopify_customer_id'];
            $result = $this->request('PUT', 'customers/' . $map['shopify_customer_id'] . '.json', $body);
        } else {
            $result = $this->request('POST', 'customers.json', $body);
        }
        $remote = $result['customer'] ?? [];
        if ($remote) {
            $this->saveCustomerMap($remote, $clientId, (int) $contact['id'], $email, $this->date($remote['updated_at'] ?? 'now'), $perfexTime);
        }
        return (bool) $remote;
    }

    public function syncProductFromShopify(array $product)
    {
        $shopTime = $this->date($product['updated_at'] ?? 'now');
        foreach (($product['variants'] ?? []) as $variant) {
            $sku = trim((string) ($variant['sku'] ?? ''));
            if ($sku === '') {
                $this->log('shopify_to_perfex', 'product', $variant['id'] ?? '', 'skipped', 'Variant has no SKU');
                continue;
            }
            $skuMap = $this->CI->db->where('sku', $sku)->get(db_prefix() . 'myshopify_product_map')->row_array();
            if ($skuMap && (string) $skuMap['shopify_variant_id'] !== (string) ($variant['id'] ?? '')) {
                $this->log('shopify_to_perfex', 'product', $variant['id'] ?? '', 'error', 'Duplicate SKU: ' . $sku);
                continue;
            }
            $map = $this->CI->db->where('shopify_variant_id', $variant['id'])->get(db_prefix() . 'myshopify_product_map')->row_array();
            $item = $this->findItemBySku($sku);
            $perfexTime = $this->date($map['perfex_updated_at'] ?? '@0');
            if ($item && $perfexTime >= $shopTime) {
                $this->saveProductMap($product, $variant, (int) $item['id'], $sku, $shopTime, $perfexTime);
                $this->pushItem((int) $item['id'], true);
                continue;
            }
            $groupId = $this->ensureItemGroup((string) ($product['product_type'] ?? 'Shopify'));
            $data = ['description' => (string) $product['title'] . (count($product['variants'] ?? []) > 1 ? ' - ' . ($variant['title'] ?? '') : ''),
                'long_description' => strip_tags((string) ($product['body_html'] ?? '')), 'rate' => (float) ($variant['price'] ?? 0),
                'group_id' => $groupId, 'commodity_code' => $sku, 'commodity_barcode' => (string) ($variant['barcode'] ?? $sku),
            ];
            if ($item) {
                $itemId = (int) $item['id'];
                $this->CI->db->where('id', $itemId)->update(db_prefix() . 'items', $this->validColumns('items', $data));
            } else {
                $this->CI->db->insert(db_prefix() . 'items', $this->validColumns('items', $data));
                $itemId = (int) $this->CI->db->insert_id();
            }
            $this->saveProductMap($product, $variant, $itemId, $sku, $shopTime, $this->date('now'));
            if ($this->warehouseActive()) {
                $this->setWarehouseStock($itemId, (int) ($variant['inventory_quantity'] ?? 0));
            }
        }
        return true;
    }

    public function pushItem($itemId, $force = false)
    {
        if (($this->inbound && !$force) || !$this->configured()) {
            return false;
        }
        $item = $this->CI->db->where('id', $itemId)->get(db_prefix() . 'items')->row_array();
        if (!$item) return false;
        $sku = trim((string) ($item['commodity_code'] ?? ''));
        if ($sku === '') return false;
        $map = $this->CI->db->where('perfex_item_id', $itemId)->get(db_prefix() . 'myshopify_product_map')->row_array();
        if (!$map) {
            $created = $this->request('POST', 'products.json', ['product' => [
                'title' => $item['description'], 'body_html' => $item['long_description'] ?? '',
                'variants' => [['sku' => $sku, 'price' => number_format((float) $item['rate'], 2, '.', '')]],
            ]]);
            $product = $created['product'] ?? [];
            $variant = $product['variants'][0] ?? [];
            if (!$product || !$variant) return false;
            $this->saveProductMap($product, $variant, $itemId, $sku, $this->date($product['updated_at'] ?? 'now'), $this->date('now'));
            $map = $this->CI->db->where('perfex_item_id', $itemId)->get(db_prefix() . 'myshopify_product_map')->row_array();
        }
        // A direct Perfex item hook is a new local write and wins timestamp ties.
        $payload = ['variant' => ['id' => (int) $map['shopify_variant_id'], 'sku' => $sku,
            'price' => number_format((float) $item['rate'], 2, '.', '')]];
        $result = $this->request('PUT', 'variants/' . $map['shopify_variant_id'] . '.json', $payload);
        if ($this->warehouseActive() && !empty($map['shopify_inventory_item_id']) && get_option('my_shopify_location_id')) {
            $this->request('POST', 'inventory_levels/set.json', ['location_id' => (int) get_option('my_shopify_location_id'),
                'inventory_item_id' => (int) $map['shopify_inventory_item_id'], 'available' => $this->warehouseStock($itemId)]);
        }
        $this->CI->db->where('id', $map['id'])->update(db_prefix() . 'myshopify_product_map', [
            'perfex_updated_at' => $this->date('now'), 'shopify_updated_at' => $this->date($result['variant']['updated_at'] ?? 'now'),
            'last_synced_at' => $this->date('now'),
        ]);
        return true;
    }

    public function pushMappedInventory()
    {
        if (!$this->warehouseActive() || !$this->configured()) return false;
        $maps = $this->CI->db->where('shopify_inventory_item_id IS NOT NULL', null, false)
            ->get(db_prefix() . 'myshopify_product_map')->result_array();
        foreach ($maps as $map) {
            $this->pushItem((int) $map['perfex_item_id'], true);
        }
        return true;
    }

    public function syncCategoryFromShopify(array $collection)
    {
        if (empty($collection['id']) || empty($collection['title'])) return false;
        $map = $this->CI->db->where('shopify_collection_id', $collection['id'])->get(db_prefix() . 'myshopify_category_map')->row_array();
        $shopTime = $this->date($collection['updated_at'] ?? 'now');
        if ($map && $this->date($map['perfex_updated_at']) >= $shopTime) return true;
        $group = $this->CI->db->where('name', $collection['title'])->get(db_prefix() . 'items_groups')->row_array();
        if ($group) $groupId = (int) $group['id'];
        else { $this->CI->db->insert(db_prefix() . 'items_groups', ['name' => $collection['title']]); $groupId = (int) $this->CI->db->insert_id(); }
        $data = ['shopify_collection_id' => $collection['id'], 'perfex_group_id' => $groupId,
            'shopify_updated_at' => $shopTime, 'perfex_updated_at' => $this->date('now'), 'last_synced_at' => $this->date('now')];
        $map ? $this->CI->db->where('id', $map['id'])->update(db_prefix() . 'myshopify_category_map', $data)
             : $this->CI->db->insert(db_prefix() . 'myshopify_category_map', $data);
        return $groupId;
    }

    public function pushPerfexCategories()
    {
        if (!$this->configured()) return false;
        foreach ($this->CI->db->get(db_prefix() . 'items_groups')->result_array() as $group) {
            $map = $this->CI->db->where('perfex_group_id', $group['id'])->get(db_prefix() . 'myshopify_category_map')->row_array();
            $payload = ['custom_collection' => ['title' => $group['name']]];
            if ($map) {
                $payload['custom_collection']['id'] = (int) $map['shopify_collection_id'];
                $result = $this->request('PUT', 'custom_collections/' . $map['shopify_collection_id'] . '.json', $payload);
            } else {
                $result = $this->request('POST', 'custom_collections.json', $payload);
            }
            $collection = $result['custom_collection'] ?? [];
            if (!$collection) continue;
            $data = ['shopify_collection_id' => $collection['id'], 'perfex_group_id' => $group['id'],
                'shopify_updated_at' => $this->date($collection['updated_at'] ?? 'now'),
                'perfex_updated_at' => $this->date('now'), 'last_synced_at' => $this->date('now')];
            $map ? $this->CI->db->where('id', $map['id'])->update(db_prefix() . 'myshopify_category_map', $data)
                 : $this->CI->db->insert(db_prefix() . 'myshopify_category_map', $data);
        }
        return true;
    }

    public function syncPaidOrder(array $order)
    {
        $orderId = (int) ($order['id'] ?? 0);
        if (!$orderId) return false;
        $lockName = 'myshopify_order_' . $orderId;
        $lock = $this->CI->db->query('SELECT GET_LOCK(?, 10) AS acquired', [$lockName])->row_array();
        if ((int) ($lock['acquired'] ?? 0) !== 1) {
            throw new RuntimeException('Could not acquire invoice lock for Shopify order ' . $orderId);
        }
        try {
            return $this->syncPaidOrderLocked($order);
        } finally {
            $this->CI->db->query('SELECT RELEASE_LOCK(?)', [$lockName]);
        }
    }

    private function syncPaidOrderLocked(array $order)
    {
        $orderId = (int) $order['id'];
        $map = $this->CI->db->where('shopify_order_id', $orderId)->get(db_prefix() . 'myshopify_order_map')->row_array();
        if ($map && !empty($map['perfex_invoice_id'])) return (int) $map['perfex_invoice_id'];
        if (strtolower((string) ($order['financial_status'] ?? '')) !== 'paid') {
            $this->saveOrderMap($order, null, null);
            return false;
        }
        $customer = $order['customer'] ?? ['email' => $order['email'] ?? '', 'first_name' => $order['billing_address']['first_name'] ?? '',
            'last_name' => $order['billing_address']['last_name'] ?? '', 'default_address' => $order['billing_address'] ?? []];
        $clientId = $this->syncCustomerFromShopify($customer);
        if (!$clientId) return $this->saveOrderMap($order, null, 'Customer could not be synchronized');
        $newitems = [];
        foreach (($order['line_items'] ?? []) as $line) {
            $sku = trim((string) ($line['sku'] ?? ''));
            $item = $sku ? $this->findItemBySku($sku) : null;
            $newitems[] = ['description' => (string) ($line['title'] ?? $sku), 'long_description' => (string) ($line['variant_title'] ?? ''),
                'qty' => (float) ($line['quantity'] ?? 1), 'rate' => (float) ($line['price'] ?? 0), 'order' => count($newitems),
                'unit' => '', 'itemid' => (int) ($item['id'] ?? 0)];
        }
        if (!$newitems) return $this->saveOrderMap($order, null, 'Order has no line items');
        $this->CI->load->model('invoices_model');
        $invoiceData = ['clientid' => $clientId, 'date' => date('Y-m-d', strtotime($order['created_at'] ?? 'now')),
            'duedate' => date('Y-m-d'), 'currency' => $this->currencyId($order['currency'] ?? ''), 'newitems' => $newitems,
            'number' => (int) get_option('next_invoice_number'),
            'subtotal' => (float) ($order['subtotal_price'] ?? 0), 'total' => (float) ($order['total_price'] ?? 0),
            'discount_total' => (float) ($order['total_discounts'] ?? 0), 'discount_type' => 'before_tax',
            'adminnote' => 'Shopify order #' . ($order['order_number'] ?? $orderId),
        ];
        $invoiceId = (int) $this->CI->invoices_model->add($invoiceData);
        if (!$invoiceId) return $this->saveOrderMap($order, null, 'Perfex invoice creation failed');
        $currentNext = (int) get_option('next_invoice_number');
        if ($currentNext <= (int) $invoiceData['number']) {
            update_option('next_invoice_number', (int) $invoiceData['number'] + 1);
        }
        $this->saveOrderMap($order, $invoiceId, null);
        $this->log('shopify_to_perfex', 'order', $orderId, 'success', 'Created invoice ' . $invoiceId);
        return $invoiceId;
    }

    public function syncInventoryLevelFromShopify(array $level)
    {
        if (!$this->warehouseActive() || empty($level['inventory_item_id'])) return false;
        $map = $this->CI->db->where('shopify_inventory_item_id', $level['inventory_item_id'])->get(db_prefix() . 'myshopify_product_map')->row_array();
        if (!$map) return false;
        $this->setWarehouseStock((int) $map['perfex_item_id'], (int) ($level['available'] ?? 0));
        return true;
    }

    private function saveCustomerMap(array $remote, $clientId, $contactId, $email, $shopTime, $perfexTime)
    {
        $data = ['shopify_customer_id' => $remote['id'] ?? null, 'perfex_client_id' => $clientId,
            'perfex_contact_id' => $contactId, 'email' => $email, 'shopify_updated_at' => $shopTime,
            'perfex_updated_at' => $perfexTime, 'last_synced_at' => $this->date('now')];
        $map = $this->CI->db->where('email', $email)->get(db_prefix() . 'myshopify_customer_map')->row_array();
        return $map ? $this->CI->db->where('id', $map['id'])->update(db_prefix() . 'myshopify_customer_map', $data)
                    : $this->CI->db->insert(db_prefix() . 'myshopify_customer_map', $data);
    }

    private function saveProductMap($product, $variant, $itemId, $sku, $shopTime, $perfexTime)
    {
        $data = ['shopify_product_id' => $product['id'], 'shopify_variant_id' => $variant['id'],
            'shopify_inventory_item_id' => $variant['inventory_item_id'] ?? null, 'perfex_item_id' => $itemId, 'sku' => $sku,
            'shopify_updated_at' => $shopTime, 'perfex_updated_at' => $perfexTime, 'last_synced_at' => $this->date('now')];
        $map = $this->CI->db->where('shopify_variant_id', $variant['id'])->get(db_prefix() . 'myshopify_product_map')->row_array();
        return $map ? $this->CI->db->where('id', $map['id'])->update(db_prefix() . 'myshopify_product_map', $data)
                    : $this->CI->db->insert(db_prefix() . 'myshopify_product_map', $data);
    }

    private function saveOrderMap($order, $invoiceId, $error)
    {
        $data = ['shopify_order_id' => $order['id'], 'perfex_invoice_id' => $invoiceId,
            'financial_status' => $order['financial_status'] ?? '', 'shopify_updated_at' => $this->date($order['updated_at'] ?? 'now'),
            'processed_at' => $invoiceId ? $this->date('now') : null, 'last_error' => $error];
        $map = $this->CI->db->where('shopify_order_id', $order['id'])->get(db_prefix() . 'myshopify_order_map')->row_array();
        $map ? $this->CI->db->where('id', $map['id'])->update(db_prefix() . 'myshopify_order_map', $data)
             : $this->CI->db->insert(db_prefix() . 'myshopify_order_map', $data);
        return false;
    }

    private function findItemBySku($sku)
    {
        if ($this->CI->db->field_exists('commodity_code', db_prefix() . 'items')) {
            return $this->CI->db->where('commodity_code', $sku)->get(db_prefix() . 'items')->row_array();
        }
        return null;
    }

    private function ensureItemGroup($name)
    {
        $name = trim($name) ?: 'Shopify';
        $row = $this->CI->db->where('name', $name)->get(db_prefix() . 'items_groups')->row_array();
        if ($row) return (int) $row['id'];
        $this->CI->db->insert(db_prefix() . 'items_groups', ['name' => $name]);
        return (int) $this->CI->db->insert_id();
    }

    private function warehouseActive()
    {
        return isset($this->CI->app_modules) && $this->CI->app_modules->is_active('warehouse')
            && $this->CI->db->table_exists(db_prefix() . 'inventory_manage');
    }

    private function setWarehouseStock($itemId, $quantity)
    {
        $warehouseId = (int) get_option('my_shopify_warehouse_id');
        if ($warehouseId < 1) return false;
        $table = db_prefix() . 'inventory_manage';
        $row = $this->CI->db->where(['warehouse_id' => $warehouseId, 'commodity_id' => $itemId])->get($table)->row_array();
        $data = ['warehouse_id' => $warehouseId, 'commodity_id' => $itemId, 'inventory_number' => $quantity];
        return $row ? $this->CI->db->where('id', $row['id'])->update($table, $this->validColumns('inventory_manage', $data))
                    : $this->CI->db->insert($table, $this->validColumns('inventory_manage', $data));
    }

    private function warehouseStock($itemId)
    {
        $this->CI->db->select_sum('inventory_number')->where('commodity_id', $itemId);
        $row = $this->CI->db->get(db_prefix() . 'inventory_manage')->row_array();
        return (int) ($row['inventory_number'] ?? 0);
    }

    private function validColumns($suffix, array $data)
    {
        $fields = array_flip($this->CI->db->list_fields(db_prefix() . $suffix));
        return array_intersect_key($data, $fields);
    }

    private function countryId($iso)
    {
        if (!$iso) return 0;
        $row = $this->CI->db->where('iso2', strtoupper($iso))->get(db_prefix() . 'countries')->row_array();
        return (int) ($row['country_id'] ?? 0);
    }

    private function currencyId($code)
    {
        $row = $this->CI->db->where('name', strtoupper($code))->get(db_prefix() . 'currencies')->row_array();
        return (int) ($row['id'] ?? get_option('default_currency'));
    }

    private function configured()
    {
        return get_option('my_shopify_url') && get_option('my_shopify_access_token');
    }

    private function request($method, $path, array $body = null)
    {
        $url = preg_match('#^https://#i', $path) ? $path
            : 'https://' . preg_replace('#^https?://#', '', rtrim(get_option('my_shopify_url'), '/')) . '/admin/api/' . $this->apiVersion . '/' . ltrim($path, '/');
        $ch = curl_init($url);
        $this->lastResponseHeaders = [];
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_TIMEOUT => 45,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-Shopify-Access-Token: ' . get_option('my_shopify_access_token')],
            CURLOPT_HEADERFUNCTION => function ($curl, $header) {
                $length = strlen($header);
                $parts = explode(':', $header, 2);
                if (count($parts) === 2) $this->lastResponseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
                return $length;
            }]);
        if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        $raw = curl_exec($ch); $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE); $error = curl_error($ch); curl_close($ch);
        if ($raw === false || $status < 200 || $status >= 300) {
            throw new RuntimeException('Shopify API ' . $method . ' ' . $path . ' failed (' . $status . '): ' . ($error ?: $raw));
        }
        return json_decode($raw, true) ?: [];
    }

    private function paginate($path, $key, array $query)
    {
        $results = []; $next = $path . '?' . http_build_query($query);
        do {
            $page = $this->request('GET', $next);
            $results = array_merge($results, $page[$key] ?? []);
            $next = null;
            $link = $this->lastResponseHeaders['link'] ?? '';
            if (preg_match('/<([^>]+)>;\s*rel="next"/', $link, $matches)) $next = $matches[1];
        } while ($next);
        return $results;
    }

    private function date($value)
    {
        $time = is_numeric($value) ? (int) $value : strtotime((string) $value);
        return gmdate('Y-m-d H:i:s', $time ?: 0);
    }

    private function log($direction, $entity, $id, $status, $message)
    {
        if (!$this->CI->db->table_exists(db_prefix() . 'myshopify_sync_log')) return;
        $this->CI->db->insert(db_prefix() . 'myshopify_sync_log', compact('direction', 'entity', 'status', 'message') + [
            'external_id' => (string) $id, 'created_at' => $this->date('now')]);
    }
}
