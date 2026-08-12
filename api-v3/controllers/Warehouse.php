<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/REST_Controller.php';

/**
 * REST facade for the Warehouse module in API v3.
 *
 * Covers the Warehouse master data, operational documents, inventory data,
 * configuration and supporting records. Domain documents are delegated to
 * Warehouse_model; auxiliary entities use schema-filtered CRUD.
 */
class Warehouse extends REST_Controller
{
    /** @var Warehouse_model */
    private $warehouseModel;

    private $resources = [
        'warehouses' => ['table' => 'warehouse', 'key' => 'warehouse_id'],
        'items'       => ['table' => 'items', 'key' => 'id'],
        'inventory'   => ['table' => 'inventory_manage', 'key' => 'id', 'readonly' => true],
        'receipts'    => ['table' => 'goods_receipt', 'key' => 'id', 'detail' => 'goods_receipt_detail', 'foreign_key' => 'goods_receipt_id'],
        'deliveries'  => ['table' => 'goods_delivery', 'key' => 'id', 'detail' => 'goods_delivery_detail', 'foreign_key' => 'goods_delivery_id'],
        'transfers'   => ['table' => 'internal_delivery_note', 'key' => 'id', 'detail' => 'internal_delivery_note_detail', 'foreign_key' => 'internal_delivery_id'],
        'adjustments' => ['table' => 'wh_loss_adjustment', 'key' => 'id', 'detail' => 'wh_loss_adjustment_detail', 'foreign_key' => 'loss_adjustment'],
        'commodity_types' => ['table' => 'ware_commodity_type', 'key' => 'commodity_type_id'],
        'commodity_groups' => ['table' => 'items_groups', 'key' => 'id'],
        'sub_groups' => ['table' => 'wh_sub_group', 'key' => 'id'],
        'units' => ['table' => 'ware_unit_type', 'key' => 'unit_type_id'],
        'sizes' => ['table' => 'ware_size_type', 'key' => 'size_type_id'],
        'styles' => ['table' => 'ware_style_type', 'key' => 'style_type_id'],
        'bodies' => ['table' => 'ware_body_type', 'key' => 'body_type_id'],
        'colors' => ['table' => 'ware_color', 'key' => 'id'],
        'brands' => ['table' => 'wh_brand', 'key' => 'id'],
        'models' => ['table' => 'wh_model', 'key' => 'id'],
        'series' => ['table' => 'wh_series', 'key' => 'id'],
        'inventory_minimums' => ['table' => 'inventory_commodity_min', 'key' => 'id'],
        'serial_numbers' => ['table' => 'wh_inventory_serial_numbers', 'key' => 'id'],
        'stock_takes' => ['table' => 'stock_take', 'key' => 'id', 'detail' => 'stock_take_detail', 'foreign_key' => 'stock_take_id'],
        'packing_lists' => ['table' => 'wh_packing_lists', 'key' => 'id', 'detail' => 'wh_packing_list_details', 'foreign_key' => 'packing_list_id'],
        'order_returns' => ['table' => 'wh_order_returns', 'key' => 'id', 'detail' => 'wh_order_return_details', 'foreign_key' => 'order_return_id'],
        'approval_settings' => ['table' => 'wh_approval_setting', 'key' => 'id'],
        'approval_details' => ['table' => 'wh_approval_details', 'key' => 'id'],
        'warehouse_custom_fields' => ['table' => 'wh_custom_fields', 'key' => 'id'],
        'staff_warehouses' => ['table' => 'wh_staff_warehouses', 'key' => 'id'],
        'activity_logs' => ['table' => 'wh_activity_log', 'key' => 'id'],
        'delivery_activity_logs' => ['table' => 'wh_goods_delivery_activity_log', 'key' => 'id'],
        'transaction_details' => ['table' => 'goods_transaction_detail', 'key' => 'id'],
        'packing_list_details' => ['table' => 'wh_packing_list_details', 'key' => 'id'],
        'stock_take_details' => ['table' => 'stock_take_detail', 'key' => 'id'],
        'return_details' => ['table' => 'wh_order_return_details', 'key' => 'id'],
        'receipt_details' => ['table' => 'goods_receipt_detail', 'key' => 'id'],
        'delivery_details' => ['table' => 'goods_delivery_detail', 'key' => 'id'],
        'adjustment_details' => ['table' => 'wh_loss_adjustment_detail', 'key' => 'id'],
        'delivery_order_links' => ['table' => 'goods_delivery_invoices_pr_orders', 'key' => 'id'],
        'item_relations' => ['table' => 'itemable', 'key' => 'id'],
        'omni_shipments' => ['table' => 'wh_omni_shipments', 'key' => 'id'],
    ];

    public function __construct()
    {
        parent::__construct();

        if (!function_exists('module_dir_path') || !is_dir(module_dir_path('warehouse'))) {
            $this->response(['status' => false, 'message' => 'Warehouse module is not installed.'], self::HTTP_SERVICE_UNAVAILABLE);
            return;
        }

        $this->load->model('warehouse/warehouse_model');
        $this->warehouseModel = $this->warehouse_model;
    }

    public function data_get($resource = '', $id = null)
    {
        if ($resource === '') {
            $catalog = [];
            foreach ($this->resources as $name => $definition) {
                $table = db_prefix() . $definition['table'];
                $catalog[$name] = [
                    'methods' => !empty($definition['readonly']) ? ['GET'] : ['GET', 'POST', 'PUT', 'DELETE'],
                    'fields' => $this->db->table_exists($table) ? $this->db->list_fields($table) : [],
                ];
            }
            $this->response([
                'status' => true,
                'resources' => $catalog,
                'filters' => 'Every real table field can be supplied as an exact-match query parameter. page, per_page, from and to are reserved list controls.',
            ], self::HTTP_OK);
            return;
        }

        $definition = $this->resource($resource);
        if ($definition === null) {
            return;
        }

        if (!$this->db->table_exists(db_prefix() . $definition['table'])) {
            $this->response(['status' => false, 'message' => 'Warehouse resource is unavailable.'], self::HTTP_SERVICE_UNAVAILABLE);
            return;
        }

        $this->db->from(db_prefix() . $definition['table']);
        if ($id !== null) {
            $this->db->where($definition['key'], (int) $id);
            $record = $this->db->get()->row_array();
            if (!$record) {
                $this->notFound($resource, $id);
                return;
            }

            if (isset($definition['detail']) && $this->db->table_exists(db_prefix() . $definition['detail'])) {
                $record['items'] = $this->db
                    ->where($definition['foreign_key'], (int) $id)
                    ->get(db_prefix() . $definition['detail'])
                    ->result_array();
            }
            $this->response($record, self::HTTP_OK);
            return;
        }

        $this->applyFilters($definition);
        $page = max((int) $this->get('page'), 1);
        $limit = min(max((int) $this->get('per_page'), 1), 100);
        if (!$this->get('per_page')) {
            $limit = 25;
        }
        $offset = ($page - 1) * $limit;
        $this->db->limit($limit, $offset);
        $this->db->order_by($definition['key'], 'desc');
        $records = $this->db->get()->result_array();

        $this->response([
            'data' => $records,
            'meta' => ['page' => $page, 'per_page' => $limit, 'count' => count($records), 'has_more' => count($records) === $limit],
        ], self::HTTP_OK);
    }

    public function data_post($resource = '')
    {
        $definition = $this->resource($resource);
        if ($definition === null || !$this->writeAllowed($definition)) {
            return;
        }

        $data = $this->payload();
        if (!$data) {
            $this->response(['status' => false, 'message' => 'A JSON or form payload is required.'], self::HTTP_NOT_ACCEPTABLE);
            return;
        }

        $this->db->trans_begin();
        $id = $this->create($resource, $data);
        if (!$id || $this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $this->response(['status' => false, 'message' => 'Warehouse record could not be created.'], self::HTTP_UNPROCESSABLE_ENTITY);
            return;
        }
        $this->db->trans_commit();

        $this->response(['status' => true, 'id' => (int) $id, 'message' => 'Warehouse record created.'], self::HTTP_CREATED);
    }

    public function data_put($resource = '', $id = null)
    {
        $definition = $this->resource($resource);
        if ($definition === null || !$this->writeAllowed($definition) || !$this->validId($id)) {
            return;
        }

        $data = $this->payload();
        if (!$data) {
            $this->response(['status' => false, 'message' => 'A JSON payload is required.'], self::HTTP_NOT_ACCEPTABLE);
            return;
        }
        if (!$this->exists($definition, $id)) {
            $this->notFound($resource, $id);
            return;
        }

        $this->db->trans_begin();
        $success = $this->update($resource, (int) $id, $data);
        if (!$success || $this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $this->response(['status' => false, 'message' => 'Warehouse record could not be updated.'], self::HTTP_UNPROCESSABLE_ENTITY);
            return;
        }
        $this->db->trans_commit();
        $this->response(['status' => true, 'id' => (int) $id, 'message' => 'Warehouse record updated.'], self::HTTP_OK);
    }

    public function data_delete($resource = '', $id = null)
    {
        $definition = $this->resource($resource);
        if ($definition === null || !$this->writeAllowed($definition) || !$this->validId($id)) {
            return;
        }
        if (!$this->exists($definition, $id)) {
            $this->notFound($resource, $id);
            return;
        }

        $success = $this->delete($resource, (int) $id);
        if (!$success) {
            $this->response(['status' => false, 'message' => 'Record is referenced or cannot be deleted.'], self::HTTP_CONFLICT);
            return;
        }
        $this->response(['status' => true, 'message' => 'Warehouse record deleted.'], self::HTTP_OK);
    }

    private function resource($resource)
    {
        $resource = strtolower(trim((string) $resource));
        if (!isset($this->resources[$resource])) {
            $this->response(['status' => false, 'message' => 'Unknown warehouse resource.'], self::HTTP_NOT_FOUND);
            return null;
        }
        return $this->resources[$resource];
    }

    private function payload()
    {
        $data = $this->input->post(null, true);
        if ($data) {
            return $data;
        }
        $decoded = json_decode($this->security->xss_clean($this->input->raw_input_stream), true);
        return is_array($decoded) ? $decoded : [];
    }

    private function applyFilters($definition)
    {
        $table = db_prefix() . $definition['table'];
        $reserved = ['page', 'per_page', 'from', 'to', 'format'];
        foreach ($this->db->list_fields($table) as $field) {
            if (in_array($field, $reserved, true)) {
                continue;
            }
            $value = $this->get($field);
            if ($value !== null && $value !== '' && $this->db->field_exists($field, db_prefix() . $definition['table'])) {
                $this->db->where($field, $value);
            }
        }
        $dateField = $this->db->field_exists('date_c', db_prefix() . $definition['table']) ? 'date_c' : null;
        if ($dateField && $this->get('from')) {
            $this->db->where($dateField . ' >=', $this->get('from'));
        }
        if ($dateField && $this->get('to')) {
            $this->db->where($dateField . ' <=', $this->get('to'));
        }
    }

    private function writeAllowed($definition)
    {
        if (!empty($definition['readonly'])) {
            $this->response(['status' => false, 'message' => 'Inventory balances are read-only; use receipts, deliveries or transfers.'], self::HTTP_METHOD_NOT_ALLOWED);
            return false;
        }
        return true;
    }

    private function validId($id)
    {
        if (!is_numeric($id) || (int) $id < 1) {
            $this->response(['status' => false, 'message' => 'A valid record ID is required.'], self::HTTP_BAD_REQUEST);
            return false;
        }
        return true;
    }

    private function exists($definition, $id)
    {
        return $this->db->where($definition['key'], (int) $id)->count_all_results(db_prefix() . $definition['table']) > 0;
    }

    private function create($resource, $data)
    {
        $nativeCreate = [
            'sub_groups' => 'add_sub_group', 'colors' => 'add_color',
            'brands' => 'add_brand', 'models' => 'add_model', 'series' => 'add_series',
            'warehouse_custom_fields' => 'add_custom_fields_warehouse',
        ];
        if (isset($nativeCreate[$resource])) {
            return $this->warehouseModel->{$nativeCreate[$resource]}($data);
        }
        if ($resource === 'items') {
            $result = $this->warehouseModel->add_commodity_one_item($data);
            return is_array($result) ? $result['insert_id'] : $result;
        }
        if ($resource === 'receipts') {
            return $this->warehouseModel->add_goods_receipt($data);
        }
        if ($resource === 'deliveries') {
            return $this->warehouseModel->add_goods_delivery($data);
        }
        if ($resource === 'transfers') {
            return $this->warehouseModel->add_internal_delivery($data);
        }
        if ($resource === 'adjustments') {
            return $this->warehouseModel->add_loss_adjustment($data);
        }

        if ($resource === 'warehouses') {
            return $this->warehouseModel->add_one_warehouse($data);
        }

        $definition = $this->resources[$resource];
        $allowed = $this->allowedColumns($definition['table'], $data, [$definition['key']]);
        if (!$allowed) {
            return false;
        }
        $this->db->insert(db_prefix() . $definition['table'], $allowed);
        return $this->db->insert_id();
    }

    private function update($resource, $id, $data)
    {
        $nativeUpdate = [
            'sub_groups' => 'add_sub_group', 'colors' => 'update_color',
            'brands' => 'update_brand', 'models' => 'update_model', 'series' => 'update_series',
            'warehouse_custom_fields' => 'update_custom_fields_warehouse',
        ];
        if (isset($nativeUpdate[$resource])) {
            return $this->warehouseModel->{$nativeUpdate[$resource]}($data, $id);
        }
        if ($resource === 'items') {
            return $this->warehouseModel->update_commodity_one_item($data, $id);
        }
        if ($resource === 'receipts') {
            return $this->warehouseModel->update_goods_receipt($data, $id);
        }
        if ($resource === 'deliveries') {
            return $this->warehouseModel->update_goods_delivery($data, $id);
        }
        if ($resource === 'transfers') {
            return $this->warehouseModel->update_internal_delivery($data, $id);
        }
        if ($resource === 'adjustments') {
            $data['id'] = $id;
            return $this->warehouseModel->update_loss_adjustment($data);
        }

        if ($resource === 'warehouses') {
            return $this->warehouseModel->update_one_warehouse($data, $id);
        }

        $definition = $this->resources[$resource];
        $allowed = $this->allowedColumns($definition['table'], $data, [$definition['key']]);
        if (!$allowed) {
            return false;
        }
        return $this->db->where($definition['key'], $id)->update(db_prefix() . $definition['table'], $allowed);
    }

    private function delete($resource, $id)
    {
        $nativeDelete = [
            'commodity_types' => 'delete_commodity_type', 'units' => 'delete_unit_type',
            'sizes' => 'delete_size_type', 'styles' => 'delete_style_type',
            'bodies' => 'delete_body_type', 'commodity_groups' => 'delete_commodity_group_type',
            'sub_groups' => 'delete_sub_group', 'colors' => 'delete_color',
            'brands' => 'delete_brand', 'models' => 'delete_model', 'series' => 'delete_series',
            'warehouse_custom_fields' => 'delete_custom_fields_warehouse',
        ];
        if (isset($nativeDelete[$resource])) {
            return $this->warehouseModel->{$nativeDelete[$resource]}($id);
        }
        if ($resource === 'warehouses') {
            return $this->warehouseModel->delete_warehouse($id);
        }
        if ($resource === 'items') {
            return $this->warehouseModel->delete_commodity($id) === true;
        }
        if ($resource === 'receipts') {
            return $this->warehouseModel->delete_goods_receipt($id);
        }
        if ($resource === 'deliveries') {
            return $this->warehouseModel->delete_goods_delivery($id);
        }
        if ($resource === 'transfers') {
            return $this->warehouseModel->delete_internal_delivery($id);
        }
        if ($resource === 'adjustments') {
            return $this->warehouseModel->delete_loss_adjustment($id);
        }

        $definition = $this->resources[$resource];
        if (isset($definition['detail']) && $this->db->table_exists(db_prefix() . $definition['detail'])) {
            $this->db->where($definition['foreign_key'], $id)->delete(db_prefix() . $definition['detail']);
        }
        return $this->db->where($definition['key'], $id)->delete(db_prefix() . $definition['table']);
    }

    private function allowedColumns($table, $data, $excluded = [])
    {
        $fields = array_diff($this->db->list_fields(db_prefix() . $table), $excluded);
        return array_intersect_key($data, array_flip($fields));
    }

    private function notFound($resource, $id)
    {
        $this->response(['status' => false, 'message' => ucfirst($resource) . ' record ' . (int) $id . ' was not found.'], self::HTTP_NOT_FOUND);
    }
}
