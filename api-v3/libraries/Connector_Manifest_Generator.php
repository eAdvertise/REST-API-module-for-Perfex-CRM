<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Connector Manifest Generator
 * 
 * Generates manifests for Zapier, Make, n8n
 */
class Connector_Manifest_Generator
{
    private $baseUrl;
    
    public function __construct($baseUrl = null)
    {
        // Base URL is required - should be passed from controller
        if (empty($baseUrl)) {
            // Fallback: build base URL manually to avoid needing CodeIgniter helpers
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
                        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) 
                        ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'];
            $scriptPath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
            $this->baseUrl = $protocol . $host . rtrim($scriptPath, '/') . '/api/';
        } else {
            $this->baseUrl = $baseUrl;
        }
    }
    
    /**
     * Generate Zapier manifest
     * 
     * @return array
     */
    public function generateZapierManifest()
    {
        return [
            'version' => '1.0.0',
            'platformVersion' => '2.0',
            'authentication' => [
                'type' => 'custom',
                'fields' => [
                    [
                        'key' => 'api_token',
                        'label' => 'API Token',
                        'type' => 'string',
                        'required' => true,
                        'help_text' => 'Your Perfex CRM API token'
                    ],
                    [
                        'key' => 'base_url',
                        'label' => 'Base URL',
                        'type' => 'string',
                        'required' => true,
                        'default' => rtrim($this->baseUrl, '/'),
                        'help_text' => 'Your Perfex CRM API base URL'
                    ]
                ],
                'test' => [
                    'url' => $this->baseUrl . 'login/key'
                ]
            ],
            'triggers' => $this->getZapierTriggers(),
            'actions' => $this->getZapierActions(),
            'searches' => $this->getZapierSearches()
        ];
    }
    
    /**
     * Get Zapier triggers
     * 
     * @return array
     */
    private function getZapierTriggers()
    {
        $resources = ['customers', 'invoices', 'leads', 'tasks', 'tickets'];
        $triggers = [];
        
        foreach ($resources as $resource) {
            $triggers[] = [
                'key' => 'new_' . $resource,
                'noun' => ucfirst($resource),
                'display' => [
                    'label' => 'New ' . ucfirst($resource),
                    'description' => 'Triggers when a new ' . $resource . ' is created'
                ],
                'operation' => [
                    'type' => 'polling',
                    'poll' => [
                        'url' => $this->baseUrl . 'zapier/poll/' . $resource,
                        'method' => 'GET'
                    ],
                    'sample' => [
                        'url' => $this->baseUrl . 'zapier/test/' . $resource,
                        'method' => 'GET'
                    ]
                ]
            ];
        }
        
        return $triggers;
    }
    
    /**
     * Get Zapier actions
     * 
     * @return array
     */
    private function getZapierActions()
    {
        $resources = ['customers', 'invoices', 'leads', 'tasks'];
        $actions = [];
        
        foreach ($resources as $resource) {
            // Create action
            $actions[] = [
                'key' => 'create_' . $resource,
                'noun' => ucfirst($resource),
                'display' => [
                    'label' => 'Create ' . ucfirst($resource),
                    'description' => 'Creates a new ' . $resource
                ],
                'operation' => [
                    'url' => $this->baseUrl . $resource,
                    'method' => 'POST',
                    'params' => $this->getResourceFields($resource)
                ]
            ];
            
            // Update action
            $actions[] = [
                'key' => 'update_' . $resource,
                'noun' => ucfirst($resource),
                'display' => [
                    'label' => 'Update ' . ucfirst($resource),
                    'description' => 'Updates an existing ' . $resource
                ],
                'operation' => [
                    'url' => $this->baseUrl . $resource . '/{{id}}',
                    'method' => 'PUT',
                    'params' => array_merge(
                        [['key' => 'id', 'required' => true, 'type' => 'integer']],
                        $this->getResourceFields($resource)
                    )
                ]
            ];
        }
        
        return $actions;
    }
    
    /**
     * Get Zapier searches
     * 
     * @return array
     */
    private function getZapierSearches()
    {
        $resources = ['customers', 'invoices', 'leads'];
        $searches = [];
        
        foreach ($resources as $resource) {
            $searches[] = [
                'key' => 'find_' . $resource,
                'noun' => ucfirst($resource),
                'display' => [
                    'label' => 'Find ' . ucfirst($resource),
                    'description' => 'Searches for ' . $resource
                ],
                'operation' => [
                    'url' => $this->baseUrl . $resource . '/search/{{query}}',
                    'method' => 'GET',
                    'params' => [
                        [
                            'key' => 'query',
                            'required' => true,
                            'type' => 'string'
                        ]
                    ]
                ]
            ];
        }
        
        return $searches;
    }
    
    /**
     * Get resource fields for forms
     * 
     * @param string $resource
     * @return array
     */
    private function getResourceFields($resource)
    {
        $fields = [
            'customers' => [
                ['key' => 'company', 'required' => true, 'type' => 'string'],
                ['key' => 'email', 'required' => false, 'type' => 'string'],
                ['key' => 'phone', 'required' => false, 'type' => 'string']
            ],
            'invoices' => [
                ['key' => 'clientid', 'required' => true, 'type' => 'integer'],
                ['key' => 'date', 'required' => true, 'type' => 'string'],
                ['key' => 'duedate', 'required' => false, 'type' => 'string']
            ],
            'leads' => [
                ['key' => 'title', 'required' => true, 'type' => 'string'],
                ['key' => 'email', 'required' => false, 'type' => 'string'],
                ['key' => 'phone', 'required' => false, 'type' => 'string']
            ],
            'tasks' => [
                ['key' => 'name', 'required' => true, 'type' => 'string'],
                ['key' => 'status', 'required' => false, 'type' => 'integer'],
                ['key' => 'priority', 'required' => false, 'type' => 'integer']
            ]
        ];
        
        return $fields[$resource] ?? [];
    }
    
    /**
     * Generate Make.com manifest
     * 
     * @return array
     */
    public function generateMakeManifest()
    {
        return [
            'version' => '1.0.0',
            'name' => 'Perfex CRM',
            'description' => 'Connect Perfex CRM with Make.com',
            'baseUrl' => rtrim($this->baseUrl, '/'),
            'authentication' => [
                'type' => 'custom',
                'fields' => [
                    [
                        'name' => 'api_token',
                        'label' => 'API Token',
                        'type' => 'string',
                        'required' => true
                    ]
                ],
                'test' => $this->baseUrl . 'login/key'
            ],
            'modules' => [
                'triggers' => $this->getMakeTriggers(),
                'actions' => $this->getMakeActions()
            ]
        ];
    }
    
    /**
     * Get Make.com triggers
     * 
     * @return array
     */
    private function getMakeTriggers()
    {
        $resources = ['customers', 'invoices', 'leads', 'tasks', 'tickets'];
        $triggers = [];
        
        foreach ($resources as $resource) {
            $triggers[] = [
                'name' => 'Watch ' . ucfirst($resource),
                'description' => 'Triggers when a new ' . $resource . ' is created',
                'poll' => [
                    'url' => $this->baseUrl . 'polling/poll/' . $resource,
                    'method' => 'GET'
                ]
            ];
        }
        
        return $triggers;
    }
    
    /**
     * Get Make.com actions
     * 
     * @return array
     */
    private function getMakeActions()
    {
        $resources = ['customers', 'invoices', 'leads', 'tasks'];
        $actions = [];
        
        foreach ($resources as $resource) {
            $actions[] = [
                'name' => 'Create ' . ucfirst($resource),
                'description' => 'Creates a new ' . $resource,
                'url' => $this->baseUrl . $resource,
                'method' => 'POST'
            ];
            
            $actions[] = [
                'name' => 'Update ' . ucfirst($resource),
                'description' => 'Updates an existing ' . $resource,
                'url' => $this->baseUrl . $resource . '/{{id}}',
                'method' => 'PUT'
            ];
        }
        
        return $actions;
    }
    
    /**
     * Generate n8n manifest
     * 
     * @return array
     */
    public function generateN8nManifest()
    {
        return [
            'name' => 'Perfex CRM',
            'version' => '1.0.0',
            'description' => 'Perfex CRM API integration for n8n',
            'credentials' => [
                [
                    'name' => 'perfexCrmApi',
                    'displayName' => 'Perfex CRM API',
                    'properties' => [
                        [
                            'displayName' => 'API Token',
                            'name' => 'apiToken',
                            'type' => 'string',
                            'required' => true
                        ],
                        [
                            'displayName' => 'Base URL',
                            'name' => 'baseUrl',
                            'type' => 'string',
                            'default' => rtrim($this->baseUrl, '/'),
                            'required' => true
                        ]
                    ]
                ]
            ],
            'nodes' => [
                'triggers' => $this->getN8nTriggers(),
                'actions' => $this->getN8nActions()
            ]
        ];
    }
    
    /**
     * Get n8n triggers
     * 
     * @return array
     */
    private function getN8nTriggers()
    {
        $resources = ['customers', 'invoices', 'leads', 'tasks', 'tickets'];
        $triggers = [];
        
        foreach ($resources as $resource) {
            $triggers[] = [
                'name' => 'Perfex CRM: ' . ucfirst($resource) . ' Trigger',
                'type' => 'n8n-nodes-base.httpRequest',
                'properties' => [
                    'url' => $this->baseUrl . 'connectors/poll/' . $resource,
                    'method' => 'GET',
                    'authentication' => 'predefinedCredentialType',
                    'nodeCredentialType' => 'perfexCrmApi'
                ]
            ];
        }
        
        return $triggers;
    }
    
    /**
     * Get n8n actions
     * 
     * @return array
     */
    private function getN8nActions()
    {
        $resources = ['customers', 'invoices', 'leads', 'tasks'];
        $actions = [];
        
        foreach ($resources as $resource) {
            $actions[] = [
                'name' => 'Perfex CRM: Create ' . ucfirst($resource),
                'type' => 'n8n-nodes-base.httpRequest',
                'properties' => [
                    'url' => $this->baseUrl . $resource,
                    'method' => 'POST',
                    'authentication' => 'predefinedCredentialType',
                    'nodeCredentialType' => 'perfexCrmApi'
                ]
            ];
        }
        
        return $actions;
    }
    
    /**
     * Export manifest as JSON
     * 
     * @param string $platform Platform name (zapier, make, n8n)
     * @param string $filename Optional filename
     * @return void
     */
    public function exportManifest($platform, $filename = null)
    {
        $manifest = null;
        
        switch (strtolower($platform)) {
            case 'zapier':
                $manifest = $this->generateZapierManifest();
                $filename = $filename ?: 'zapier_manifest.json';
                break;
            case 'make':
                $manifest = $this->generateMakeManifest();
                $filename = $filename ?: 'make_manifest.json';
                break;
            case 'n8n':
                $manifest = $this->generateN8nManifest();
                $filename = $filename ?: 'n8n_manifest.json';
                break;
            default:
                // Clear output and send JSON error instead of show_error() to avoid HTML output
                while (ob_get_level()) {
                    ob_end_clean();
                }
                header('Content-Type: application/json');
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['error' => 'Invalid platform specified']);
                exit;
        }
        
        $json = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        // Clear ALL output buffering completely
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Disable any further output buffering
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', 1);
        }
        @ini_set('zlib.output_compression', 0);
        
        // Set proper headers for JSON download
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($json));
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        
        // Output JSON
        echo $json;
        
        // Flush output and exit
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        exit;
    }
}
