<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__ . '/REST_Controller.php';

/** API v3 facade for the optional Purchase Management module. */
class Purchase extends REST_Controller
{
    private $purchaseModel;
    private $resources = [
        'vendors' => ['pur_vendor', 'get_vendor', 'add_vendor', 'update_vendor', 'delete_vendor'],
        'requests' => ['pur_request', 'get_purchase_request', 'add_pur_request', 'update_pur_request', 'delete_pur_request'],
        'quotations' => ['pur_estimates', 'get_estimate', 'add_estimate', 'update_estimate', 'delete_estimate'],
        'orders' => ['pur_orders', 'get_pur_order', 'add_pur_order', 'update_pur_order', 'delete_pur_order'],
        'contracts' => ['pur_contracts', 'get_contract', 'add_contract', 'update_contract', 'delete_contract'],
        'invoices' => ['pur_invoices', 'get_pur_invoice', 'add_pur_invoice', 'update_pur_invoice', 'delete_pur_invoice'],
        'debit-notes' => ['pur_debit_notes', 'get_debit_note', 'add_debit_note', 'update_debit_note', 'delete_debit_note'],
        'order-returns' => ['wh_order_returns', 'get_order_return', 'add_order_return', 'update_order_return', null],
        'vendor-categories' => ['pur_vendor_cate', 'get_vendor_category', 'add_vendor_category', 'update_vendor_category', 'delete_vendor_category'],
        'units' => ['pur_unit', 'get_unit_type', 'add_unit_type', 'add_unit_type', 'delete_unit_type'],
        'commodity-groups' => ['items_groups', 'get_commodity_group_type', 'add_commodity_group_type', 'add_commodity_group_type', 'delete_commodity_group_type'],
        'sub-groups' => ['wh_sub_group', 'get_sub_group', 'add_sub_group', 'add_sub_group', 'delete_sub_group'],
        'approval-settings' => ['pur_approval_setting', 'get_approval_setting', 'add_approval_setting', 'edit_approval_setting', 'delete_approval_setting'],
        'vendor-items' => ['pur_vendor_items', null, 'add_vendor_items', null, 'delete_vendor_items'],
    ];

    public function __construct()
    {
        parent::__construct();
        if (!function_exists('module_dir_path') || !is_dir(module_dir_path('purchase')) || !$this->app_modules->is_active('purchase')) {
            $this->response(['status' => false, 'message' => 'Purchase module is not installed or active.'], self::HTTP_SERVICE_UNAVAILABLE); return;
        }
        $this->load->model('purchase/purchase_model');
        $this->purchaseModel = $this->purchase_model;
    }

    public function catalog_get()
    {
        $endpoints = [];
        foreach (array_keys($this->resources) as $resource) $endpoints[$resource] = '/api/purchase/' . $resource;
        $this->response(['status' => true, 'endpoints' => $endpoints, 'actions' => [
            'status' => '/api/purchase/{resource}/{id}/status', 'pdf' => '/api/purchase/{resource}/{id}/pdf',
            'payments' => '/api/purchase/{resource}/{id}/payments', 'statement' => '/api/purchase/vendors/{id}/statement',
        ]], self::HTTP_OK);
    }

    public function data_get($resource, $id = null)
    {
        $config = $this->resource($resource); if (!$config) return;
        if ($id !== null) {
            $row = $config[1] ? $this->purchaseModel->{$config[1]}((int) $id) : $this->db->where('id', (int) $id)->get(db_prefix() . $config[0])->row();
            if (!$row) return $this->notFound($resource);
            $data = (array) $row; $data['details'] = $this->details($resource, (int) $id);
            return $this->response($data, self::HTTP_OK);
        }
        $this->paginate(db_prefix() . $config[0]);
    }

    public function data_post($resource)
    {
        $config = $this->resource($resource); if (!$config) return;
        if (!$config[2]) return $this->unsupported();
        $data = $this->payload();
        try {
            if ($resource === 'order-returns') { $relType = (string) ($data['rel_type'] ?? 'purchase_order'); unset($data['rel_type']); $result = $this->purchaseModel->{$config[2]}($data, $relType); }
            elseif (in_array($resource, ['units', 'commodity-groups', 'sub-groups'], true)) $result = $this->purchaseModel->{$config[2]}($data, false);
            else $result = $this->purchaseModel->{$config[2]}($data);
            $id = is_numeric($result) ? (int) $result : (int) $this->db->insert_id();
            $this->response(['status' => (bool) $result, 'id' => $id], $result ? self::HTTP_CREATED : self::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) { $this->failure($e); }
    }

    public function data_put($resource, $id)
    {
        $config = $this->resource($resource); if (!$config) return;
        if (!$config[3]) return $this->unsupported();
        $data = $this->payload();
        try {
            if (in_array($resource, ['invoices', 'approval-settings'], true)) $result = $this->purchaseModel->{$config[3]}((int) $id, $data);
            elseif ($resource === 'order-returns') { $relType = (string) ($data['rel_type'] ?? 'purchase_order'); unset($data['rel_type']); $result = $this->purchaseModel->{$config[3]}($data, $relType, (int) $id); }
            elseif (in_array($resource, ['units', 'commodity-groups', 'sub-groups'], true)) $result = $this->purchaseModel->{$config[3]}($data, (int) $id);
            else $result = $this->purchaseModel->{$config[3]}($data, (int) $id);
            $this->response(['status' => (bool) $result, 'id' => (int) $id], $result ? self::HTTP_OK : self::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) { $this->failure($e); }
    }

    public function data_delete($resource, $id)
    {
        $config = $this->resource($resource); if (!$config) return;
        if (!$config[4]) return $this->unsupported();
        try { $ok = (bool) $this->purchaseModel->{$config[4]}((int) $id); $this->response(['status' => $ok], $ok ? self::HTTP_OK : self::HTTP_CONFLICT); }
        catch (Throwable $e) { $this->failure($e); }
    }

    public function status_put($resource, $id)
    {
        $methods = ['requests' => 'change_status_pur_request', 'quotations' => 'change_status_pur_estimate', 'orders' => 'change_status_pur_order'];
        if (!isset($methods[$resource])) return $this->badRequest('Status changes support requests, quotations and orders.');
        $status = (int) ($this->payload()['status'] ?? 0); if ($status < 1) return $this->badRequest('status is required.');
        $ok = (bool) $this->purchaseModel->{$methods[$resource]}($status, (int) $id);
        $this->response(['status' => $ok, 'id' => (int) $id, 'document_status' => $status], $ok ? self::HTTP_OK : self::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function pdf_get($resource, $id)
    {
        $map = ['requests' => ['get_purchase_request', 'pur_request_pdf', 'purchase-request'], 'orders' => ['get_pur_order', 'purorder_pdf', 'purchase-order'], 'quotations' => ['get_estimate', 'purestimate_pdf', 'purchase-quotation']];
        if (!isset($map[$resource])) return $this->badRequest('PDF supports requests, quotations and orders.');
        [$getter, $pdfMethod, $name] = $map[$resource]; $record = $this->purchaseModel->$getter((int) $id); if (!$record) return $this->notFound($resource);
        try {
            $pdf = $resource === 'quotations' ? $this->purchaseModel->$pdfMethod($record, (int) $id) : $this->purchaseModel->$pdfMethod($record);
            $filename = $name . '-' . (int) $id . '.pdf'; $content = $pdf->Output($filename, 'S');
            $this->response(['status' => true, 'filename' => $filename, 'content_type' => 'application/pdf', 'content_base64' => base64_encode($content)], self::HTTP_OK);
        } catch (Throwable $e) { $this->failure($e); }
    }

    public function payments_get($resource, $id)
    {
        if ($resource === 'orders') $rows = $this->purchaseModel->get_payment_purchase_order((int) $id);
        elseif ($resource === 'invoices') $rows = $this->purchaseModel->get_payment_invoice((int) $id);
        else return $this->badRequest('Payments support orders and invoices.');
        $this->response(['data' => $rows], self::HTTP_OK);
    }

    public function payments_post($resource, $id)
    {
        $data = $this->payload();
        if ($resource === 'orders') $result = $this->purchaseModel->add_payment($data, (int) $id);
        elseif ($resource === 'invoices') $result = $this->purchaseModel->add_invoice_payment($data, (int) $id);
        else return $this->badRequest('Payments support orders and invoices.');
        $this->response(['status' => (bool) $result, 'id' => is_numeric($result) ? (int) $result : 0], $result ? self::HTTP_CREATED : self::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function statement_get($vendorId)
    {
        $from = (string) ($this->get('from') ?: date('Y-m-01')); $to = (string) ($this->get('to') ?: date('Y-m-d'));
        $this->response($this->purchaseModel->get_statement((int) $vendorId, $from, $to), self::HTTP_OK);
    }

    private function resource($name) { if (!isset($this->resources[$name])) { $this->badRequest('Unknown purchase resource.'); return null; } return $this->resources[$name]; }
    private function details($resource, $id) { $methods = ['requests'=>'get_pur_request_detail','quotations'=>'get_pur_estimate_detail','orders'=>'get_pur_order_detail','invoices'=>'get_pur_invoice']; return isset($methods[$resource]) ? $this->purchaseModel->{$methods[$resource]}($id) : []; }
    private function paginate($table) { $page=max(1,(int)$this->get('page'));$per=min(100,max(1,(int)($this->get('per_page')?:25)));$rows=$this->db->order_by('id','desc')->limit($per,($page-1)*$per)->get($table)->result_array();$this->response(['data'=>$rows,'meta'=>['page'=>$page,'per_page'=>$per,'count'=>count($rows),'has_more'=>count($rows)===$per]],self::HTTP_OK); }
    private function payload(){ $data=$this->input->post(null,true);if($data)return$data;$json=json_decode($this->security->xss_clean($this->input->raw_input_stream),true);return is_array($json)?$json:[]; }
    private function badRequest($m){$this->response(['status'=>false,'message'=>$m],self::HTTP_BAD_REQUEST);} private function notFound($r){$this->response(['status'=>false,'message'=>'Purchase '.$r.' record not found.'],self::HTTP_NOT_FOUND);} private function unsupported(){$this->response(['status'=>false,'message'=>'Operation is not supported for this resource.'],self::HTTP_METHOD_NOT_ALLOWED);} private function failure(Throwable $e){log_message('error','Purchase API v3: '.$e->getMessage());$this->response(['status'=>false,'message'=>$e->getMessage()],self::HTTP_UNPROCESSABLE_ENTITY);}
}
