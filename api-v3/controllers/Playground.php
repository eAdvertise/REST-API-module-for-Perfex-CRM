<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * API Playground Controller
 * SECURITY: Requires admin authentication to prevent unauthorized access
 * This controller provides interactive API testing tools for administrators only
 */
class Playground extends AdminController
{    
    public function __construct()
    {
        parent::__construct();
        
        // SECURITY CHECK: Require admin authentication
        if (!is_admin()) {
            access_denied('API Playground');
        }
        
        // Only load essential helpers and libraries
        $this->load->helper('url');
        $this->load->helper('string');
        
        // Override any problematic properties that might be accessed by helpers
        $this->load->library('app_modules');
        
        // Verify API module is active
        if (!$this->app_modules->is_active('api')) {
            access_denied('API Module');
        }
    }
    
    /**
     * Playground index page (admin only)
     */
    public function index()
    {
        $data['title'] = 'API Playground - Test eAD-CRM API';
        $data['base_url'] = base_url();
        $data['api_base_url'] = base_url('api/');
        
        // Load the sandbox view instead of swagger
        $this->load->view('playground/swagger', $data);
    }
    
    /**
     * Sandbox playground page
     */
    public function sandbox()
    {
        $data['title'] = 'API Sandbox Playground - Test eAD-CRM API';
        $data['base_url'] = base_url();
        $data['api_base_url'] = base_url('api/');
        
        $this->load->view('playground/sandbox', $data);
    }
    
    /**
     * Execute API request (admin authentication required)
     * SECURITY: This method acts as a server-side proxy and must be protected
     */
    public function execute_request()
    {
        $method = $this->input->post('method');
        $endpoint = $this->input->post('endpoint');
        $headers = $this->input->post('headers');
        $data = $this->input->post('data');
        
        // Validate inputs
        if (empty($method) || empty($endpoint)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Method and endpoint are required'
                ]));
            return;
        }
        
        // Prepare headers
        $request_headers = [];
        if (!empty($headers)) {
            $header_lines = explode("\n", $headers);
            foreach ($header_lines as $line) {
                $line = trim($line ?? '');
                if (strpos($line, ':') !== false) {
                    list($key, $value) = explode(':', $line, 2);
                    $request_headers[trim($key ?? '')] = trim($value ?? '');
                }
            }
        }
        
        // Add default headers
        $request_headers['Content-Type'] = 'application/json';
        $request_headers['Accept'] = 'application/json';
        
        // Prepare request data
        $request_data = null;
        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $request_data = $data;
        }

        // Make the API request
        $response = $this->make_api_request($method, $endpoint, $request_headers, $request_data);
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
    
    /**
     * Get sample requests
     */
    public function get_samples()
    {
        // Load comprehensive samples from config file
        $samples = include(dirname(__DIR__) . '/config/api_samples.php');
        $moduleSamples = include(dirname(__DIR__) . '/config/playground_module_samples.php');
        $samples = array_merge($samples, $moduleSamples);
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($samples));
    }
    
    /**
     * Make API request using cURL
     */
    private function make_api_request($method, $endpoint, $headers = [], $data = null)
    {
        $url = base_url('api/' . ltrim($endpoint, '/'));
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->format_headers($headers));
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'message' => 'cURL Error: ' . $error,
                'http_code' => 0
            ];
        }
        
        return [
            'success' => true,
            'response' => $response,
            'http_code' => $http_code,
            'url' => $url
        ];
    }
    
    /**
     * Format headers for cURL
     */
    private function format_headers($headers)
    {
        $formatted = [];
        foreach ($headers as $key => $value) {
            $formatted[] = $key . ': ' . $value;
        }
        return $formatted;
    }

    /**
     * Get API documentation
     */
    public function documentation()
    {
        $data['title'] = 'API Documentation';
        $this->load->view('playground/documentation', $data);
    }

    /**
     * Get Swagger file
     */
    public function swagger() {
        // v3: the static config/swagger.json (frozen at 2.1.4) is superseded
        // by the dynamic OpenAPI 3.1 spec, which always matches the real API.
        redirect(site_url('api/openapi.json'));
    }
    
    /**
     * Get environment configuration
     */
    public function get_environment_config()
    {
        $config = [
            'sandbox' => [
                'name' => 'Sandbox Environment',
                'description' => 'Safe testing environment - no production data affected',
                'base_url' => base_url('api/'),
                'features' => [
                    'Safe testing',
                    'No production data impact',
                    'Request logging',
                    'Sample data available'
                ]
            ],
            'production' => [
                'name' => 'Production Environment',
                'description' => 'Live production environment - USE WITH EXTREME CAUTION!',
                'base_url' => base_url('api/'),
                'features' => [
                    'Live data access',
                    'Real-time operations',
                    'Production impact',
                    'Requires authentication'
                ],
                'warning' => 'This will affect live production data!'
            ]
        ];
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($config));
    }
    
    /**
     * Get available endpoints by category
     */
    public function get_endpoints()
    {
        $endpoints = [
            'leads' => [
                'name' => 'Leads',
                'description' => 'Manage sales leads and prospects',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/leads', 'description' => 'Get all leads'],
                    ['method' => 'POST', 'path' => '/leads', 'description' => 'Create new lead'],
                    ['method' => 'GET', 'path' => '/leads/{id}', 'description' => 'Get specific lead'],
                    ['method' => 'PUT', 'path' => '/leads/{id}', 'description' => 'Update lead'],
                    ['method' => 'DELETE', 'path' => '/leads/{id}', 'description' => 'Delete lead'],
                    ['method' => 'GET', 'path' => '/leads/search/{keyword}', 'description' => 'Search leads']
                ]
            ],
            'projects' => [
                'name' => 'Projects',
                'description' => 'Manage projects and project-related data',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/projects', 'description' => 'Get all projects'],
                    ['method' => 'POST', 'path' => '/projects', 'description' => 'Create new project'],
                    ['method' => 'GET', 'path' => '/projects/{id}', 'description' => 'Get specific project'],
                    ['method' => 'PUT', 'path' => '/projects/{id}', 'description' => 'Update project'],
                    ['method' => 'DELETE', 'path' => '/projects/{id}', 'description' => 'Delete project']
                ]
            ],
            'tasks' => [
                'name' => 'Tasks',
                'description' => 'Manage project tasks and assignments',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/tasks', 'description' => 'Get all tasks'],
                    ['method' => 'POST', 'path' => '/tasks', 'description' => 'Create new task'],
                    ['method' => 'GET', 'path' => '/tasks/{id}', 'description' => 'Get specific task'],
                    ['method' => 'PUT', 'path' => '/tasks/{id}', 'description' => 'Update task'],
                    ['method' => 'DELETE', 'path' => '/tasks/{id}', 'description' => 'Delete task']
                ]
            ],
            'tickets' => [
                'name' => 'Support Tickets',
                'description' => 'Manage customer support tickets',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/tickets', 'description' => 'Get all tickets'],
                    ['method' => 'POST', 'path' => '/tickets', 'description' => 'Create new ticket'],
                    ['method' => 'GET', 'path' => '/tickets/{id}', 'description' => 'Get specific ticket'],
                    ['method' => 'PUT', 'path' => '/tickets/{id}', 'description' => 'Update ticket'],
                    ['method' => 'DELETE', 'path' => '/tickets/{id}', 'description' => 'Delete ticket']
                ]
            ],
            'invoices' => [
                'name' => 'Invoices',
                'description' => 'Manage billing and invoicing',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/invoices', 'description' => 'Get all invoices'],
                    ['method' => 'POST', 'path' => '/invoices', 'description' => 'Create new invoice'],
                    ['method' => 'GET', 'path' => '/invoices/{id}', 'description' => 'Get specific invoice'],
                    ['method' => 'PUT', 'path' => '/invoices/{id}', 'description' => 'Update invoice'],
                    ['method' => 'DELETE', 'path' => '/invoices/{id}', 'description' => 'Delete invoice'],
                    ['method' => 'GET', 'path' => '/invoices/search/{keyword}', 'description' => 'Search invoices']
                ]
            ],
            'estimates' => [
                'name' => 'Estimates',
                'description' => 'Manage project estimates and quotes',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/estimates', 'description' => 'Get all estimates'],
                    ['method' => 'POST', 'path' => '/estimates', 'description' => 'Create new estimate'],
                    ['method' => 'GET', 'path' => '/estimates/{id}', 'description' => 'Get specific estimate'],
                    ['method' => 'PUT', 'path' => '/estimates/{id}', 'description' => 'Update estimate'],
                    ['method' => 'DELETE', 'path' => '/estimates/{id}', 'description' => 'Delete estimate'],
                    ['method' => 'GET', 'path' => '/estimates/search/{keyword}', 'description' => 'Search estimates']
                ]
            ],
            'contracts' => [
                'name' => 'Contracts',
                'description' => 'Manage client contracts and agreements',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/contracts', 'description' => 'Get all contracts'],
                    ['method' => 'POST', 'path' => '/contracts', 'description' => 'Create new contract'],
                    ['method' => 'GET', 'path' => '/contracts/{id}', 'description' => 'Get specific contract'],
                    ['method' => 'PUT', 'path' => '/contracts/{id}', 'description' => 'Update contract'],
                    ['method' => 'DELETE', 'path' => '/contracts/{id}', 'description' => 'Delete contract']
                ]
            ],
            'credit_notes' => [
                'name' => 'Credit Notes',
                'description' => 'Manage credit notes and refunds',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/credit_notes', 'description' => 'Get all credit notes'],
                    ['method' => 'POST', 'path' => '/credit_notes', 'description' => 'Create new credit note'],
                    ['method' => 'GET', 'path' => '/credit_notes/{id}', 'description' => 'Get specific credit note'],
                    ['method' => 'PUT', 'path' => '/credit_notes/{id}', 'description' => 'Update credit note'],
                    ['method' => 'DELETE', 'path' => '/credit_notes/{id}', 'description' => 'Delete credit note'],
                    ['method' => 'GET', 'path' => '/credit_notes/search/{keyword}', 'description' => 'Search credit notes']
                ]
            ],
            'expenses' => [
                'name' => 'Expenses',
                'description' => 'Manage business expenses and reimbursements',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/expenses', 'description' => 'Get all expenses'],
                    ['method' => 'POST', 'path' => '/expenses', 'description' => 'Create new expense'],
                    ['method' => 'GET', 'path' => '/expenses/{id}', 'description' => 'Get specific expense'],
                    ['method' => 'PUT', 'path' => '/expenses/{id}', 'description' => 'Update expense'],
                    ['method' => 'DELETE', 'path' => '/expenses/{id}', 'description' => 'Delete expense'],
                    ['method' => 'GET', 'path' => '/expenses/search/{keyword}', 'description' => 'Search expenses']
                ]
            ],
            'items' => [
                'name' => 'Items',
                'description' => 'Manage invoice items and products',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/items', 'description' => 'Get all items'],
                    ['method' => 'GET', 'path' => '/items/{id}', 'description' => 'Get specific item'],
                    ['method' => 'GET', 'path' => '/items/search/{keyword}', 'description' => 'Search items']
                ]
            ],
            'contacts' => [
                'name' => 'Contacts',
                'description' => 'Manage client contacts and relationships',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/contacts', 'description' => 'Get all contacts'],
                    ['method' => 'POST', 'path' => '/contacts', 'description' => 'Create new contact'],
                    ['method' => 'GET', 'path' => '/contacts/{customer_id}/{contact_id}', 'description' => 'Get specific contact'],
                    ['method' => 'PUT', 'path' => '/contacts/{customer_id}/{contact_id}', 'description' => 'Update contact'],
                    ['method' => 'DELETE', 'path' => '/contacts/{customer_id}', 'description' => 'Delete contact'],
                    ['method' => 'GET', 'path' => '/contacts/search/{keyword}', 'description' => 'Search contacts']
                ]
            ],
            'staff' => [
                'name' => 'Staff',
                'description' => 'Manage staff members and team information',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/staff', 'description' => 'Get all staff members'],
                    ['method' => 'GET', 'path' => '/staff/{id}', 'description' => 'Get specific staff member']
                ]
            ],
            'payments' => [
                'name' => 'Payments',
                'description' => 'Manage invoice payments and transactions',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/payments', 'description' => 'Get all payments'],
                    ['method' => 'POST', 'path' => '/payments', 'description' => 'Create new payment'],
                    ['method' => 'GET', 'path' => '/payments/{id}', 'description' => 'Get specific payment'],
                    ['method' => 'PUT', 'path' => '/payments/{id}', 'description' => 'Update payment'],
                    ['method' => 'DELETE', 'path' => '/payments/{id}', 'description' => 'Delete payment']
                ]
            ],
            'proposals' => [
                'name' => 'Proposals',
                'description' => 'Manage project proposals and quotes',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/proposals', 'description' => 'Get all proposals'],
                    ['method' => 'POST', 'path' => '/proposals', 'description' => 'Create new proposal'],
                    ['method' => 'GET', 'path' => '/proposals/{id}', 'description' => 'Get specific proposal'],
                    ['method' => 'PUT', 'path' => '/proposals/{id}', 'description' => 'Update proposal'],
                    ['method' => 'DELETE', 'path' => '/proposals/{id}', 'description' => 'Delete proposal']
                ]
            ],
            'subscriptions' => [
                'name' => 'Subscriptions',
                'description' => 'Manage recurring subscriptions and billing',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/subscriptions', 'description' => 'Get all subscriptions'],
                    ['method' => 'POST', 'path' => '/subscriptions', 'description' => 'Create new subscription'],
                    ['method' => 'GET', 'path' => '/subscriptions/{id}', 'description' => 'Get specific subscription'],
                    ['method' => 'PUT', 'path' => '/subscriptions/{id}', 'description' => 'Update subscription'],
                    ['method' => 'DELETE', 'path' => '/subscriptions/{id}', 'description' => 'Delete subscription']
                ]
            ],
            'milestones' => [
                'name' => 'Milestones',
                'description' => 'Manage project milestones and deliverables',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/milestones', 'description' => 'Get all milestones'],
                    ['method' => 'POST', 'path' => '/milestones', 'description' => 'Create new milestone'],
                    ['method' => 'GET', 'path' => '/milestones/{id}', 'description' => 'Get specific milestone'],
                    ['method' => 'PUT', 'path' => '/milestones/{id}', 'description' => 'Update milestone'],
                    ['method' => 'DELETE', 'path' => '/milestones/{id}', 'description' => 'Delete milestone'],
                    ['method' => 'GET', 'path' => '/milestones/search/{keyword}', 'description' => 'Search milestones']
                ]
            ],
            'timesheets' => [
                'name' => 'Timesheets',
                'description' => 'Manage time tracking and work logs',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/timesheets', 'description' => 'Get all timesheets'],
                    ['method' => 'POST', 'path' => '/timesheets', 'description' => 'Create new timesheet entry'],
                    ['method' => 'GET', 'path' => '/timesheets/{id}', 'description' => 'Get specific timesheet'],
                    ['method' => 'PUT', 'path' => '/timesheets/{id}', 'description' => 'Update timesheet'],
                    ['method' => 'DELETE', 'path' => '/timesheets/{id}', 'description' => 'Delete timesheet']
                ]
            ],
            'calendar' => [
                'name' => 'Calendar',
                'description' => 'Manage calendar events and scheduling',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/calendar', 'description' => 'Get all calendar events'],
                    ['method' => 'POST', 'path' => '/calendar', 'description' => 'Create new calendar event'],
                    ['method' => 'GET', 'path' => '/calendar/{id}', 'description' => 'Get specific calendar event'],
                    ['method' => 'PUT', 'path' => '/calendar/{id}', 'description' => 'Update calendar event'],
                    ['method' => 'DELETE', 'path' => '/calendar/{id}', 'description' => 'Delete calendar event']
                ]
            ],
            'common' => [
                'name' => 'Common Data',
                'description' => 'Access common system data and configurations',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/common/expense_category', 'description' => 'Get expense categories'],
                    ['method' => 'GET', 'path' => '/common/payment_mode', 'description' => 'Get payment modes'],
                    ['method' => 'GET', 'path' => '/common/tax_data', 'description' => 'Get tax data']
                ]
            ],
            'custom_fields' => [
                'name' => 'Custom Fields',
                'description' => 'Manage custom fields for different modules',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/custom_fields/{type}', 'description' => 'Get custom fields by type'],
                    ['method' => 'GET', 'path' => '/custom_fields/{type}/{id}', 'description' => 'Get specific custom field']
                ]
            ],
            'authentication' => [
                'name' => 'Authentication',
                'description' => 'User authentication and API key management',
                'endpoints' => [
                    ['method' => 'POST', 'path' => '/login/auth', 'description' => 'Authenticate user'],
                    ['method' => 'GET', 'path' => '/login/key', 'description' => 'Get API key information']
                ]
            ],
            'webhooks' => [
                'name' => 'Webhooks (v3)',
                'description' => 'Manage webhooks via the API - 124 events across 22 resource groups',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/webhooks', 'description' => 'List webhooks'],
                    ['method' => 'POST', 'path' => '/webhooks', 'description' => 'Create webhook'],
                    ['method' => 'GET', 'path' => '/webhooks/{id}', 'description' => 'Get webhook'],
                    ['method' => 'PUT', 'path' => '/webhooks/{id}', 'description' => 'Update webhook'],
                    ['method' => 'DELETE', 'path' => '/webhooks/{id}', 'description' => 'Delete webhook'],
                    ['method' => 'POST', 'path' => '/webhooks/{id}/toggle', 'description' => 'Enable/disable webhook'],
                    ['method' => 'GET', 'path' => '/webhooks/events', 'description' => 'Event catalog (124 events)'],
                    ['method' => 'GET', 'path' => '/webhooks/{id}/logs', 'description' => 'Delivery logs']
                ]
            ],
            'knowledge_base' => [
                'name' => 'Knowledge Base (v3)',
                'description' => 'Manage knowledge base articles and groups',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/knowledge_base', 'description' => 'List articles (?group_id= filter)'],
                    ['method' => 'POST', 'path' => '/knowledge_base', 'description' => 'Create article'],
                    ['method' => 'GET', 'path' => '/knowledge_base/{id}', 'description' => 'Get article'],
                    ['method' => 'PUT', 'path' => '/knowledge_base/{id}', 'description' => 'Update article'],
                    ['method' => 'DELETE', 'path' => '/knowledge_base/{id}', 'description' => 'Delete article'],
                    ['method' => 'GET', 'path' => '/knowledge_base/groups', 'description' => 'List groups'],
                    ['method' => 'POST', 'path' => '/knowledge_base/groups', 'description' => 'Create group']
                ]
            ],
            'notes' => [
                'name' => 'Notes (v3)',
                'description' => 'Polymorphic notes across 12 entity types',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/notes/{rel_type}/{rel_id}', 'description' => 'List notes of an entity'],
                    ['method' => 'POST', 'path' => '/notes', 'description' => 'Create note'],
                    ['method' => 'GET', 'path' => '/notes/{id}', 'description' => 'Get note'],
                    ['method' => 'PUT', 'path' => '/notes/{id}', 'description' => 'Update note'],
                    ['method' => 'DELETE', 'path' => '/notes/{id}', 'description' => 'Delete note']
                ]
            ],
            'platform' => [
                'name' => 'Platform (v3)',
                'description' => 'MCP server for AI agents, batch operations and the OpenAPI specification',
                'endpoints' => [
                    ['method' => 'POST', 'path' => '/mcp', 'description' => 'MCP Server - 148 tools for AI agents (JSON-RPC 2.0)'],
                    ['method' => 'POST', 'path' => '/batch', 'description' => 'Batch operations (up to 50 per request)'],
                    ['method' => 'GET', 'path' => '/openapi.json', 'description' => 'OpenAPI 3.1 specification']
                ]
            ]
        ];

        // Optional-module endpoints live in one declarative catalog shared by
        // the playground UI. This keeps the interactive tester aligned with
        // the explicit API v3 routes without making this controller larger.
        $moduleEndpoints = include(dirname(__DIR__) . '/config/playground_module_endpoints.php');
        $endpoints = array_merge($endpoints, $moduleEndpoints);
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($endpoints));
    }
}
