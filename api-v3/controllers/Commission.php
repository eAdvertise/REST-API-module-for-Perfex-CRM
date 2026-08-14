<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/REST_Controller.php';

/** REST API facade for the optional Sales Commission module. */
class Commission extends REST_Controller
{
    private $commissionModel;

    public function __construct()
    {
        parent::__construct();
        if (!function_exists('module_dir_path') || !is_dir(module_dir_path('commission')) || !$this->app_modules->is_active('commission')) {
            $this->response(['status' => false, 'message' => 'Commission module is not installed or active.'], self::HTTP_SERVICE_UNAVAILABLE);
            return;
        }
        $this->load->model('commission/commission_model');
        $this->commissionModel = $this->commission_model;
    }

    public function catalog_get()
    {
        $this->response(['status' => true, 'endpoints' => [
            'commissions' => '/api/commission/commissions', 'policies' => '/api/commission/policies',
            'applicable_staff' => '/api/commission/applicable-staff', 'applicable_clients' => '/api/commission/applicable-clients',
            'hierarchies' => '/api/commission/hierarchies', 'salesadmin_groups' => '/api/commission/salesadmin-groups',
            'receipts' => '/api/commission/receipts', 'chart' => '/api/commission/chart',
            'recalculate' => '/api/commission/recalculate',
        ]], self::HTTP_OK);
    }

    public function commissions_get($id = null)
    {
        if ($id !== null) {
            $row = $this->commissionModel->get_commission((int) $id, [db_prefix() . 'commission.id' => (int) $id]);
            return $row ? $this->response($row, self::HTTP_OK) : $this->notFound('Commission entry');
        }
        $this->paginatedTable('commission', ['staffid', 'invoice_id', 'is_client', 'paid'], 'date');
    }

    public function policies_get($id = null) { $this->modelGet('load_commission_policy', $id, 'Commission policy'); }
    public function policies_post() { $this->policyWrite(); }
    public function policies_put($id) { $this->policyWrite((int) $id); }
    public function policies_delete($id) { $this->modelDelete('delete_commission_policy', $id, 'Commission policy'); }

    public function applicable_staff_get($id = null) { $this->applicableGet($id, 0); }
    public function applicable_staff_post() { $this->applicableWrite(0); }
    public function applicable_staff_put($id) { $this->applicableWrite(0, (int) $id); }
    public function applicable_staff_delete($id) { $this->modelDelete('delete_applicable_staff', $id, 'Applicable staff assignment'); }

    public function applicable_clients_get($id = null) { $this->applicableGet($id, 1); }
    public function applicable_clients_post() { $this->applicableWrite(1); }
    public function applicable_clients_put($id) { $this->applicableWrite(1, (int) $id); }
    public function applicable_clients_delete($id) { $this->modelDelete('delete_applicable_staff', $id, 'Applicable client assignment'); }

    public function hierarchies_get($id = null) { $this->modelGet('get_hierarchy', $id, 'Commission hierarchy'); }
    public function hierarchies_post() { $this->simpleWrite('add_hierarchy', 'commission_hierarchy', ['salesman', 'coordinator', 'percent']); }
    public function hierarchies_put($id) { $this->simpleWrite('update_hierarchy', 'commission_hierarchy', ['salesman', 'coordinator', 'percent'], (int) $id); }
    public function hierarchies_delete($id) { $this->modelDelete('delete_hierarchy', $id, 'Commission hierarchy'); }

    public function salesadmin_groups_get($id = null) { $this->modelGet('get_salesadmin_group', $id, 'Sales-admin group'); }
    public function salesadmin_groups_post() { $this->simpleWrite('add_salesadmin_group', 'commission_salesadmin_group', ['salesadmin', 'customer_group']); }
    public function salesadmin_groups_put($id) { $this->simpleWrite('update_salesadmin_group', 'commission_salesadmin_group', ['salesadmin', 'customer_group'], (int) $id); }
    public function salesadmin_groups_delete($id) { $this->modelDelete('delete_salesadmin_group', $id, 'Sales-admin group'); }

    public function receipts_get($id = null)
    {
        if ($id !== null) {
            $row = $this->commissionModel->get_receipt((int) $id);
            return $row ? $this->response($row, self::HTTP_OK) : $this->notFound('Commission receipt');
        }
        $this->paginatedTable('commission_receipt', ['addedfrom', 'paymentmode', 'convert_expense'], 'date');
    }

    public function receipts_post() { $this->receiptWrite(); }
    public function receipts_put($id) { $this->receiptWrite((int) $id); }
    public function receipts_delete($id) { $this->modelDelete('delete_receipt', $id, 'Commission receipt'); }

    public function pdf_get($id)
    {
        $receipt = $this->commissionModel->get_receipt((int) $id);
        if (!$receipt) return $this->notFound('Commission receipt');
        try {
            $filename = mb_strtoupper(slug_it('commission-receipt-' . $receipt->id)) . '.pdf';
            $content = $this->commissionModel->receipt_pdf($receipt)->Output($filename, 'S');
            $this->response(['status' => true, 'filename' => $filename, 'content_type' => 'application/pdf', 'content_base64' => base64_encode($content)], self::HTTP_OK);
        } catch (Throwable $e) { $this->failure($e->getMessage()); }
    }

    public function email_post($id)
    {
        if (!$this->commissionModel->get_receipt((int) $id)) return $this->notFound('Commission receipt');
        $payload = $this->payload();
        $emails = $payload['sent_to'] ?? [];
        if (is_string($emails)) $emails = [$emails];
        if (!$emails) return $this->badRequest('sent_to is required.');
        $this->load->model('emails_model');
        $sent = 0;
        foreach ($emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL) && $this->emails_model->send_simple_email($email, _l('commission'), (string) ($payload['message'] ?? ''))) $sent++;
        }
        $this->response(['status' => $sent > 0, 'sent' => $sent], $sent > 0 ? self::HTTP_OK : self::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function chart_get()
    {
        $this->response($this->commissionModel->commission_chart(
            (string) ($this->get('year') ?: date('Y')), $this->ids($this->get('staff_ids')),
            $this->ids($this->get('product_ids')), (int) ($this->get('is_client') ?: 0)
        ), self::HTTP_OK);
    }

    public function recalculate_post()
    {
        $invoiceIds = $this->ids($this->payload()['invoice_ids'] ?? []);
        if (!$invoiceIds) return $this->badRequest('invoice_ids is required.');
        $ok = (bool) $this->commissionModel->recalculate(['invoice' => $invoiceIds]);
        $this->response(['status' => $ok, 'invoice_ids' => $invoiceIds], $ok ? self::HTTP_OK : self::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function policyWrite($id = null)
    {
        $data = $this->payload();
        foreach (['name', 'from_date', 'commission_policy_type'] as $required) if (empty($data[$required])) return $this->badRequest($required . ' is required.');
        foreach (['from_amount', 'to_amount', 'percent_enjoyed_ladder', 'from_amount_product', 'to_amount_product', 'percent_enjoyed_ladder_product', 'ladder_product'] as $array) $data[$array] = isset($data[$array]) && is_array($data[$array]) ? $data[$array] : [];
        $method = $id ? 'update_commission_policy' : 'add_commission_policy';
        $ok = $id ? $this->commissionModel->$method($data, $id) : $this->commissionModel->$method($data);
        $newId = $id ?: (int) $this->db->insert_id();
        $this->response(['status' => (bool) $ok, 'id' => $newId], $ok ? ($id ? self::HTTP_OK : self::HTTP_CREATED) : self::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function applicableGet($id, $isClient) { $this->modelGet('load_applicable_staff', $id, $isClient ? 'Applicable client assignment' : 'Applicable staff assignment', [$isClient]); }
    private function applicableWrite($isClient, $id = null)
    {
        $data = $this->payload();
        $data['applicable_staff'] = $this->ids($data['applicable_staff'] ?? []);
        if (!$data['applicable_staff'] || empty($data['commission_policy'])) return $this->badRequest('applicable_staff and commission_policy are required.');
        if ($isClient) $data['is_client'] = 1;
        $method = $id ? 'update_applicable_staff' : 'add_applicable_staff';
        $ok = $id ? $this->commissionModel->$method($data, $id) : $this->commissionModel->$method($data);
        $this->response(['status' => (bool) $ok, 'id' => $id ?: (int) $this->db->insert_id()], $ok ? ($id ? self::HTTP_OK : self::HTTP_CREATED) : self::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function receiptWrite($id = null)
    {
        $data = $this->payload();
        $data['list_commission'] = $this->ids($data['list_commission'] ?? []);
        foreach (['amount', 'date', 'list_commission'] as $required) if (empty($data[$required])) return $this->badRequest($required . ' is required.');
        $data['note'] = (string) ($data['note'] ?? '');
        $method = $id ? 'update_receipt' : 'add_receipt';
        $result = $id ? $this->commissionModel->$method($data, $id) : $this->commissionModel->$method($data);
        $this->response(['status' => (bool) $result, 'id' => $id ?: (int) $result], $result ? ($id ? self::HTTP_OK : self::HTTP_CREATED) : self::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function simpleWrite($method, $table, $allowed, $id = null)
    {
        $data = array_intersect_key($this->payload(), array_flip($allowed));
        foreach ($allowed as $required) if (!isset($data[$required]) || $data[$required] === '') return $this->badRequest($required . ' is required.');
        $result = $id ? $this->commissionModel->$method($data, $id) : $this->commissionModel->$method($data);
        $this->response(['status' => (bool) $result, 'id' => $id ?: (int) $result], $result ? ($id ? self::HTTP_OK : self::HTTP_CREATED) : self::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function modelGet($method, $id, $label, $extra = [])
    {
        $args = array_merge([$id === null ? '' : (int) $id], $extra);
        $result = call_user_func_array([$this->commissionModel, $method], $args);
        if ($id !== null && !$result) return $this->notFound($label);
        $this->response($id === null ? ['data' => $result] : $result, self::HTTP_OK);
    }

    private function modelDelete($method, $id, $label)
    {
        $ok = (bool) $this->commissionModel->$method((int) $id);
        $this->response(['status' => $ok, 'message' => $ok ? $label . ' deleted.' : $label . ' could not be deleted.'], $ok ? self::HTTP_OK : self::HTTP_NOT_FOUND);
    }

    private function paginatedTable($table, $filters, $dateField)
    {
        foreach ($filters as $field) if (($value = $this->get($field)) !== null && $value !== '') $this->db->where($field, $value);
        if ($this->get('from')) $this->db->where($dateField . ' >=', $this->get('from'));
        if ($this->get('to')) $this->db->where($dateField . ' <=', $this->get('to'));
        $page = max(1, (int) $this->get('page')); $perPage = min(100, max(1, (int) ($this->get('per_page') ?: 25)));
        $rows = $this->db->order_by('id', 'desc')->limit($perPage, ($page - 1) * $perPage)->get(db_prefix() . $table)->result_array();
        $this->response(['data' => $rows, 'meta' => ['page' => $page, 'per_page' => $perPage, 'count' => count($rows), 'has_more' => count($rows) === $perPage]], self::HTTP_OK);
    }

    private function payload() { $data = $this->input->post(null, true); if ($data) return $data; $decoded = json_decode($this->security->xss_clean($this->input->raw_input_stream), true); return is_array($decoded) ? $decoded : []; }
    private function ids($value) { if (is_string($value)) { $json = json_decode($value, true); $value = is_array($json) ? $json : explode(',', $value); } return array_values(array_unique(array_filter(array_map('intval', is_array($value) ? $value : [])))); }
    private function badRequest($message) { $this->response(['status' => false, 'message' => $message], self::HTTP_BAD_REQUEST); }
    private function notFound($label) { $this->response(['status' => false, 'message' => $label . ' not found.'], self::HTTP_NOT_FOUND); }
    private function failure($message) { $this->response(['status' => false, 'message' => $message], self::HTTP_UNPROCESSABLE_ENTITY); }
}
