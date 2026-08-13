<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/REST_Controller.php';

/** API v3 facade for the optional MyShopify synchronization module. */
class Myshopify extends REST_Controller
{
    private $shopifyModel;
    private $syncService;

    public function __construct()
    {
        parent::__construct();
        if (!function_exists('module_dir_path') || !is_dir(module_dir_path('myshopify')) || !$this->app_modules->is_active('myshopify')) {
            $this->response(['status' => false, 'message' => 'MyShopify module is not installed or active.'], self::HTTP_SERVICE_UNAVAILABLE);
            return;
        }
        $this->load->model('myshopify/shopify_model');
        $this->load->library('myshopify/Myshopify_sync_service');
        $this->shopifyModel = $this->shopify_model;
        $this->syncService = $this->myshopify_sync_service;
    }

    public function catalog_get()
    {
        $this->response(['status' => true, 'configured' => $this->configured(), 'endpoints' => [
            'products' => '/api/myshopify/products', 'customers' => '/api/myshopify/customers',
            'orders' => '/api/myshopify/orders', 'categories' => '/api/myshopify/categories',
            'discounts' => '/api/myshopify/discounts', 'maps' => '/api/myshopify/maps/{type}',
            'logs' => '/api/myshopify/logs', 'sync' => '/api/myshopify/sync',
            'webhooks' => '/api/myshopify/webhooks/register',
        ]], self::HTTP_OK);
    }

    public function products_get($id = null) { $this->localResource('products', 'id', $id); }
    public function customers_get($id = null) { $this->localResource('customers', 'id', $id); }
    public function orders_get($id = null) { $this->localResource('orders', 'id', $id); }
    public function categories_get($id = null) { $this->localResource('categories', 'id', $id); }
    public function discounts_get($id = null) { $this->localResource('discounts', 'id', $id); }

    public function maps_get($type)
    {
        $tables = ['products' => 'product_map', 'customers' => 'customer_map', 'categories' => 'category_map', 'orders' => 'order_map'];
        if (!isset($tables[$type])) return $this->badRequest('type must be products, customers, categories or orders.');
        $this->paginated(db_prefix() . 'myshopify_' . $tables[$type]);
    }

    public function logs_get($id = null)
    {
        $table = db_prefix() . 'myshopify_sync_log';
        if ($id !== null) {
            $row = $this->db->where('id', (int) $id)->get($table)->row_array();
            return $row ? $this->response($row, self::HTTP_OK) : $this->notFound('Sync log');
        }
        foreach (['direction', 'entity_type', 'status'] as $field) if (($value = $this->get($field)) !== null && $value !== '') $this->db->where($field, $value);
        $this->paginated($table);
    }

    public function sync_post()
    {
        if (!$this->configured()) return $this->response(['status' => false, 'message' => 'Shopify URL and access token must be configured.'], self::HTTP_SERVICE_UNAVAILABLE);
        try {
            $ok = (bool) $this->syncService->reconcile();
            $this->response(['status' => $ok, 'last_sync' => get_option('my_shopify_last_sync')], $ok ? self::HTTP_OK : self::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) { $this->failure($e); }
    }

    public function register_webhooks_post()
    {
        if (!$this->configured()) return $this->response(['status' => false, 'message' => 'Shopify URL and access token must be configured.'], self::HTTP_SERVICE_UNAVAILABLE);
        try {
            $ok = (bool) $this->syncService->registerWebhooks();
            $this->response(['status' => $ok], $ok ? self::HTTP_OK : self::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) { $this->failure($e); }
    }

    public function push_customer_post($id) { $this->push('pushCustomer', $id, 'Perfex customer'); }
    public function push_item_post($id) { $this->push('pushItem', $id, 'Perfex item'); }
    public function push_inventory_post() { $this->push('pushMappedInventory', null, 'Mapped inventory'); }
    public function push_categories_post() { $this->push('pushPerfexCategories', null, 'Perfex categories'); }

    private function localResource($resource, $idColumn, $id)
    {
        $table = db_prefix() . 'myshopify_' . $resource;
        if (!$this->db->table_exists($table)) return $this->response(['status' => false, 'message' => 'MyShopify storage is not installed.'], self::HTTP_SERVICE_UNAVAILABLE);
        if ($id !== null) {
            $row = $this->db->where($idColumn, (int) $id)->get($table)->row_array();
            return $row ? $this->response($row, self::HTTP_OK) : $this->notFound(ucfirst(rtrim($resource, 's')));
        }
        $this->paginated($table);
    }

    private function paginated($table)
    {
        $page = max(1, (int) $this->get('page')); $perPage = min(100, max(1, (int) ($this->get('per_page') ?: 25)));
        $rows = $this->db->order_by('id', 'desc')->limit($perPage, ($page - 1) * $perPage)->get($table)->result_array();
        $this->response(['data' => $rows, 'meta' => ['page' => $page, 'per_page' => $perPage, 'count' => count($rows), 'has_more' => count($rows) === $perPage]], self::HTTP_OK);
    }

    private function push($method, $id, $label)
    {
        try {
            $ok = $id === null ? (bool) $this->syncService->$method() : (bool) $this->syncService->$method((int) $id, true);
            $this->response(['status' => $ok, 'message' => $ok ? $label . ' synchronized.' : $label . ' could not be synchronized.'], $ok ? self::HTTP_OK : self::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) { $this->failure($e); }
    }

    private function configured() { return trim((string) get_option('my_shopify_url')) !== '' && trim((string) get_option('my_shopify_access_token')) !== ''; }
    private function badRequest($message) { $this->response(['status' => false, 'message' => $message], self::HTTP_BAD_REQUEST); }
    private function notFound($label) { $this->response(['status' => false, 'message' => $label . ' not found.'], self::HTTP_NOT_FOUND); }
    private function failure(Throwable $e) { log_message('error', 'MyShopify API v3: ' . $e->getMessage()); $this->response(['status' => false, 'message' => $e->getMessage()], self::HTTP_UNPROCESSABLE_ENTITY); }
}
