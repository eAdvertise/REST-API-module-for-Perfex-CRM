<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/../libraries/Api_Mcp_Registry.php';

/**
 * OpenAPI 3.1 specification endpoint (v3)
 *
 * GET api/openapi.json - machine-readable spec for the whole REST API,
 * generated from the same declarative resource registry that powers the MCP
 * server, so it can never drift from the actual surface. Public (the spec
 * contains no secrets) - import it straight into Postman, Insomnia, Stoplight
 * or any OpenAPI-aware tool.
 */
class Openapi extends App_Controller
{
    /** REST path overrides / exclusions for registry keys */
    private $path_map = [
        'kb_articles'     => 'knowledge_base',
        'kb_groups'       => null, // covered by explicit /knowledge_base/groups paths
        'calendar_events' => 'calendar',
        'notes'           => null, // covered by explicit /notes paths
    ];

    public function index()
    {
        $this->json();
    }

    public function json()
    {
        $spec = $this->build_spec();

        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        echo json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function build_spec()
    {
        $base = function_exists('site_url') ? rtrim(site_url('api'), '/') : '/api';

        $spec = [
            // Swagger UI bundled with the module parses OpenAPI 3.0.x; the document is
            // 3.0-compatible (simple types + oneOf), so we declare 3.0.3.
            'openapi' => '3.0.3',
            'info'    => [
                'title'       => 'eAD-CRM REST API',
                // Sourced from the module version so the spec never drifts.
                'version'     => defined('API_MODULE_VERSION') ? API_MODULE_VERSION : '3.0.3',
                'description' => 'REST API for eAD-CRM by eAdvertise.eu. '
                    . 'Authenticate every request with the authtoken header. '
                    . 'Lists support page/per_page pagination, sort, fields selection and date-range filters. '
                    . 'An MCP server for AI agents is available at POST /api/mcp.',
                'contact' => ['url' => 'https://www.eadvertise.eu'],
            ],
            'servers'  => [['url' => $base]],
            'security' => [['authtoken' => []]],
            'tags'     => [],
            'paths'    => [],
            'components' => [
                'securitySchemes' => [
                    'authtoken' => [
                        'type' => 'apiKey', 'in' => 'header', 'name' => 'authtoken',
                        'description' => 'API token created under Setup > API > API Management',
                    ],
                ],
                'parameters' => [
                    'page'     => ['name' => 'page', 'in' => 'query', 'schema' => ['type' => 'integer', 'minimum' => 1], 'description' => 'Page number; enables the {data, meta} envelope'],
                    'per_page' => ['name' => 'per_page', 'in' => 'query', 'schema' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100], 'description' => 'Rows per page (default 25, max 100)'],
                    'sort'     => ['name' => 'sort', 'in' => 'query', 'schema' => ['type' => 'string'], 'description' => 'Comma list of columns; prefix with - for descending (e.g. -datecreated,company)'],
                    'fields'   => ['name' => 'fields', 'in' => 'query', 'schema' => ['type' => 'string'], 'description' => 'Comma list of columns to return (id always included)'],
                    'created_after'  => ['name' => 'created_after', 'in' => 'query', 'schema' => ['type' => 'string', 'format' => 'date'], 'description' => 'Keep rows created on/after this ISO date'],
                    'created_before' => ['name' => 'created_before', 'in' => 'query', 'schema' => ['type' => 'string', 'format' => 'date'], 'description' => 'Keep rows created on/before this ISO date'],
                ],
                'schemas' => [
                    'Error' => [
                        'type' => 'object',
                        'properties' => [
                            'status'  => ['type' => 'boolean'],
                            'message' => ['type' => 'string'],
                        ],
                    ],
                    'ValidationError' => [
                        'type' => 'object',
                        'properties' => [
                            'status'  => ['type' => 'boolean'],
                            'error'   => ['type' => 'string'],
                            'message' => ['type' => 'string'],
                            'errors'  => ['type' => 'object', 'additionalProperties' => ['type' => 'string']],
                        ],
                    ],
                    'GuestInvoiceItem' => [
                        'type' => 'object',
                        'required' => ['description', 'qty', 'rate'],
                        'properties' => [
                            'description' => ['type' => 'string'],
                            'long_description' => ['type' => 'string'],
                            'qty' => ['type' => 'number'],
                            'rate' => ['type' => 'number'],
                            'unit' => ['type' => 'string'],
                            'taxname' => [
                                'oneOf' => [
                                    ['type' => 'string'],
                                    ['type' => 'array', 'items' => ['type' => 'string']],
                                ],
                                'description' => 'Tax name/rate format accepted by Perfex CRM, for example VAT|24.00',
                            ],
                        ],
                    ],
                    'GuestInvoiceRequest' => [
                        'type' => 'object',
                        'required' => ['email', 'items'],
                        'properties' => [
                            'email' => ['type' => 'string', 'format' => 'email'],
                            'name' => ['type' => 'string'],
                            'company' => ['type' => 'string'],
                            'phone' => ['type' => 'string'],
                            'address' => ['type' => 'string'],
                            'city' => ['type' => 'string'],
                            'state' => ['type' => 'string'],
                            'zip' => ['type' => 'string'],
                            'country' => ['type' => 'integer'],
                            'currency' => ['type' => 'integer'],
                            'items' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/GuestInvoiceItem']],
                            'send_email' => ['type' => 'boolean'],
                            'update_existing_name' => ['type' => 'boolean'],
                        ],
                    ],
                    'GuestCheckoutRequest' => [
                        'allOf' => [
                            ['$ref' => '#/components/schemas/GuestInvoiceRequest'],
                            ['type' => 'object', 'required' => ['payment_mode'], 'properties' => [
                                'payment_mode' => ['type' => 'integer'],
                                'payment_date' => ['type' => 'string', 'format' => 'date'],
                                'transaction_id' => ['type' => 'string'],
                                'payment_note' => ['type' => 'string'],
                                'send_email_mode' => ['type' => 'string', 'enum' => ['auto', 'never', 'always']],
                            ]],
                        ],
                    ],
                    'GuestInvoiceResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'status' => ['type' => 'boolean'],
                            'client_id' => ['type' => 'integer'],
                            'contact_id' => ['type' => 'integer'],
                            'invoice_id' => ['type' => 'integer'],
                            'invoice_number' => ['type' => 'string'],
                            'message' => ['type' => 'string'],
                        ],
                    ],
                    'GuestCheckoutResponse' => [
                        'allOf' => [
                            ['$ref' => '#/components/schemas/GuestInvoiceResponse'],
                            ['type' => 'object', 'properties' => [
                                'payment_id' => ['type' => 'integer'],
                                'email_sent' => ['type' => 'boolean'],
                            ]],
                        ],
                    ],
                    'ListMeta' => [
                        'type' => 'object',
                        'properties' => [
                            'page' => ['type' => 'integer'], 'per_page' => ['type' => 'integer'],
                            'total' => ['type' => 'integer'], 'total_pages' => ['type' => 'integer'],
                            'has_more' => ['type' => 'boolean'],
                            'current_page' => ['type' => 'integer'], 'last_page' => ['type' => 'integer'],
                        ],
                    ],
                ],
            ],
        ];

        foreach (Api_Mcp_Registry::resources() as $key => $cfg) {
            $path = array_key_exists($key, $this->path_map) ? $this->path_map[$key] : $key;
            if ($path === null) {
                continue;
            }
            $this->add_resource_paths($spec, $path, $cfg['label']);
        }

        $this->add_special_paths($spec);

        return $spec;
    }

    private function add_resource_paths(&$spec, $path, $label)
    {
        $tag = ucwords(str_replace('_', ' ', $path));
        $spec['tags'][] = ['name' => $tag];

        $listParams = [];
        foreach (['page', 'per_page', 'sort', 'fields', 'created_after', 'created_before'] as $p) {
            $listParams[] = ['$ref' => '#/components/parameters/' . $p];
        }

        $spec['paths']['/' . $path] = [
            'get' => [
                'tags' => [$tag], 'summary' => 'List ' . $label . 's',
                'parameters' => $listParams,
                'responses' => $this->list_responses($label),
            ],
            'post' => [
                'tags' => [$tag], 'summary' => 'Create a ' . $label,
                'requestBody' => $this->form_body('Record fields; see the API guide for the field list. multipart/form-data or JSON.'),
                'responses' => $this->write_responses(),
            ],
        ];

        $spec['paths']['/' . $path . '/{id}'] = [
            'parameters' => [$this->id_param()],
            'get' => [
                'tags' => [$tag], 'summary' => 'Get a ' . $label . ' by id',
                'responses' => $this->single_responses(),
            ],
            'put' => [
                'tags' => [$tag], 'summary' => 'Update a ' . $label . ' (partial - unknown fields ignored)',
                'requestBody' => $this->form_body('Fields to change'),
                'responses' => $this->write_responses(),
            ],
            'delete' => [
                'tags' => [$tag], 'summary' => 'Delete a ' . $label,
                'responses' => $this->write_responses(),
            ],
        ];

        $spec['paths']['/' . $path . '/search/{keysearch}'] = [
            'get' => [
                'tags' => [$tag], 'summary' => 'Search ' . $label . 's',
                'parameters' => [[
                    'name' => 'keysearch', 'in' => 'path', 'required' => true,
                    'schema' => ['type' => 'string'], 'description' => 'Search keyword',
                ]],
                'responses' => $this->list_responses($label),
            ],
        ];
    }

    private function add_special_paths(&$spec)
    {
        $spec['tags'][] = ['name' => 'Payments On Account'];
        $poaTag = ['Payments On Account'];
        $spec['paths']['/paymentsonaccount'] = [
            'get' => ['tags' => $poaTag, 'summary' => 'Discover PaymentsOnAccount endpoints', 'responses' => $this->single_responses()],
        ];
        $spec['paths']['/paymentsonaccount/receipts'] = [
            'get' => ['tags' => $poaTag, 'summary' => 'List receipts', 'responses' => $this->list_responses('receipt')],
            'post' => ['tags' => $poaTag, 'summary' => 'Create and optionally email a receipt', 'requestBody' => $this->form_body('client_id, amount, payment_mode, payment_date, invoice_ids, on_account, send_email'), 'responses' => $this->write_responses()],
        ];
        $spec['paths']['/paymentsonaccount/receipts/{id}'] = [
            'parameters' => [$this->id_param()],
            'get' => ['tags' => $poaTag, 'summary' => 'Get a receipt and its invoice applications', 'responses' => $this->single_responses()],
            'put' => ['tags' => $poaTag, 'summary' => 'Update receipt fields', 'requestBody' => $this->form_body('amount, payment_date, payment_mode, payment_method, transaction_id, note, receipt_number'), 'responses' => $this->write_responses()],
            'delete' => ['tags' => $poaTag, 'summary' => 'Delete a receipt and its core payments', 'responses' => $this->write_responses()],
        ];
        $spec['paths']['/paymentsonaccount/receipts/{id}/applications'] = [
            'parameters' => [$this->id_param()],
            'get' => ['tags' => $poaTag, 'summary' => 'List receipt invoice applications', 'responses' => $this->list_responses('application')],
            'post' => ['tags' => $poaTag, 'summary' => 'Apply a receipt to invoices', 'requestBody' => $this->form_body('invoice_ids or allocations [{invoice_id, amount}]'), 'responses' => $this->write_responses()],
        ];
        $spec['paths']['/paymentsonaccount/receipts/{id}/applications/{payment_id}'] = [
            'parameters' => [$this->id_param(), ['name' => 'payment_id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]],
            'delete' => ['tags' => $poaTag, 'summary' => 'Remove an applied core payment', 'responses' => $this->write_responses()],
        ];
        foreach (['email' => 'Send receipt email', 'pdf' => 'Get receipt PDF as base64'] as $action => $summary) {
            $method = $action === 'email' ? 'post' : 'get';
            $spec['paths']['/paymentsonaccount/receipts/{id}/' . $action] = [
                'parameters' => [$this->id_param()],
                $method => ['tags' => $poaTag, 'summary' => $summary, 'responses' => $method === 'get' ? $this->single_responses() : $this->write_responses()],
            ];
        }
        $spec['paths']['/paymentsonaccount/clients/{id}/unpaid-invoices'] = [
            'parameters' => [$this->id_param()],
            'get' => ['tags' => $poaTag, 'summary' => 'List client unpaid invoices', 'responses' => $this->list_responses('invoice')],
        ];
        $spec['paths']['/paymentsonaccount/clients/{id}/payment-modes'] = [
            'parameters' => [$this->id_param()],
            'get' => ['tags' => $poaTag, 'summary' => 'Get client payment modes', 'responses' => $this->single_responses()],
            'put' => ['tags' => $poaTag, 'summary' => 'Replace client payment modes', 'requestBody' => $this->form_body('payment_mode_ids'), 'responses' => $this->write_responses()],
        ];
        $spec['paths']['/paymentsonaccount/clients/{id}/statement'] = [
            'parameters' => [$this->id_param()],
            'get' => ['tags' => $poaTag, 'summary' => 'Get receipt-based client statement', 'responses' => $this->single_responses()],
        ];
        $spec['paths']['/paymentsonaccount/reports/receipts'] = [
            'get' => ['tags' => $poaTag, 'summary' => 'Get paginated receipts report', 'responses' => $this->list_responses('receipt')],
        ];
        $spec['paths']['/paymentsonaccount/reports/credits'] = [
            'get' => ['tags' => $poaTag, 'summary' => 'Get credits report', 'responses' => $this->list_responses('credit note')],
        ];

        // Warehouse module resources. Inventory balances are deliberately
        // read-only; stock changes go through a domain document endpoint.
        $spec['tags'][] = ['name' => 'Warehouse'];
        foreach ([
            'warehouses', 'items', 'receipts', 'deliveries', 'transfers', 'adjustments', 'commodity_types', 'commodity_groups',
            'sub_groups', 'units', 'sizes', 'styles', 'bodies', 'colors', 'brands', 'models',
            'series', 'inventory_minimums', 'serial_numbers', 'stock_takes', 'packing_lists', 'order_returns', 'approval_settings', 'approval_details',
            'warehouse_custom_fields', 'staff_warehouses', 'activity_logs', 'delivery_activity_logs', 'transaction_details', 'packing_list_details', 'stock_take_details', 'return_details',
            'receipt_details', 'delivery_details', 'adjustment_details', 'delivery_order_links', 'item_relations', 'omni_shipments',
        ] as $resource) {
            $spec['paths']['/warehouse/' . $resource] = [
                'get' => ['tags' => ['Warehouse'], 'summary' => 'List warehouse ' . $resource, 'responses' => $this->list_responses($resource)],
                'post' => ['tags' => ['Warehouse'], 'summary' => 'Create warehouse ' . rtrim($resource, 's'), 'requestBody' => $this->form_body('Warehouse module payload; operational documents accept newitems.'), 'responses' => $this->write_responses()],
            ];
            $spec['paths']['/warehouse/' . $resource . '/{id}'] = [
                'parameters' => [$this->id_param()],
                'get' => ['tags' => ['Warehouse'], 'summary' => 'Get warehouse ' . rtrim($resource, 's'), 'responses' => $this->single_responses()],
                'put' => ['tags' => ['Warehouse'], 'summary' => 'Update warehouse ' . rtrim($resource, 's'), 'requestBody' => $this->form_body('Fields to change'), 'responses' => $this->write_responses()],
                'delete' => ['tags' => ['Warehouse'], 'summary' => 'Delete warehouse ' . rtrim($resource, 's'), 'responses' => $this->write_responses()],
            ];
        }
        $spec['paths']['/warehouse/inventory'] = [
            'get' => ['tags' => ['Warehouse'], 'summary' => 'List read-only inventory balances', 'responses' => $this->list_responses('inventory balance')],
        ];
        $spec['paths']['/warehouse/inventory/{id}'] = [
            'parameters' => [$this->id_param()],
            'get' => ['tags' => ['Warehouse'], 'summary' => 'Get a read-only inventory balance', 'responses' => $this->single_responses()],
        ];

        // Knowledge base groups
        $spec['tags'][] = ['name' => 'Knowledge Base Groups'];
        $spec['paths']['/knowledge_base/groups'] = [
            'get'  => ['tags' => ['Knowledge Base Groups'], 'summary' => 'List groups', 'responses' => $this->list_responses('group')],
            'post' => ['tags' => ['Knowledge Base Groups'], 'summary' => 'Create a group', 'requestBody' => $this->form_body('name, description, active, color, group_order'), 'responses' => $this->write_responses()],
        ];
        $spec['paths']['/knowledge_base/groups/{id}'] = [
            'parameters' => [$this->id_param()],
            'put'    => ['tags' => ['Knowledge Base Groups'], 'summary' => 'Update a group', 'requestBody' => $this->form_body('Fields to change'), 'responses' => $this->write_responses()],
            'delete' => ['tags' => ['Knowledge Base Groups'], 'summary' => 'Delete a group (409 when articles still attached)', 'responses' => $this->write_responses()],
        ];

        // Notes (polymorphic)
        $spec['tags'][] = ['name' => 'Notes'];
        $spec['paths']['/notes'] = [
            'post' => ['tags' => ['Notes'], 'summary' => 'Create a note', 'requestBody' => $this->form_body('rel_type (customer|lead|contract|ticket|invoice|estimate|credit_note|staff|expense|proposal|project|task), rel_id, description'), 'responses' => $this->write_responses()],
        ];
        $spec['paths']['/notes/{id}'] = [
            'parameters' => [$this->id_param()],
            'get'    => ['tags' => ['Notes'], 'summary' => 'Get a note', 'responses' => $this->single_responses()],
            'put'    => ['tags' => ['Notes'], 'summary' => 'Update a note description', 'requestBody' => $this->form_body('description'), 'responses' => $this->write_responses()],
            'delete' => ['tags' => ['Notes'], 'summary' => 'Delete a note', 'responses' => $this->write_responses()],
        ];
        $spec['paths']['/notes/{rel_type}/{rel_id}'] = [
            'get' => [
                'tags' => ['Notes'], 'summary' => 'List notes attached to an entity',
                'parameters' => [
                    ['name' => 'rel_type', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                    ['name' => 'rel_id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                ],
                'responses' => $this->list_responses('note'),
            ],
        ];

        // Guest invoices / checkout
        $spec['tags'][] = ['name' => 'Guest Invoices'];
        $spec['paths']['/guest_invoices'] = [
            'post' => [
                'tags' => ['Guest Invoices'],
                'summary' => 'Create or reuse a guest and create an invoice',
                'requestBody' => $this->json_body('#/components/schemas/GuestInvoiceRequest'),
                'responses' => $this->guest_invoice_responses('#/components/schemas/GuestInvoiceResponse'),
            ],
        ];
        $spec['paths']['/guest_invoices/checkout'] = [
            'post' => [
                'tags' => ['Guest Invoices'],
                'summary' => 'Create or reuse a guest, create an invoice, record payment and optionally email invoice/receipt PDFs',
                'requestBody' => $this->json_body('#/components/schemas/GuestCheckoutRequest'),
                'responses' => $this->guest_invoice_responses('#/components/schemas/GuestCheckoutResponse'),
            ],
        ];
        $spec['paths']['/guestinvoices/checkout'] = [
            'post' => [
                'tags' => ['Guest Invoices'],
                'summary' => 'Legacy checkout alias for /guest_invoices/checkout',
                'requestBody' => $this->json_body('#/components/schemas/GuestCheckoutRequest'),
                'responses' => $this->guest_invoice_responses('#/components/schemas/GuestCheckoutResponse'),
            ],
        ];

        // Webhook management extras
        $spec['tags'][] = ['name' => 'Webhooks'];
        $spec['paths']['/webhooks/events'] = [
            'get' => ['tags' => ['Webhooks'], 'summary' => 'Webhook event catalog (124 events in 22 groups)', 'responses' => $this->single_responses()],
        ];
        $spec['paths']['/webhooks/{id}/toggle'] = [
            'parameters' => [$this->id_param()],
            'post' => ['tags' => ['Webhooks'], 'summary' => 'Enable/disable a webhook', 'responses' => $this->write_responses()],
        ];
        $spec['paths']['/webhooks/{id}/logs'] = [
            'parameters' => [$this->id_param()],
            'get' => ['tags' => ['Webhooks'], 'summary' => 'Delivery logs (latest 500, paginated)', 'responses' => $this->list_responses('log')],
        ];

        // MCP
        $spec['tags'][] = ['name' => 'MCP'];
        $spec['paths']['/mcp'] = [
            'post' => [
                'tags' => ['MCP'],
                'summary' => 'MCP Server (JSON-RPC 2.0): initialize, ping, tools/list, tools/call - 148 permission-filtered CRM tools for AI agents',
                'requestBody' => [
                    'required' => true,
                    'content' => ['application/json' => ['schema' => ['type' => 'object']]],
                ],
                'responses' => ['200' => ['description' => 'JSON-RPC response']],
            ],
        ];
    }

    // ------------------------------------------------------------------

    private function id_param()
    {
        return ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']];
    }

    private function json_body($schemaRef)
    {
        return [
            'required' => true,
            'content' => [
                'application/json' => ['schema' => ['$ref' => $schemaRef]],
                'multipart/form-data' => ['schema' => ['$ref' => $schemaRef]],
            ],
        ];
    }

    private function guest_invoice_responses($schemaRef)
    {
        return [
            '201' => ['description' => 'Created', 'content' => ['application/json' => ['schema' => ['$ref' => $schemaRef]]]],
            '400' => ['description' => 'Invalid request', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/Error']]]],
            '422' => ['description' => 'Validation failed', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ValidationError']]]],
        ];
    }

    private function form_body($description)
    {
        $schema = ['type' => 'object', 'description' => $description];
        return [
            'required' => true,
            'content'  => [
                'multipart/form-data' => ['schema' => $schema],
                'application/json'    => ['schema' => $schema],
            ],
        ];
    }

    private function list_responses($label)
    {
        return [
            '200' => [
                'description' => 'Plain array (legacy) or {data, meta} envelope when pagination params are sent',
                'content' => ['application/json' => ['schema' => [
                    'oneOf' => [
                        ['type' => 'array', 'items' => ['type' => 'object']],
                        ['type' => 'object', 'properties' => [
                            'data' => ['type' => 'array', 'items' => ['type' => 'object']],
                            'meta' => ['$ref' => '#/components/schemas/ListMeta'],
                        ]],
                    ],
                ]]],
            ],
            '404' => ['description' => 'No data found', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/Error']]]],
            '422' => ['description' => 'Invalid filters', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ValidationError']]]],
        ];
    }

    private function single_responses()
    {
        return [
            '200' => ['description' => 'The record', 'content' => ['application/json' => ['schema' => ['type' => 'object']]]],
            '404' => ['description' => 'Not found', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/Error']]]],
        ];
    }

    private function write_responses()
    {
        return [
            '200' => ['description' => 'Success (record_id included on create)', 'content' => ['application/json' => ['schema' => ['type' => 'object']]]],
            '404' => ['description' => 'Not found', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/Error']]]],
            '422' => ['description' => 'Validation failed', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ValidationError']]]],
        ];
    }
}
