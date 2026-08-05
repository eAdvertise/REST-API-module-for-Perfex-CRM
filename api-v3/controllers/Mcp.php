<?php
defined('BASEPATH') or exit('No direct script access allowed');

require __DIR__ . '/REST_Controller.php';
require_once __DIR__ . '/../libraries/Api_Mcp_Registry.php';

/**
 * @api {post} api/mcp MCP Server (AI Agents)
 * @apiVersion 3.0.0
 * @apiName McpServer
 * @apiGroup MCP
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 * @apiDescription Model Context Protocol server over Streamable HTTP (JSON-RPC 2.0).
 * Exposes 148 permission-filtered CRM tools (list/get/search/create/update/delete across 22 resources,
 * plus lookups and utilities) to Claude Desktop, ChatGPT, Cursor, n8n AI Agent and any MCP client.
 * Supported methods: initialize, ping, tools/list (single page), tools/call. Batches and notifications
 * follow the JSON-RPC spec. Tool errors are returned as isError results so agents can react.
 * Disabled by default - enable it under Setup > API > Settings. Configure your MCP client with this URL
 * and the authtoken header.
 * @apiParamExample {json} tools/call example:
 * {
 *   "jsonrpc": "2.0", "id": 1, "method": "tools/call",
 *   "params": { "name": "customers_search", "arguments": { "q": "Acme" } }
 * }
 *
 * @apiSuccessExample {json} tools/list:
 *     HTTP/1.1 200 OK
 *     {
 *       "jsonrpc": "2.0",
 *       "result": {
 *         "tools": [
 *           {
 *             "name": "customers_list",
 *             "description": "List customers. Supports limit/offset and optional date range.",
 *             "inputSchema": {
 *               "type": "object",
 *               "properties": {
 *                 "limit": { "type": "integer", "description": "Max rows (default 25, max 100)" },
 *                 "offset": { "type": "integer", "description": "Rows to skip" },
 *                 "created_after": { "type": "string", "description": "ISO date lower bound" },
 *                 "created_before": { "type": "string", "description": "ISO date upper bound" }
 *               }
 *             }
 *           },
 *           {
 *             "name": "customers_search",
 *             "description": "Search customers by keyword (matches company, vat, phonenumber).",
 *             "inputSchema": {
 *               "type": "object",
 *               "properties": {
 *                 "q": { "type": "string", "description": "Search keyword" },
 *                 "limit": { "type": "integer", "description": "Max rows (default 25, max 100)" }
 *               },
 *               "required": ["q"]
 *             }
 *           },
 *           {
 *             "name": "invoices_create",
 *             "description": "Create a invoice. Pass the record fields as the \"data\" object (same field names as the REST API).",
 *             "inputSchema": {
 *               "type": "object",
 *               "properties": {
 *                 "data": { "type": "object", "description": "Field map for the new record" }
 *               },
 *               "required": ["data"]
 *             }
 *           }
 *         ]
 *       },
 *       "id": 1
 *     }
 *
 * @apiSuccessExample {json} tools/call:
 *     HTTP/1.1 200 OK
 *     {
 *       "jsonrpc": "2.0",
 *       "result": {
 *         "content": [
 *           {
 *             "type": "text",
 *             "text": "[\n    {\n        \"userid\": \"12\",\n        \"company\": \"Acme Corporation\",\n        \"vat\": \"GB123456789\",\n        \"phonenumber\": \"+1 415 555 0132\",\n        \"city\": \"San Francisco\",\n        \"zip\": \"94103\",\n        \"state\": \"California\",\n        \"country\": \"231\",\n        \"datecreated\": \"2026-02-14 09:21:45\",\n        \"active\": \"1\"\n    }\n]"
 *           }
 *         ],
 *         "isError": false
 *       },
 *       "id": 2
 *     }
 *
 * @apiErrorExample {json} Unknown method:
 *     HTTP/1.1 200 OK
 *     {
 *       "jsonrpc": "2.0",
 *       "error": {
 *         "code": -32601,
 *         "message": "Method not found: tools/get"
 *       },
 *       "id": 3
 *     }
 */
class Mcp extends REST_Controller
{
    const PROTOCOL_VERSION = '2024-11-05';
    const SERVER_VERSION   = '3.0.0';

    public function __construct()
    {
        parent::__construct();
    }

    public function index_get()
    {
        $this->response([
            'jsonrpc' => '2.0',
            'error'   => ['code' => -32601, 'message' => 'Use POST with JSON-RPC 2.0 payloads; SSE streaming is not offered'],
            'id'      => null,
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }

    public function index_post()
    {
        if (function_exists('get_option') && get_option('api_mcp_enabled') !== '1') {
            $this->response([
                'jsonrpc' => '2.0',
                'error'   => ['code' => -32000, 'message' => 'MCP server is disabled. Enable it in Setup > API > Settings.'],
                'id'      => null,
            ], REST_Controller::HTTP_FORBIDDEN);
        }

        $raw     = file_get_contents('php://input');
        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            $this->response([
                'jsonrpc' => '2.0',
                'error'   => ['code' => -32700, 'message' => 'Parse error: request body must be JSON'],
                'id'      => null,
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $isBatch  = array_keys($decoded) === range(0, count($decoded) - 1);
        $messages = $isBatch ? $decoded : [$decoded];
        $granted  = $this->granted_permissions();

        $responses = [];
        foreach ($messages as $message) {
            $reply = $this->handle_message(is_array($message) ? $message : [], $granted);
            if ($reply !== null) {
                $responses[] = $reply;
            }
        }

        if (empty($responses)) {
            // Notification-only payload
            $this->response(null, REST_Controller::HTTP_ACCEPTED);
        }

        $this->response($isBatch ? $responses : $responses[0], REST_Controller::HTTP_OK);
    }

    /**
     * Handle one JSON-RPC message. Returns the response array, or NULL for
     * notifications (no id).
     */
    public function handle_message(array $message, $granted = null)
    {
        $id             = array_key_exists('id', $message) ? $message['id'] : null;
        $isNotification = !array_key_exists('id', $message);
        $method         = isset($message['method']) ? (string)$message['method'] : '';
        $params         = isset($message['params']) && is_array($message['params']) ? $message['params'] : [];

        if (!isset($message['jsonrpc']) || $message['jsonrpc'] !== '2.0' || $method === '') {
            return $isNotification ? null : $this->rpc_error($id, -32600, 'Invalid Request: jsonrpc 2.0 with a method is required');
        }

        // Notifications require no response
        if (strpos($method, 'notifications/') === 0) {
            return null;
        }

        switch ($method) {
            case 'initialize':
                return $this->rpc_result($id, [
                    'protocolVersion' => self::PROTOCOL_VERSION,
                    'capabilities'    => ['tools' => ['listChanged' => false]],
                    'serverInfo'      => [
                        'name'    => 'perfex-crm-rest-api',
                        'version' => self::SERVER_VERSION,
                    ],
                ]);

            case 'ping':
                return $this->rpc_result($id, new stdClass());

            case 'tools/list':
                // All tools in a single page - Anthropic clients do not follow
                // cursor pagination, so nextCursor is intentionally omitted.
                return $this->rpc_result($id, [
                    'tools' => Api_Mcp_Registry::buildTools($granted),
                ]);

            case 'tools/call':
                $name = isset($params['name']) ? (string)$params['name'] : '';
                $args = isset($params['arguments']) && is_array($params['arguments']) ? $params['arguments'] : [];
                if ($name === '') {
                    return $this->rpc_error($id, -32602, 'Invalid params: tool name is required');
                }
                try {
                    $result = Api_Mcp_Registry::execute($name, $args, $this, $granted);
                    return $this->rpc_result($id, [
                        'content' => [[
                            'type' => 'text',
                            'text' => json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                        ]],
                        'isError' => false,
                    ]);
                } catch (Exception $e) {
                    // Per MCP spec tool failures are results with isError=true,
                    // not protocol errors - agents can read and react to them.
                    return $this->rpc_result($id, [
                        'content' => [[
                            'type' => 'text',
                            'text' => 'Error: ' . $e->getMessage(),
                        ]],
                        'isError' => true,
                    ]);
                }

            default:
                return $isNotification ? null : $this->rpc_error($id, -32601, 'Method not found: ' . $method);
        }
    }

    /**
     * Resolve the token's permission grants.
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

    private function rpc_result($id, $result)
    {
        return ['jsonrpc' => '2.0', 'result' => $result, 'id' => $id];
    }

    private function rpc_error($id, $code, $message)
    {
        return ['jsonrpc' => '2.0', 'error' => ['code' => $code, 'message' => $message], 'id' => $id];
    }
}
