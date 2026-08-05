<?php
defined('BASEPATH') or exit('No direct script access allowed');

require __DIR__ . '/REST_Controller.php';
require_once __DIR__ . '/../libraries/Api_Webhook_Service.php';

/**
 * @api {get} api/webhooks List Webhooks
 * @apiVersion 3.0.0
 * @apiName GetWebhooks
 * @apiGroup Webhooks
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 * @apiDescription List all configured webhooks. Secrets are never returned (has_secret flag only). Supports page/per_page, sort, fields and date filters.
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "data": [
 *         {
 *           "id": "3",
 *           "name": "Order sync",
 *           "url": "https://hooks.example.com/perfex",
 *           "events": ["invoice_created", "invoice_paid"],
 *           "active": "1",
 *           "headers": { "X-Env": "prod" },
 *           "timeout": "30",
 *           "retry_count": "3",
 *           "last_triggered": "2026-07-20 14:32:10",
 *           "success_count": "128",
 *           "failure_count": "2",
 *           "date_created": "2026-02-14 09:12:44",
 *           "date_updated": "2026-07-20 14:32:10",
 *           "has_secret": true
 *         },
 *         {
 *           "id": "4",
 *           "name": "Ticket alerts",
 *           "url": "https://ops.example.com/webhooks/tickets",
 *           "events": ["ticket_created", "ticket_reply_created"],
 *           "active": "0",
 *           "headers": null,
 *           "timeout": "30",
 *           "retry_count": "5",
 *           "last_triggered": null,
 *           "success_count": "0",
 *           "failure_count": "0",
 *           "date_created": "2026-06-01 08:00:00",
 *           "date_updated": "2026-06-01 08:00:00",
 *           "has_secret": false
 *         }
 *       ],
 *       "meta": {
 *         "page": 1,
 *         "per_page": 25,
 *         "total": 2,
 *         "total_pages": 1,
 *         "has_more": false,
 *         "current_page": 1,
 *         "last_page": 1
 *       }
 *     }
 */

/**
 * @api {post} api/webhooks Create a Webhook
 * @apiVersion 3.0.0
 * @apiName PostWebhook
 * @apiGroup Webhooks
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 * @apiParam {String} name Webhook name.
 * @apiParam {String} url Target URL (SSRF-checked: https/http public hosts only).
 * @apiParam {String} events Comma list of event names, or "*" for all. See GET api/webhooks/events.
 * @apiParam {String} [secret] HMAC secret. Deliveries then carry X-Perfex-Signature: t=&lt;unix&gt;,v1=&lt;hmac_sha256&gt;.
 * @apiParam {Number} [timeout=30] Request timeout in seconds (1-120).
 * @apiParam {Number} [retry_count=3] Retries in queued mode (0-10).
 * @apiParam {String} [headers] JSON object of custom headers.
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "status": true,
 *       "message": "Webhook created successfully",
 *       "record_id": 3,
 *       "data": {
 *         "id": "3",
 *         "name": "Order sync",
 *         "url": "https://hooks.example.com/perfex",
 *         "events": ["invoice_created", "invoice_paid"],
 *         "active": "1",
 *         "headers": { "X-Env": "prod" },
 *         "timeout": "30",
 *         "retry_count": "3",
 *         "last_triggered": null,
 *         "success_count": "0",
 *         "failure_count": "0",
 *         "date_created": "2026-02-14 09:12:44",
 *         "date_updated": "2026-02-14 09:12:44",
 *         "has_secret": true
 *       }
 *     }
 *
 * @apiErrorExample {json} Validation-Error:
 *     HTTP/1.1 422 Unprocessable Entity
 *     {
 *       "status": false,
 *       "error": "validation_failed",
 *       "message": "Webhook validation failed",
 *       "errors": {
 *         "url": "The url field is required.",
 *         "events": "Unknown events: invoice.paid. See GET api/webhooks/events for the catalog."
 *       }
 *     }
 */

/**
 * @api {get} api/webhooks/:id Request Webhook Information
 * @apiVersion 3.0.0
 * @apiName GetWebhook
 * @apiGroup Webhooks
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "id": "3",
 *       "name": "Order sync",
 *       "url": "https://hooks.example.com/perfex",
 *       "events": ["invoice_created", "invoice_paid"],
 *       "active": "1",
 *       "headers": { "X-Env": "prod" },
 *       "timeout": "30",
 *       "retry_count": "3",
 *       "last_triggered": "2026-07-20 14:32:10",
 *       "success_count": "128",
 *       "failure_count": "2",
 *       "date_created": "2026-02-14 09:12:44",
 *       "date_updated": "2026-07-20 14:32:10",
 *       "has_secret": true
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "status": false,
 *       "message": "Webhook not found"
 *     }
 */

/**
 * @api {put} api/webhooks/:id Update a Webhook
 * @apiVersion 3.0.0
 * @apiName PutWebhook
 * @apiGroup Webhooks
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 * @apiDescription Partial update - only the provided fields change; same validation as create.
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "status": true,
 *       "message": "Webhook updated successfully",
 *       "data": {
 *         "id": "3",
 *         "name": "Order sync",
 *         "url": "https://hooks.example.com/perfex",
 *         "events": ["invoice_created", "invoice_paid", "invoice_cancelled"],
 *         "active": "1",
 *         "headers": { "X-Env": "prod" },
 *         "timeout": "45",
 *         "retry_count": "3",
 *         "last_triggered": "2026-07-20 14:32:10",
 *         "success_count": "128",
 *         "failure_count": "2",
 *         "date_created": "2026-02-14 09:12:44",
 *         "date_updated": "2026-07-21 10:05:12",
 *         "has_secret": true
 *       }
 *     }
 *
 * @apiErrorExample {json} Not-Found:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "status": false,
 *       "message": "Webhook not found"
 *     }
 *
 * @apiErrorExample {json} Nothing-To-Update:
 *     HTTP/1.1 422 Unprocessable Entity
 *     {
 *       "status": false,
 *       "error": "validation_failed",
 *       "message": "Nothing to update",
 *       "errors": {
 *         "_general": "No updatable fields provided"
 *       }
 *     }
 */

/**
 * @api {delete} api/webhooks/:id Delete a Webhook
 * @apiVersion 3.0.0
 * @apiName DeleteWebhook
 * @apiGroup Webhooks
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 * @apiDescription Deletes the webhook together with its delivery logs and queued jobs.
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "status": true,
 *       "message": "Webhook deleted successfully"
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "status": false,
 *       "message": "Webhook not found"
 *     }
 */

/**
 * @api {post} api/webhooks/:id/toggle Enable/Disable a Webhook
 * @apiVersion 3.0.0
 * @apiName ToggleWebhook
 * @apiGroup Webhooks
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "status": true,
 *       "message": "Webhook enabled",
 *       "active": 1
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "status": false,
 *       "message": "Webhook not found"
 *     }
 */

/**
 * @api {get} api/webhooks/events Webhook Event Catalog
 * @apiVersion 3.0.0
 * @apiName GetWebhookEvents
 * @apiGroup Webhooks
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 * @apiDescription The authoritative catalog: 124 events across 22 resource groups (invoice_created, lead_status_changed, ticket_reply_created, kb_article_created...).
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "total_events": 124,
 *       "wildcard": "*",
 *       "groups": {
 *         "customers": {
 *           "customer_created": "Customer created",
 *           "customer_updated": "Customer updated",
 *           "customer_deleted": "Customer deleted"
 *         },
 *         "invoices": {
 *           "invoice_created": "Invoice created",
 *           "invoice_paid": "Invoice fully paid",
 *           "invoice_status_changed": "Invoice status changed"
 *         },
 *         "tickets": {
 *           "ticket_created": "Ticket created",
 *           "ticket_reply_created": "Reply added to ticket"
 *         },
 *         "system": {
 *           "webhook_created": "Webhook created",
 *           "webhook_updated": "Webhook updated",
 *           "webhook_deleted": "Webhook deleted"
 *         }
 *       }
 *     }
 */

/**
 * @api {get} api/webhooks/:id/logs Webhook Delivery Logs
 * @apiVersion 3.0.0
 * @apiName GetWebhookLogs
 * @apiGroup Webhooks
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 * @apiDescription Latest 500 delivery attempts with status, response code and error details. Paginated via page/per_page.
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "data": [
 *         {
 *           "id": "982",
 *           "webhook_id": "3",
 *           "event": "invoice_paid",
 *           "url": "https://hooks.example.com/perfex",
 *           "payload": "{\"event\":\"invoice_paid\",\"timestamp\":1753021930,\"data\":{\"webhook_id\":3}}",
 *           "response_code": "200",
 *           "response_body": "{\"ok\":true}",
 *           "error_message": null,
 *           "attempt_number": "1",
 *           "status": "success",
 *           "triggered_at": "2026-07-20 14:32:10"
 *         },
 *         {
 *           "id": "981",
 *           "webhook_id": "3",
 *           "event": "invoice_created",
 *           "url": "https://hooks.example.com/perfex",
 *           "payload": "{\"event\":\"invoice_created\",\"timestamp\":1753021300,\"data\":{\"webhook_id\":3}}",
 *           "response_code": "500",
 *           "response_body": "Internal Server Error",
 *           "error_message": "HTTP 500: Internal Server Error",
 *           "attempt_number": "2",
 *           "status": "failed",
 *           "triggered_at": "2026-07-20 14:21:40"
 *         }
 *       ],
 *       "meta": {
 *         "page": 1,
 *         "per_page": 25,
 *         "total": 2,
 *         "total_pages": 1,
 *         "has_more": false,
 *         "current_page": 1,
 *         "last_page": 1
 *       }
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "status": false,
 *       "message": "Webhook not found"
 *     }
 */
class Webhooks extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_webhooks_permission();
    }

    /**
     * Tokens with granular permissions enabled need the webhooks feature;
     * full-access tokens (permission_enable = 0) pass.
     */
    private function require_webhooks_permission()
    {
        $token = isset($this->rest->key) ? $this->rest->key : '';
        if ($token === '') {
            return; // token-less access is already rejected upstream
        }

        $row = $this->db
            ->where('token', $token)
            ->get(db_prefix() . 'user_api')
            ->row_array();

        if (!$row || (int)$row['permission_enable'] !== 1) {
            return;
        }

        $allowed = $this->db
            ->where('api_id', $row['id'])
            ->where('feature', 'webhooks')
            ->get(db_prefix() . 'user_api_permissions')
            ->num_rows();

        if ($allowed === 0) {
            $this->response([
                'status'  => FALSE,
                'message' => 'This token has no webhooks permission',
            ], REST_Controller::HTTP_FORBIDDEN);
        }
    }

    /**
     * Serialize a webhook row for API output (secret never leaves the server).
     */
    private function present(array $row)
    {
        $row['events']     = array_values(array_filter(array_map('trim', explode(',', (string)$row['events']))));
        $row['headers']    = !empty($row['headers']) ? json_decode($row['headers'], true) : null;
        $row['has_secret'] = !empty($row['secret']);
        unset($row['secret']);
        return $row;
    }

    /**
     * Validate + normalize a create/update payload. Responds 422 on problems.
     *
     * @param bool $creating
     * @return array Fields ready for insert/update
     */
    private function validated_payload($creating)
    {
        $post   = $this->input->post();
        $errors = [];
        $data   = [];

        if ($creating || array_key_exists('name', $post)) {
            $name = isset($post['name']) ? trim((string)$post['name']) : '';
            if ($name === '') {
                $errors['name'] = 'The name field is required.';
            } else {
                $data['name'] = $name;
            }
        }

        if ($creating || array_key_exists('url', $post)) {
            $url = isset($post['url']) ? trim((string)$post['url']) : '';
            $ssrfError = null;
            $resolved  = null;
            $strict = function_exists('get_option') ? (get_option('api_webhook_ssrf_strict') == '1') : false;
            if ($url === '') {
                $errors['url'] = 'The url field is required.';
            } elseif (!Api_Webhook_Service::isUrlSafe($url, $strict, $resolved, $ssrfError)) {
                $errors['url'] = 'URL rejected: ' . $ssrfError;
            } else {
                $data['url'] = $url;
            }
        }

        if ($creating || array_key_exists('events', $post)) {
            $events = isset($post['events']) ? $post['events'] : '';
            if (is_string($events)) {
                $events = array_filter(array_map('trim', explode(',', $events)));
            }
            if (!is_array($events) || empty($events)) {
                $errors['events'] = 'Provide at least one event (or "*").';
            } else {
                $valid   = Api_Webhook_Service::eventNames();
                $unknown = array_values(array_diff($events, $valid));
                if (!empty($unknown)) {
                    $errors['events'] = 'Unknown events: ' . implode(', ', $unknown)
                        . '. See GET api/webhooks/events for the catalog.';
                } else {
                    $data['events'] = implode(',', $events);
                }
            }
        }

        if (array_key_exists('secret', $post)) {
            $data['secret'] = (string)$post['secret'];
        }
        if (array_key_exists('active', $post)) {
            $data['active'] = (int)((string)$post['active'] === '1' || $post['active'] === 1 || $post['active'] === true);
        }
        if (array_key_exists('timeout', $post)) {
            $data['timeout'] = max(1, min(120, (int)$post['timeout']));
        }
        if (array_key_exists('retry_count', $post)) {
            $data['retry_count'] = max(0, min(10, (int)$post['retry_count']));
        }
        if (array_key_exists('headers', $post)) {
            $headers = $post['headers'];
            if (is_string($headers) && $headers !== '') {
                $decoded = json_decode($headers, true);
                if (!is_array($decoded)) {
                    $errors['headers'] = 'Headers must be a JSON object.';
                } else {
                    $data['headers'] = json_encode($decoded);
                }
            } elseif (is_array($headers)) {
                $data['headers'] = json_encode($headers);
            } else {
                $data['headers'] = null;
            }
        }

        if (!empty($errors)) {
            $this->api_validation_error($errors, 'Webhook validation failed');
        }

        return $data;
    }

    // ------------------------------------------------------------------
    // CRUD
    // ------------------------------------------------------------------

    public function data_get($id = '')
    {
        if (!empty($id)) {
            $row = $this->db->where('id', (int)$id)->get(db_prefix() . 'api_webhooks')->row_array();
            if (!$row) {
                $this->response(['status' => FALSE, 'message' => 'Webhook not found'], REST_Controller::HTTP_NOT_FOUND);
            }
            $this->response($this->present($row), REST_Controller::HTTP_OK);
        }

        $rows = $this->db->order_by('id', 'ASC')->get(db_prefix() . 'api_webhooks')->result_array();
        $rows = array_map([$this, 'present'], $rows);
        $this->api_list_response($rows);
    }

    public function data_post()
    {
        $data = $this->validated_payload(true);

        $data['active']       = isset($data['active']) ? $data['active'] : 1;
        $data['timeout']      = isset($data['timeout']) ? $data['timeout'] : 30;
        $data['retry_count']  = isset($data['retry_count']) ? $data['retry_count'] : 3;
        $data['date_created'] = date('Y-m-d H:i:s');

        $this->db->insert(db_prefix() . 'api_webhooks', $data);
        $id = $this->db->insert_id();

        if (!$id) {
            $this->response(['status' => FALSE, 'message' => 'Webhook could not be created'], REST_Controller::HTTP_BAD_REQUEST);
        }

        $this->fire_system_event('webhook_created', $id);

        $row = $this->db->where('id', $id)->get(db_prefix() . 'api_webhooks')->row_array();
        $this->response([
            'status'    => TRUE,
            'message'   => 'Webhook created successfully',
            'record_id' => $id,
            'data'      => $this->present($row),
        ], REST_Controller::HTTP_OK);
    }

    public function data_put($id = '')
    {
        $existing = $this->find_or_404($id);
        $data     = $this->validated_payload(false);

        if (empty($data)) {
            $this->api_validation_error(['_general' => 'No updatable fields provided'], 'Nothing to update');
        }

        $this->db->where('id', $existing['id'])->update(db_prefix() . 'api_webhooks', $data);
        $this->fire_system_event('webhook_updated', $existing['id']);

        $row = $this->db->where('id', $existing['id'])->get(db_prefix() . 'api_webhooks')->row_array();
        $this->response([
            'status'  => TRUE,
            'message' => 'Webhook updated successfully',
            'data'    => $this->present($row),
        ], REST_Controller::HTTP_OK);
    }

    public function data_delete($id = '')
    {
        $existing = $this->find_or_404($id);

        $this->db->where('webhook_id', $existing['id'])->delete(db_prefix() . 'api_webhook_logs');
        if ($this->db->table_exists(db_prefix() . 'api_webhook_queue')) {
            $this->db->where('webhook_id', $existing['id'])->delete(db_prefix() . 'api_webhook_queue');
        }
        $this->db->where('id', $existing['id'])->delete(db_prefix() . 'api_webhooks');

        $this->fire_system_event('webhook_deleted', $existing['id']);

        $this->response(['status' => TRUE, 'message' => 'Webhook deleted successfully'], REST_Controller::HTTP_OK);
    }

    // ------------------------------------------------------------------
    // Extras
    // ------------------------------------------------------------------

    public function toggle_post($id = '')
    {
        $existing  = $this->find_or_404($id);
        $newActive = ((int)$existing['active'] === 1) ? 0 : 1;

        $this->db->where('id', $existing['id'])->update(db_prefix() . 'api_webhooks', ['active' => $newActive]);

        $this->response([
            'status'  => TRUE,
            'message' => $newActive ? 'Webhook enabled' : 'Webhook disabled',
            'active'  => $newActive,
        ], REST_Controller::HTTP_OK);
    }

    public function events_get()
    {
        $catalog = Api_Webhook_Service::eventCatalog();
        $total   = 0;
        foreach ($catalog as $events) {
            $total += count($events);
        }

        $this->response([
            'total_events' => $total,
            'wildcard'     => '*',
            'groups'       => $catalog,
        ], REST_Controller::HTTP_OK);
    }

    public function logs_get($id = '')
    {
        $existing = $this->find_or_404($id);

        $rows = $this->db
            ->where('webhook_id', $existing['id'])
            ->order_by('id', 'DESC')
            ->limit(500)
            ->get(db_prefix() . 'api_webhook_logs')
            ->result_array();

        $this->api_list_response($rows, ['date_field' => 'triggered_at']);
    }

    // ------------------------------------------------------------------
    // Internals
    // ------------------------------------------------------------------

    private function find_or_404($id)
    {
        if (empty($id) || !is_numeric($id)) {
            $this->response(['status' => FALSE, 'message' => 'Invalid webhook ID'], REST_Controller::HTTP_NOT_FOUND);
        }
        $row = $this->db->where('id', (int)$id)->get(db_prefix() . 'api_webhooks')->row_array();
        if (!$row) {
            $this->response(['status' => FALSE, 'message' => 'Webhook not found'], REST_Controller::HTTP_NOT_FOUND);
        }
        return $row;
    }

    private function fire_system_event($event, $webhookId)
    {
        try {
            $service = new Api_Webhook_Service();
            $service->triggerWebhooks($event, ['webhook_id' => $webhookId]);
        } catch (Exception $e) {
            // System events must never break the management API
        }
    }
}
