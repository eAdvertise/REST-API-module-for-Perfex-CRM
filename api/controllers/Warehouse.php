<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/REST_Controller.php';

/**
 * REST facade for the GreenTech Warehouse module.
 *
 * Resources: warehouses, items, inventory, receipts, deliveries, transfers
 * and adjustments. Operational writes are delegated to Warehouse_model so
 * approvals, inventory movements, hooks and activity logs remain intact.
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
            $this->response([
                'status' => true,
                'resources' => array_keys($this->resources),
                'filters' => ['warehouse_id', 'commodity_id', 'active', 'approval', 'from', 'to', 'limit', 'offset'],
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
        $limit = min(max((int) $this->get('limit'), 1), 500);
        if (!$this->get('limit')) {
            $limit = 100;
        }
        $offset = max((int) $this->get('offset'), 0);
        $this->db->limit($limit, $offset);
        $this->db->order_by($definition['key'], 'desc');
        $records = $this->db->get()->result_array();

        $this->response([
            'data' => $records,
            'pagination' => ['limit' => $limit, 'offset' => $offset, 'count' => count($records)],
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
        foreach (['warehouse_id', 'commodity_id', 'active', 'approval'] as $field) {
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

        $allowed = $this->allowedColumns('warehouse', $data, ['warehouse_id']);
        $this->db->insert(db_prefix() . 'warehouse', $allowed);
        return $this->db->insert_id();
    }

    private function update($resource, $id, $data)
    {
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

        $allowed = $this->allowedColumns('warehouse', $data, ['warehouse_id']);
        if (!$allowed) {
            return false;
        }
        return $this->db->where('warehouse_id', $id)->update(db_prefix() . 'warehouse', $allowed);
    }

    private function delete($resource, $id)
    {
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
        return $this->warehouseModel->delete_loss_adjustment($id);
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
