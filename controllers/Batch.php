<?php
defined('BASEPATH') or exit('No direct script access allowed');

require __DIR__ . '/REST_Controller.php';
require_once __DIR__ . '/../libraries/Api_Mcp_Registry.php';

/**
 * @api {post} api/batch Batch Operations
 * @apiVersion 3.0.0
 * @apiName BatchOperations
 * @apiGroup Batch
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 * @apiDescription Execute up to 50 operations in one request, in order, with continue-on-error.
 * Operations use the same names, arguments and per-operation permission rules as the MCP tools
 * (e.g. customers_create, invoices_get, leads_search). Each item returns status plus result or error,
 * with completed/failed counters in the envelope.
 * @apiParamExample {json} Request-Example:
 * {
 *   "operations": [
 *     {"tool": "customers_create", "arguments": {"data": {"company": "Acme LTD"}}},
 *     {"tool": "invoices_get",     "arguments": {"id": 5}}
 *   ]
 * }
 * @apiSuccess {Boolean} status True only when every operation succeeded (i.e. failed is 0).
 * @apiSuccess {Number} completed Count of operations that succeeded.
 * @apiSuccess {Number} failed Count of operations that failed.
 * @apiSuccess {Object[]} results Per-operation outcomes in request order; each item carries index, tool, status and either result (on success) or error (on failure).
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "status": true,
 *       "completed": 3,
 *       "failed": 0,
 *       "results": [
 *         {
 *           "index": 0,
 *           "tool": "customers_create",
 *           "status": true,
 *           "result": { "status": true, "record_id": 142 }
 *         },
 *         {
 *           "index": 1,
 *           "tool": "invoices_get",
 *           "status": true,
 *           "result": {
 *             "id": "5",
 *             "clientid": "142",
 *             "number": "1000",
 *             "prefix": "INV-",
 *             "date": "2026-07-21",
 *             "duedate": "2026-08-20",
 *             "currency": "1",
 *             "subtotal": "1200.00",
 *             "total_tax": "0.00",
 *             "total": "1200.00",
 *             "status": "1"
 *           }
 *         },
 *         {
 *           "index": 2,
 *           "tool": "leads_update",
 *           "status": true,
 *           "result": { "status": true, "record_id": 88 }
 *         }
 *       ]
 *     }
 * @apiSuccessExample {json} Partial-Failure-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "status": false,
 *       "completed": 2,
 *       "failed": 1,
 *       "results": [
 *         {
 *           "index": 0,
 *           "tool": "customers_create",
 *           "status": true,
 *           "result": { "status": true, "record_id": 142 }
 *         },
 *         {
 *           "index": 1,
 *           "tool": "invoices_get",
 *           "status": false,
 *           "error": "Invoice not found"
 *         },
 *         {
 *           "index": 2,
 *           "tool": "leads_update",
 *           "status": true,
 *           "result": { "status": true, "record_id": 88 }
 *         }
 *       ]
 *     }
 * @apiErrorExample {json} Validation-Error:
 *     HTTP/1.1 422 Unprocessable Entity
 *     {
 *       "status": false,
 *       "error": "validation_failed",
 *       "message": "Batch validation failed",
 *       "errors": {
 *         "operations": "Provide an operations array: [{\"tool\": \"...\", \"arguments\": {...}}]"
 *       }
 *     }
 */
class Batch extends REST_Controller
{
    const MAX_OPERATIONS = 50;

    public function __construct()
    {
        parent::__construct();
    }

    public function index_post()
    {
        $raw     = file_get_contents('php://input');
        $decoded = json_decode($raw, true);

        $operations = null;
        if (is_array($decoded) && isset($decoded['operations'])) {
            $operations = $decoded['operations'];
        } elseif (is_array($this->post('operations'))) {
            $operations = $this->post('operations');
        }

        if (!is_array($operations) || empty($operations)) {
            $this->api_validation_error(
                ['operations' => 'Provide an operations array: [{"tool": "...", "arguments": {...}}]'],
                'Batch validation failed'
            );
        }
        if (count($operations) > self::MAX_OPERATIONS) {
            $this->api_validation_error(
                ['operations' => 'Too many operations: max ' . self::MAX_OPERATIONS . ' per request'],
                'Batch validation failed'
            );
        }

        $granted   = $this->granted_permissions();
        $results   = [];
        $completed = 0;
        $failed    = 0;

        foreach (array_values($operations) as $index => $operation) {
            $tool = is_array($operation) && isset($operation['tool']) ? (string)$operation['tool'] : '';
            $args = is_array($operation) && isset($operation['arguments']) && is_array($operation['arguments'])
                ? $operation['arguments'] : [];

            if ($tool === '') {
                $failed++;
                $results[] = ['index' => $index, 'status' => false, 'error' => 'Missing tool name'];
                continue;
            }

            try {
                $result = Api_Mcp_Registry::execute($tool, $args, $this, $granted);
                $completed++;
                $results[] = ['index' => $index, 'tool' => $tool, 'status' => true, 'result' => $result];
            } catch (Exception $e) {
                $failed++;
                $results[] = ['index' => $index, 'tool' => $tool, 'status' => false, 'error' => $e->getMessage()];
            }
        }

        $this->response([
            'status'    => $failed === 0,
            'completed' => $completed,
            'failed'    => $failed,
            'results'   => $results,
        ], REST_Controller::HTTP_OK);
    }

    /**
     * Same grant resolution as the MCP controller:
     * NULL = full access; otherwise feature => [capabilities].
     */
    private function granted_permissions()
    {
        $token = isset($this->rest->key) ? $this->rest->key : '';
        if ($token === '') {
            return null;
        }

        $row = $this->db->where('token', $token)->get(db_prefix() . 'user_api')->row_array();
        if (!$row || (int)$row['permission_enable'] !== 1) {
            return null;
        }

        $rows = $this->db->where('api_id', $row['id'])
            ->get(db_prefix() . 'user_api_permissions')->result_array();

        $granted = [];
        foreach ($rows as $permission) {
            $granted[$permission['feature']][] = $permission['capability'];
        }
        return $granted;
    }
}
