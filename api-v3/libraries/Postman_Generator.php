<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Postman Collection Generator
 * 
 * Generates Postman v2.1 collection format from API endpoints
 */
class Postman_Generator
{
    private $CI;
    private $baseUrl;
    private $apiKey;
    
    public function __construct($baseUrl = null, $apiKey = null)
    {
        // Don't try to get CI instance - work standalone
        $this->CI = null;
        
        $this->baseUrl = $baseUrl ?: '/api/';
        $this->apiKey = $apiKey;
    }
    
    /**
     * Generate Postman collection
     * 
     * @return array Postman collection v2.1 format
     */
    public function generateCollection()
    {
        $endpoints = $this->discoverEndpoints();
        
        $collection = [
            'info' => [
                'name' => 'eAD-CRM API',
                'description' => 'Complete REST API collection for eAD-CRM',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
                '_postman_id' => uniqid(),
                'version' => [
                    'major' => 1,
                    'minor' => 0,
                    'patch' => 0
                ]
            ],
            'auth' => [
                'type' => 'bearer',
                'bearer' => [
                    [
                        'key' => 'token',
                        'value' => '{{api_token}}',
                        'type' => 'string'
                    ]
                ]
            ],
            'variable' => [
                [
                    'key' => 'base_url',
                    'value' => rtrim($this->baseUrl, '/'),
                    'type' => 'string'
                ],
                [
                    'key' => 'api_token',
                    'value' => $this->apiKey ?: 'YOUR_API_TOKEN_HERE',
                    'type' => 'string'
                ]
            ],
            'item' => $this->organizeEndpoints($endpoints)
        ];
        
        return $collection;
    }
    
    /**
     * Discover all API endpoints
     * 
     * @return array
     */
    private function discoverEndpoints()
    {
        // Skip Playground controller instantiation to avoid Session issues
        // Always use default endpoints which work without CodeIgniter
        return $this->generateDefaultEndpoints();
    }
    
    /**
     * Generate default endpoints if discovery fails
     * 
     * @return array
     */
    private function generateDefaultEndpoints()
    {
        $resources = [
            'customers', 'contacts', 'invoices', 'estimates', 'projects',
            'tasks', 'leads', 'tickets', 'staffs', 'payments', 'expenses',
            'proposals', 'contracts', 'credit_notes', 'subscriptions', 'timesheets',
            'milestones', 'items', 'calendar', 'knowledge_base', 'notes', 'webhooks'
        ];
        
        $endpoints = [];
        
        foreach ($resources as $resource) {
            // GET all
            $endpoints[] = [
                'category' => $resource,
                'name' => ucfirst($resource),
                'method' => 'GET',
                'path' => '/' . $resource,
                'description' => 'Get all ' . $resource
            ];
            
            // GET by ID
            $endpoints[] = [
                'category' => $resource,
                'name' => ucfirst($resource),
                'method' => 'GET',
                'path' => '/' . $resource . '/{id}',
                'description' => 'Get ' . $resource . ' by ID'
            ];
            
            // POST create
            $endpoints[] = [
                'category' => $resource,
                'name' => ucfirst($resource),
                'method' => 'POST',
                'path' => '/' . $resource,
                'description' => 'Create new ' . $resource
            ];
            
            // PUT update
            $endpoints[] = [
                'category' => $resource,
                'name' => ucfirst($resource),
                'method' => 'PUT',
                'path' => '/' . $resource . '/{id}',
                'description' => 'Update ' . $resource
            ];
            
            // DELETE
            $endpoints[] = [
                'category' => $resource,
                'name' => ucfirst($resource),
                'method' => 'DELETE',
                'path' => '/' . $resource . '/{id}',
                'description' => 'Delete ' . $resource
            ];
            
            // Search
            $endpoints[] = [
                'category' => $resource,
                'name' => ucfirst($resource),
                'method' => 'GET',
                'path' => '/' . $resource . '/search/{keyword}',
                'description' => 'Search ' . $resource
            ];
        }
        
        // Add authentication endpoints
        $endpoints[] = [
            'category' => 'authentication',
            'name' => 'Authentication',
            'method' => 'POST',
            'path' => '/login/auth',
            'description' => 'Authenticate user and get token'
        ];
        
        return $endpoints;
    }
    
    /**
     * Organize endpoints into Postman folder structure
     * 
     * @param array $endpoints
     * @return array
     */
    private function organizeEndpoints($endpoints)
    {
        $folders = [];
        
        foreach ($endpoints as $endpoint) {
            $category = $endpoint['category'];
            
            if (!isset($folders[$category])) {
                $folders[$category] = [
                    'name' => ucfirst(str_replace('_', ' ', $category)),
                    'item' => []
                ];
            }
            
            $request = $this->createPostmanRequest($endpoint);
            $folders[$category]['item'][] = $request;
        }
        
        return array_values($folders);
    }
    
    /**
     * Create Postman request item
     * 
     * @param array $endpoint
     * @return array
     */
    private function createPostmanRequest($endpoint)
    {
        $path = $endpoint['path'];
        $method = strtoupper($endpoint['method']);
        
        // Extract path variables
        $pathVariables = [];
        if (preg_match_all('/\{(\w+)\}/', $path, $matches)) {
            foreach ($matches[1] as $var) {
                $pathVariables[] = [
                    'key' => $var,
                    'value' => '',
                    'description' => ucfirst($var) . ' parameter'
                ];
            }
        }
        
        // Build URL
        $url = [
            'raw' => '{{base_url}}' . $path,
            'host' => ['{{base_url}}'],
            'path' => array_filter(explode('/', trim($path, '/')))
        ];
        
        // Add query parameters for GET requests
        $query = [];
        if ($method === 'GET' && strpos($path, '{id}') === false && strpos($path, 'search') === false) {
            $query[] = [
                'key' => 'page',
                'value' => '1',
                'description' => 'Page number'
            ];
            $query[] = [
                'key' => 'limit',
                'value' => '20',
                'description' => 'Items per page'
            ];
        }
        
        if (!empty($query)) {
            $url['query'] = $query;
        }
        
        // Create request
        $request = [
            'name' => $method . ' ' . $path . ($endpoint['description'] ? ' - ' . $endpoint['description'] : ''),
            'request' => [
                'method' => $method,
                'header' => [
                    [
                        'key' => 'Content-Type',
                        'value' => 'application/json'
                    ],
                    [
                        'key' => 'authtoken',
                        'value' => '{{api_token}}'
                    ]
                ],
                'url' => $url,
                'description' => $endpoint['description']
            ]
        ];
        
        // Add body for POST/PUT requests
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $request['request']['body'] = [
                'mode' => 'raw',
                'raw' => $this->generateSampleBody($endpoint),
                'options' => [
                    'raw' => [
                        'language' => 'json'
                    ]
                ]
            ];
        }
        
        // Add path variables
        if (!empty($pathVariables)) {
            $request['request']['url']['variable'] = $pathVariables;
        }
        
        return $request;
    }
    
    /**
     * Generate sample request body
     * 
     * @param array $endpoint
     * @return string JSON string
     */
    private function generateSampleBody($endpoint)
    {
        $resource = $endpoint['category'];
        $method = strtoupper($endpoint['method']);
        
        // Skip loading samples file to avoid BASEPATH dependency
        // Always use generic sample generation which works without CodeIgniter
        
        // Generate generic sample
        $sample = [];
        
        // Common fields for most resources
        if ($method === 'POST') {
            $sample = [
                'name' => 'Sample ' . ucfirst($resource),
                'description' => 'Sample description'
            ];
            
            // Resource-specific fields
            switch ($resource) {
                case 'customers':
                case 'contacts':
                    $sample['email'] = 'sample@example.com';
                    $sample['phone'] = '+1234567890';
                    break;
                case 'invoices':
                case 'estimates':
                    $sample['clientid'] = 1;
                    $sample['date'] = date('Y-m-d');
                    $sample['duedate'] = date('Y-m-d', strtotime('+30 days'));
                    break;
                case 'tasks':
                    $sample['name'] = 'Sample Task';
                    $sample['status'] = 1;
                    $sample['priority'] = 2;
                    break;
            }
        } elseif ($method === 'PUT') {
            $sample = [
                'name' => 'Updated ' . ucfirst($resource),
                'description' => 'Updated description'
            ];
        }
        
        return json_encode($sample, JSON_PRETTY_PRINT);
    }
    
    /**
     * Export collection as JSON file
     * 
     * @param string $filename Optional filename
     * @return void
     */
    public function exportCollection($filename = null)
    {
        $collection = $this->generateCollection();
        $json = json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        $filename = $filename ?: 'Perfex_CRM_API_' . date('Y-m-d') . '.postman_collection.json';
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($json));
        
        echo $json;
        exit;
    }
}
