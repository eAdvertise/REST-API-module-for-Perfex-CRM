<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/REST_Controller.php';

/** REST API facade for the optional Delivery Notes module. */
class Delivery_notes extends REST_Controller
{
    private $deliveryNotesModel;

    public function __construct()
    {
        parent::__construct();

        if (!function_exists('module_dir_path') || !is_dir(module_dir_path('delivery_notes')) || !$this->app_modules->is_active('delivery_notes')) {
            $this->response(['status' => false, 'message' => 'Delivery Notes module is not installed or active.'], self::HTTP_SERVICE_UNAVAILABLE);
            return;
        }

        $model = module_dir_path('delivery_notes', 'models/Delivery_notes_model.php');
        if (!is_file($model)) {
            $this->response(['status' => false, 'message' => 'Delivery Notes model is unavailable.'], self::HTTP_SERVICE_UNAVAILABLE);
            return;
        }

        $this->load->model('delivery_notes/delivery_notes_model');
        $this->deliveryNotesModel = $this->delivery_notes_model;
    }

    public function catalog_get()
    {
        $this->response([
            'status' => true,
            'endpoints' => [
                'delivery_notes' => '/api/delivery_notes/notes',
                'delivery_note' => '/api/delivery_notes/notes/{id}',
                'statuses' => '/api/delivery_notes/statuses',
                'status' => '/api/delivery_notes/notes/{id}/status',
                'email' => '/api/delivery_notes/notes/{id}/email',
                'pdf' => '/api/delivery_notes/notes/{id}/pdf',
                'copy' => '/api/delivery_notes/notes/{id}/copy',
                'convert_to_invoice' => '/api/delivery_notes/notes/{id}/convert-to-invoice',
                'from_invoice' => '/api/delivery_notes/from-invoice/{id}',
                'from_estimate' => '/api/delivery_notes/from-estimate/{id}',
                'from_purchase_order' => '/api/delivery_notes/from-purchase-order/{id}',
            ],
        ], self::HTTP_OK);
    }

    public function notes_get($id = null)
    {
        if ($id !== null) {
            $note = $this->deliveryNotesModel->get((int) $id);
            if (!$note) {
                $this->notFound();
                return;
            }
            $this->response($note, self::HTTP_OK);
            return;
        }

        $table = db_prefix() . 'delivery_notes';
        $this->db->from($table);
        foreach (['clientid', 'status', 'currency', 'sale_agent', 'project_id'] as $field) {
            $value = $this->get($field);
            if ($value !== null && $value !== '') {
                $this->db->where($field, $value);
            }
        }
        if ($this->get('from')) {
            $this->db->where('date >=', $this->get('from'));
        }
        if ($this->get('to')) {
            $this->db->where('date <=', $this->get('to'));
        }

        $page = max(1, (int) $this->get('page'));
        $perPage = min(100, max(1, (int) ($this->get('per_page') ?: 25)));
        $rows = $this->db->order_by('id', 'desc')->limit($perPage, ($page - 1) * $perPage)->get()->result_array();
        $this->response([
            'data' => $rows,
            'meta' => ['page' => $page, 'per_page' => $perPage, 'count' => count($rows), 'has_more' => count($rows) === $perPage],
        ], self::HTTP_OK);
    }

    public function notes_post()
    {
        $payload = $this->payload();
        foreach (['clientid', 'currency', 'date', 'newitems'] as $required) {
            if (!isset($payload[$required]) || $payload[$required] === '' || ($required === 'newitems' && !is_array($payload[$required]))) {
                $this->badRequest($required . ' is required.');
                return;
            }
        }

        $payload += $this->addressDefaults();
        $payload['status'] = isset($payload['status']) ? (int) $payload['status'] : 1;
        $payload['number'] = isset($payload['number']) ? trim((string) $payload['number']) : get_option('next_delivery_note_number');

        try {
            $id = $this->deliveryNotesModel->add($payload);
            $this->response(['status' => (bool) $id, 'id' => (int) $id], $id ? self::HTTP_CREATED : self::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) {
            $this->unprocessable($e->getMessage());
        }
    }

    public function notes_put($id)
    {
        $current = $this->rawNote($id);
        if (!$current) {
            $this->notFound();
            return;
        }

        $payload = $this->payload();
        $columns = array_flip($this->db->list_fields(db_prefix() . 'delivery_notes'));
        $write = array_intersect_key($payload, $columns);
        foreach (['id', 'datecreated', 'addedfrom', 'hash', 'created_by'] as $readOnly) {
            unset($write[$readOnly]);
        }
        foreach (['number', 'status', 'billing_street', 'shipping_street'] as $requiredByModel) {
            if (!array_key_exists($requiredByModel, $write)) {
                $write[$requiredByModel] = $current[$requiredByModel] ?? ($requiredByModel === 'status' ? 1 : '');
            }
        }
        foreach (['items', 'newitems', 'removed_items', 'custom_fields', 'tags'] as $special) {
            if (array_key_exists($special, $payload)) {
                $write[$special] = $payload[$special];
            }
        }
        $write['removed_items'] = $write['removed_items'] ?? [];

        try {
            $updated = $this->deliveryNotesModel->update($write, (int) $id);
            $this->response(['status' => (bool) $updated, 'id' => (int) $id, 'message' => $updated ? 'Delivery note updated.' : 'No changes were made.'], self::HTTP_OK);
        } catch (Throwable $e) {
            $this->unprocessable($e->getMessage());
        }
    }

    public function notes_delete($id)
    {
        if (!$this->rawNote($id)) {
            $this->notFound();
            return;
        }
        $deleted = $this->deliveryNotesModel->delete((int) $id);
        $ok = $deleted === true;
        $this->response(['status' => $ok, 'message' => $ok ? 'Delivery note deleted.' : 'Delivery note could not be deleted.', 'details' => $ok ? null : $deleted], $ok ? self::HTTP_OK : self::HTTP_CONFLICT);
    }

    public function statuses_get()
    {
        $this->response(['data' => $this->deliveryNotesModel->get_statuses()], self::HTTP_OK);
    }

    public function status_put($id)
    {
        if (!$this->rawNote($id)) {
            $this->notFound();
            return;
        }
        $status = (int) ($this->payload()['status'] ?? 0);
        if (!in_array($status, array_map('intval', $this->deliveryNotesModel->get_statuses()), true)) {
            $this->badRequest('A valid status is required.');
            return;
        }
        $ok = (bool) $this->deliveryNotesModel->mark_action_status($status, (int) $id);
        $this->response(['status' => $ok, 'id' => (int) $id, 'delivery_note_status' => $status], $ok ? self::HTTP_OK : self::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function email_post($id)
    {
        if (!$this->rawNote($id)) {
            $this->notFound();
            return;
        }
        $payload = $this->payload();
        $ok = (bool) $this->deliveryNotesModel->send_delivery_note_to_client((int) $id, '', !isset($payload['attach_pdf']) || (bool) $payload['attach_pdf'], (string) ($payload['cc'] ?? ''));
        $this->response(['status' => $ok, 'id' => (int) $id], $ok ? self::HTTP_OK : self::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function pdf_get($id)
    {
        $note = $this->deliveryNotesModel->get((int) $id);
        if (!$note) {
            $this->notFound();
            return;
        }
        try {
            $filename = mb_strtoupper(slug_it(format_delivery_note_number($note->id))) . '.pdf';
            $content = delivery_note_pdf($note)->Output($filename, 'S');
            $this->response(['status' => true, 'filename' => $filename, 'content_type' => 'application/pdf', 'content_base64' => base64_encode($content)], self::HTTP_OK);
        } catch (Throwable $e) {
            $this->unprocessable($e->getMessage());
        }
    }

    public function copy_post($id)
    {
        $this->createdFrom(function () use ($id) { return $this->deliveryNotesModel->copy((int) $id); }, 'Delivery note could not be copied.');
    }

    public function convert_to_invoice_post($id)
    {
        $payload = $this->payload();
        $this->createdFrom(function () use ($id, $payload) {
            return $this->deliveryNotesModel->convert_to_invoice((int) $id, false, !empty($payload['draft']));
        }, 'Delivery note could not be converted to an invoice.', 'invoice_id');
    }

    public function from_invoice_post($id)
    {
        $this->createdFrom(function () use ($id) { return $this->deliveryNotesModel->convert_from_invoice((int) $id, $this->conversionStatus()); }, 'Invoice could not be converted.');
    }

    public function from_estimate_post($id)
    {
        $this->createdFrom(function () use ($id) { return $this->deliveryNotesModel->convert_from_estimate((int) $id, $this->conversionStatus()); }, 'Estimate could not be converted.');
    }

    public function from_purchase_order_post($id)
    {
        $this->createdFrom(function () use ($id) { return $this->deliveryNotesModel->convert_from_purchase_order((int) $id, $this->conversionStatus()); }, 'Purchase order could not be converted.');
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

    private function rawNote($id)
    {
        return (int) $id > 0 ? $this->db->where('id', (int) $id)->get(db_prefix() . 'delivery_notes')->row_array() : null;
    }

    private function addressDefaults()
    {
        return ['billing_street' => '', 'billing_city' => '', 'billing_state' => '', 'billing_zip' => '', 'billing_country' => 0, 'shipping_street' => '', 'shipping_city' => '', 'shipping_state' => '', 'shipping_zip' => '', 'shipping_country' => 0];
    }

    private function conversionStatus()
    {
        return !empty($this->payload()['draft']) ? 1 : 4;
    }

    private function createdFrom($callback, $failure, $key = 'id')
    {
        try {
            $id = $callback();
            $this->response(['status' => (bool) $id, $key => (int) $id, 'message' => $id ? null : $failure], $id ? self::HTTP_CREATED : self::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) {
            $this->unprocessable($e->getMessage());
        }
    }

    private function badRequest($message)
    {
        $this->response(['status' => false, 'message' => $message], self::HTTP_BAD_REQUEST);
    }

    private function unprocessable($message)
    {
        $this->response(['status' => false, 'message' => $message], self::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function notFound()
    {
        $this->response(['status' => false, 'message' => 'Delivery note not found.'], self::HTTP_NOT_FOUND);
    }
}
