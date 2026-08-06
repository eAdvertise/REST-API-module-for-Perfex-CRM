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
                'title'       => 'Perfex CRM REST API',
                // Sourced from the module version so the spec never drifts.
                'version'     => defined('API_MODULE_VERSION') ? API_MODULE_VERSION : '3.0.3',
                'description' => 'REST API for Perfex CRM by Themesic Interactive. '
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
