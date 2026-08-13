<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/REST_Controller.php';

/** REST API facade for PaymentsOnAccount 3.x. */
class Paymentsonaccount extends REST_Controller
{
    private $poaModel;

    public function __construct()
    {
        parent::__construct();

        if (!function_exists('module_dir_path') || !is_dir(module_dir_path('paymentsonaccount'))) {
            $this->response(['status' => false, 'message' => 'PaymentsOnAccount module is not installed.'], self::HTTP_SERVICE_UNAVAILABLE);
            return;
        }

        $model = module_dir_path('paymentsonaccount', 'models/Payments_on_account_model.php');
        if (!is_file($model)) {
            $this->response(['status' => false, 'message' => 'PaymentsOnAccount model is unavailable.'], self::HTTP_SERVICE_UNAVAILABLE);
            return;
        }

        $this->load->model('paymentsonaccount/payments_on_account_model');
        $this->poaModel = $this->payments_on_account_model;
    }

    public function catalog_get()
    {
        $this->response([
            'status' => true,
            'endpoints' => [
                'receipts' => '/api/paymentsonaccount/receipts',
                'receipt' => '/api/paymentsonaccount/receipts/{id}',
                'applications' => '/api/paymentsonaccount/receipts/{id}/applications',
                'email' => '/api/paymentsonaccount/receipts/{id}/email',
                'pdf' => '/api/paymentsonaccount/receipts/{id}/pdf',
                'unpaid_invoices' => '/api/paymentsonaccount/clients/{id}/unpaid-invoices',
                'client_payment_modes' => '/api/paymentsonaccount/clients/{id}/payment-modes',
                'statement' => '/api/paymentsonaccount/clients/{id}/statement',
                'report' => '/api/paymentsonaccount/reports/receipts',
                'credit_report' => '/api/paymentsonaccount/reports/credits',
            ],
        ], self::HTTP_OK);
    }

    public function receipts_get($id = null)
    {
        if ($id !== null) {
            $receipt = $this->poaModel->get_receipt((int) $id);
            if (!$receipt) {
                $this->notFound('Receipt not found.');
                return;
            }
            $data = (array) $receipt;
            $data['applications'] = $this->applications((int) $id);
            $this->response($data, self::HTTP_OK);
            return;
        }

        $table = db_prefix() . 'receipts';
        $this->db->select('r.*, c.company AS client_name');
        $this->db->from($table . ' AS r');
        $this->db->join(db_prefix() . 'clients AS c', 'c.userid = r.client_id', 'left');
        foreach (['client_id', 'payment_mode', 'transaction_id', 'receipt_number'] as $field) {
            $value = $this->get($field);
            if ($value !== null && $value !== '') {
                $this->db->where('r.' . $field, $value);
            }
        }
        if ($this->get('from')) {
            $this->db->where('r.payment_date >=', $this->get('from'));
        }
        if ($this->get('to')) {
            $this->db->where('r.payment_date <=', $this->get('to'));
        }

        $page = max(1, (int) $this->get('page'));
        $perPage = min(100, max(1, (int) ($this->get('per_page') ?: 25)));
        $this->db->order_by('r.id', 'desc')->limit($perPage, ($page - 1) * $perPage);
        $rows = $this->db->get()->result_array();
        $this->response(['data' => $rows, 'meta' => ['page' => $page, 'per_page' => $perPage, 'count' => count($rows), 'has_more' => count($rows) === $perPage]], self::HTTP_OK);
    }

    public function receipts_post()
    {
        $payload = $this->payload();
        foreach (['client_id', 'amount', 'payment_mode'] as $required) {
            if (!isset($payload[$required]) || $payload[$required] === '') {
                $this->response(['status' => false, 'message' => $required . ' is required.'], self::HTTP_BAD_REQUEST);
                return;
            }
        }
        if ((int) $payload['client_id'] < 1 || (float) $payload['amount'] <= 0) {
            $this->response(['status' => false, 'message' => 'A valid client_id and positive amount are required.'], self::HTTP_BAD_REQUEST);
            return;
        }

        try {
            $id = $this->poaModel->create_receipt(
                (int) $payload['client_id'],
                (float) $payload['amount'],
                (string) $payload['payment_mode'],
                $this->ids($payload['invoice_ids'] ?? []),
                (string) ($payload['note'] ?? ''),
                to_sql_date($payload['payment_date'] ?? date('Y-m-d')),
                (string) ($payload['payment_method'] ?? ''),
                (string) ($payload['transaction_id'] ?? ''),
                !empty($payload['on_account']),
                isset($payload['source_payment_id']) ? (int) $payload['source_payment_id'] : null,
                isset($payload['manual_receipt_digits']) ? preg_replace('/\D+/', '', (string) $payload['manual_receipt_digits']) : null
            );
            $emailSent = null;
            if (!empty($payload['send_email'])) {
                $emailSent = (bool) $this->poaModel->send_receipt_email($id);
            }
            paymentsonaccount_emit_event('receipt_created', $id, ['source' => 'api-v3', 'email_sent' => $emailSent]);
            if ($emailSent) {
                paymentsonaccount_emit_event('receipt_email', $id, ['source' => 'api-v3', 'email_sent' => true]);
            }
            $this->response(['status' => true, 'id' => (int) $id, 'email_sent' => $emailSent], self::HTTP_CREATED);
        } catch (Throwable $e) {
            $this->response(['status' => false, 'message' => $e->getMessage()], self::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function receipts_put($id)
    {
        if (!$this->receiptExists($id)) {
            return;
        }
        $payload = $this->payload();
        $map = ['amount' => 'total_amount', 'payment_date' => 'payment_date', 'payment_mode' => 'payment_mode', 'payment_method' => 'payment_method', 'transaction_id' => 'transaction_id', 'note' => 'note'];
        $update = [];
        foreach ($map as $input => $column) {
            if (array_key_exists($input, $payload)) {
                $update[$column] = $input === 'amount' ? (float) $payload[$input] : ($input === 'payment_date' ? to_sql_date($payload[$input]) : (string) $payload[$input]);
            }
        }
        if (isset($payload['receipt_number'])) {
            $number = trim((string) $payload['receipt_number']);
            if ($number === '' || $this->poaModel->is_receipt_number_taken($number, (int) $id)) {
                $this->response(['status' => false, 'message' => 'Receipt number is invalid or already exists.'], self::HTTP_CONFLICT);
                return;
            }
            $update['receipt_number'] = $number;
        }
        if (!$update) {
            $this->response(['status' => false, 'message' => 'No writable receipt fields supplied.'], self::HTTP_BAD_REQUEST);
            return;
        }
        $ok = (bool) $this->poaModel->update_receipt_fields((int) $id, $update);
        if ($ok) {
            paymentsonaccount_emit_event('receipt_updated', (int) $id, ['source' => 'api-v3', 'changed_fields' => array_keys($update)]);
        }
        $this->response(['status' => $ok, 'id' => (int) $id], $ok ? self::HTTP_OK : self::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function receipts_delete($id)
    {
        if (!$this->receiptExists($id)) {
            return;
        }
        $snapshot = $this->poaModel->get_receipt((int) $id);
        $ok = (bool) $this->poaModel->delete_receipt((int) $id);
        if ($ok) {
            paymentsonaccount_emit_event('receipt_deleted', (int) $id, ['source' => 'api-v3'], $snapshot);
        }
        $this->response(['status' => $ok, 'message' => $ok ? 'Receipt deleted.' : 'Receipt could not be deleted.'], $ok ? self::HTTP_OK : self::HTTP_CONFLICT);
    }

    public function applications_get($receiptId)
    {
        if (!$this->receiptExists($receiptId)) {
            return;
        }
        $this->response(['data' => $this->applications((int) $receiptId)], self::HTTP_OK);
    }

    public function applications_post($receiptId)
    {
        if (!$this->receiptExists($receiptId)) {
            return;
        }
        $payload = $this->payload();
        $allocations = [];
        if (!empty($payload['allocations']) && is_array($payload['allocations'])) {
            foreach ($payload['allocations'] as $allocation) {
                $invoiceId = (int) ($allocation['invoice_id'] ?? 0);
                $amount = isset($allocation['amount']) ? (float) $allocation['amount'] : PHP_FLOAT_MAX;
                if ($invoiceId > 0 && $amount > 0) {
                    $allocations[] = ['invoice_id' => $invoiceId, 'amount' => $amount];
                }
            }
        } else {
            foreach ($this->ids($payload['invoice_ids'] ?? []) as $invoiceId) {
                $allocations[] = ['invoice_id' => $invoiceId, 'amount' => PHP_FLOAT_MAX];
            }
        }
        if (!$allocations) {
            $this->response(['status' => false, 'message' => 'allocations or invoice_ids are required.'], self::HTTP_BAD_REQUEST);
            return;
        }
        try {
            $applied = (float) $this->poaModel->apply_receipt_to_invoices((int) $receiptId, $allocations);
            if ($applied > 0) {
                paymentsonaccount_emit_event('receipt_applied', (int) $receiptId, ['source' => 'api-v3', 'applied_amount' => $applied]);
            }
            $this->response(['status' => $applied > 0, 'applied' => $applied, 'applications' => $this->applications((int) $receiptId)], $applied > 0 ? self::HTTP_OK : self::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) {
            $this->response(['status' => false, 'message' => $e->getMessage()], self::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function application_delete($receiptId, $paymentId)
    {
        $bridge = db_prefix() . 'receipt_invoice_applications';
        $row = $this->db->where('receipt_id', (int) $receiptId)->where('payment_record_id', (int) $paymentId)->get($bridge)->row_array();
        if (!$row) {
            $this->notFound('Receipt application not found.');
            return;
        }
        $this->load->model('payments_model');
        if (!$this->payments_model->delete((int) $paymentId)) {
            $this->response(['status' => false, 'message' => 'Core payment could not be deleted.'], self::HTTP_CONFLICT);
            return;
        }
        $this->db->where('receipt_id', (int) $receiptId)->where('payment_record_id', (int) $paymentId)->delete($bridge);
        $this->syncAppliedInvoices((int) $receiptId);
        paymentsonaccount_emit_event('application_deleted', (int) $receiptId, ['source' => 'api-v3', 'payment_id' => (int) $paymentId, 'invoice_id' => (int) $row['invoice_id']]);
        $this->response(['status' => true, 'message' => 'Receipt application deleted.'], self::HTTP_OK);
    }

    public function email_post($receiptId)
    {
        if (!$this->receiptExists($receiptId)) {
            return;
        }
        $sent = (bool) $this->poaModel->send_receipt_email((int) $receiptId);
        paymentsonaccount_emit_event('receipt_email', (int) $receiptId, ['source' => 'api-v3', 'email_sent' => $sent]);
        $this->response(['status' => $sent, 'email_sent' => $sent], $sent ? self::HTTP_OK : self::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function pdf_get($receiptId)
    {
        $receipt = $this->poaModel->get_receipt((int) $receiptId);
        if (!$receipt) {
            $this->notFound('Receipt not found.');
            return;
        }
        try {
            $this->load->model('clients_model');
            $this->load->model('payment_modes_model');
            $client = $this->clients_model->get((int) $receipt->client_id);
            $mode = $this->payment_modes_model->get($receipt->payment_mode);
            $receipt->client = $client;
            $receipt->client_name = $client ? $client->company : '';
            $receipt->payment_mode_name = $mode ? $mode->name : '';
            $receipt->ref = str_pad($receipt->receipt_number, 6, '0', STR_PAD_LEFT);
            $receipt->invoices = json_decode($receipt->invoices_applied, true) ?: [];
            $pdf = $this->poaModel->receipt_pdf($receipt);
            $bytes = $pdf->Output('', 'S');
            $this->response(['filename' => 'payment_receipt_' . (int) $receiptId . '.pdf', 'content_type' => 'application/pdf', 'content_base64' => base64_encode($bytes)], self::HTTP_OK);
        } catch (Throwable $e) {
            $this->response(['status' => false, 'message' => $e->getMessage()], self::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function unpaid_invoices_get($clientId)
    {
        $this->load->model('invoices_model');
        $this->response(['data' => $this->invoices_model->get_unpaid_invoices((int) $clientId) ?: []], self::HTTP_OK);
    }

    public function client_modes_get($clientId)
    {
        $ids = $this->db->select('payment_mode_id')->where('client_id', (int) $clientId)->get(db_prefix() . 'poa_client_payment_modes')->result_array();
        $this->response(['client_id' => (int) $clientId, 'payment_mode_ids' => array_map('intval', array_column($ids, 'payment_mode_id'))], self::HTTP_OK);
    }

    public function client_modes_put($clientId)
    {
        $ids = $this->ids($this->payload()['payment_mode_ids'] ?? []);
        $table = db_prefix() . 'poa_client_payment_modes';
        $this->db->trans_start();
        $this->db->where('client_id', (int) $clientId)->delete($table);
        foreach ($ids as $id) {
            $this->db->insert($table, ['client_id' => (int) $clientId, 'payment_mode_id' => $id, 'created_at' => date('Y-m-d H:i:s')]);
        }
        $this->db->trans_complete();
        $ok = $this->db->trans_status();
        $this->response(['status' => $ok, 'client_id' => (int) $clientId, 'payment_mode_ids' => $ids], $ok ? self::HTTP_OK : self::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function statement_get($clientId)
    {
        $from = $this->get('from') ?: date('Y-m-01');
        $to = $this->get('to') ?: date('Y-m-d');
        $invoices = $this->db->where('clientid', (int) $clientId)->where('date >=', $from)->where('date <=', $to)->order_by('date', 'asc')->get(db_prefix() . 'invoices')->result_array();
        $receipts = $this->db->where('client_id', (int) $clientId)->where('payment_date >=', $from)->where('payment_date <=', $to)->order_by('payment_date', 'asc')->get(db_prefix() . 'receipts')->result_array();
        $credits = [];
        if ($this->db->table_exists(db_prefix() . 'creditnotes')) {
            $credits = $this->db->where('clientid', (int) $clientId)->where('date >=', $from)->where('date <=', $to)->order_by('date', 'asc')->get(db_prefix() . 'creditnotes')->result_array();
        }
        $invoiceTotal = array_sum(array_map(function ($row) { return (float) $row['total']; }, $invoices));
        $receiptTotal = array_sum(array_map(function ($row) { return (float) $row['total_amount']; }, $receipts));
        $creditTotal = array_sum(array_map(function ($row) { return (float) $row['total']; }, $credits));
        $this->response(['client_id' => (int) $clientId, 'from' => $from, 'to' => $to, 'invoices' => $invoices, 'receipts' => $receipts, 'credits' => $credits, 'totals' => ['invoices' => $invoiceTotal, 'receipts' => $receiptTotal, 'credits' => $creditTotal, 'balance' => $invoiceTotal - $receiptTotal - $creditTotal]], self::HTTP_OK);
    }

    public function reports_get()
    {
        $this->receipts_get();
    }

    public function credits_get()
    {
        $table = db_prefix() . 'creditnotes';
        if (!$this->db->table_exists($table)) {
            $this->response(['data' => [], 'meta' => ['count' => 0]], self::HTTP_OK);
            return;
        }
        if ($this->get('client_id')) {
            $this->db->where('clientid', (int) $this->get('client_id'));
        }
        if ($this->get('from')) {
            $this->db->where('date >=', $this->get('from'));
        }
        if ($this->get('to')) {
            $this->db->where('date <=', $this->get('to'));
        }
        $page = max(1, (int) $this->get('page'));
        $perPage = min(100, max(1, (int) ($this->get('per_page') ?: 25)));
        $rows = $this->db->order_by('id', 'desc')->limit($perPage, ($page - 1) * $perPage)->get($table)->result_array();
        $this->response(['data' => $rows, 'meta' => ['page' => $page, 'per_page' => $perPage, 'count' => count($rows), 'has_more' => count($rows) === $perPage, 'total' => array_sum(array_map(function ($row) { return (float) $row['total']; }, $rows))]], self::HTTP_OK);
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

    private function ids($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : explode(',', $value);
        }
        return array_values(array_unique(array_filter(array_map('intval', is_array($value) ? $value : []), function ($id) { return $id > 0; })));
    }

    private function receiptExists($id)
    {
        if ((int) $id < 1 || !$this->poaModel->get_receipt((int) $id)) {
            $this->notFound('Receipt not found.');
            return false;
        }
        return true;
    }

    private function applications($receiptId)
    {
        $table = db_prefix() . 'receipt_invoice_applications';
        if (!$this->db->table_exists($table)) {
            return [];
        }
        return $this->db->where('receipt_id', $receiptId)->order_by('id', 'asc')->get($table)->result_array();
    }

    private function syncAppliedInvoices($receiptId)
    {
        $rows = $this->applications($receiptId);
        $applied = array_map(function ($row) { return ['invoice_id' => (int) $row['invoice_id'], 'amount' => (float) $row['amount']]; }, $rows);
        $this->db->where('id', $receiptId)->update(db_prefix() . 'receipts', ['invoices_applied' => json_encode($applied, JSON_UNESCAPED_UNICODE)]);
    }

    private function notFound($message)
    {
        $this->response(['status' => false, 'message' => $message], self::HTTP_NOT_FOUND);
    }
}
