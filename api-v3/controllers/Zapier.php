<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require __DIR__ . '/REST_Controller.php';

/**
 * Zapier Controller
 * 
 * Handles api/zapier routes via generic api/(:any) pattern
 * Routes api/zapier/test/customers -> zapier/data/test/customers -> test_get('customers')
 * 
 * Extends REST_Controller directly (like Customers.php) to avoid Session.php errors
 */
class Zapier extends REST_Controller
{
    public function __construct()
    {
        // Map authtoken query parameter to Authtoken (for compatibility with automation platforms and Postman)
        // Also support api_key for backwards compatibility
        if (isset($_GET['authtoken']) && !empty($_GET['authtoken'])) {
            $_GET['Authtoken'] = $_GET['authtoken'];
            $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $_GET['authtoken'];
        } elseif (isset($_GET['api_key']) && !empty($_GET['api_key'])) {
            // Backwards compatibility with api_key parameter
            $_GET['Authtoken'] = $_GET['api_key'];
            $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $_GET['api_key'];
        }
        
        // Simple constructor - exactly like Customers.php
        parent::__construct();
        $this->load->model('api_model');
    }
    
    /**
     * @api {get} api/zapier/{method}/{resource} Automation Route Handler
     * @apiName ZapierRouteHandler
     * @apiGroup Automation
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     * @apiHeader {String} [Authorization] Bearer token (alternative to authtoken header)
     *
     * @apiParam {String} method Method name (poll, test, resources)
     * @apiParam {String} [resource] Resource name (customers, invoices, leads, tasks, tickets) - required for poll and test methods
     *
     * @apiDescription This is a routing method that maps to specific Zapier endpoints. Use the direct endpoints (poll_get, test_get, resources_get) instead.
     *
     * @apiSuccess {Boolean} status Request status
     * @apiSuccess {Mixed} data Response data
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "data": [
     *         { "key": "customers", "label": "Customers", "description": "New or updated customers" },
     *         { "key": "invoices", "label": "Invoices", "description": "New or updated invoices" },
     *         { "key": "leads", "label": "Leads", "description": "New or updated leads" },
     *         { "key": "tasks", "label": "Tasks", "description": "New or updated tasks" },
     *         { "key": "tickets", "label": "Tickets", "description": "New or updated support tickets" }
     *       ]
     *     }
     *
     * @apiError {Boolean} status Request status
     * @apiError {String} error Error message
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "Method not found: invalid_get"
     *     }
     */
    public function data_get($method = '', $resource = '')
    {
        // Map to appropriate method
        if (empty($method)) {
            $method = 'resources';
        }
        
        $method_name = $method . '_get';
        
        if (method_exists($this, $method_name)) {
            if ($resource) {
                call_user_func([$this, $method_name], $resource);
            } else {
                call_user_func([$this, $method_name]);
            }
        } else {
            $this->response(['error' => 'Method not found: ' . $method_name], 404);
        }
    }
    
    /**
     * @api {get} api/zapier/poll/{resource} Poll for New Data
     * @apiName PollData
     * @apiGroup Automation
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     * @apiHeader {String} [Authorization] Bearer token (alternative to authtoken header)
     *
     * @apiParam {String} resource Resource name. Must be one of: customers, invoices, leads, tasks, tickets
     * @apiParam {Number} [since] Unix timestamp to filter records created/updated after this time. Default: last 24 hours
     * @apiParam {Number} [limit] Maximum number of records to return. Default: 50
     *
     * @apiDescription Polling endpoint used by automation platforms (Zapier, Make.com, n8n) to retrieve new or updated records. Returns records that were created or modified after the specified timestamp.
     *
     * @apiSuccess {Boolean} status Request status
     * @apiSuccess {Array} data Array of resource records
     * @apiSuccess {Object} meta Metadata object
     * @apiSuccess {Number} meta.since Timestamp used for filtering
     * @apiSuccess {Number} meta.count Number of records returned
     * @apiSuccess {Number} meta.next_poll Recommended timestamp for next poll
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "data": [
     *         {
     *           "id": "1",
     *           "company": "Example Company",
     *           "datecreated": "2024-01-15 10:30:00"
     *         }
     *       ],
     *       "meta": {
     *         "since": 1705312200,
     *         "count": 1,
     *         "next_poll": 1705315800
     *       }
     *     }
     *
     * @apiError {Boolean} status Request status
     * @apiError {String} message Error message
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *       "status": false,
     *       "message": "Resource parameter required"
     *     }
     */
    public function poll_get($resource = '')
    {
        if (empty($resource)) {
            $this->response([
                'status' => false,
                'message' => 'Resource parameter required'
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }
        
        // Get since timestamp (default: last 24 hours)
        $since = $this->get('since') ?: (time() - 86400);
        $limit = $this->get('limit') ?: 50;
        
        // Get new/updated records
        $data = $this->getPollingData($resource, $since, $limit);
        
        $this->response([
            'status' => true,
            'data' => $data,
            'meta' => [
                'since' => $since,
                'count' => count($data),
                'next_poll' => time()
            ]
        ], REST_Controller::HTTP_OK);
    }
    
    /**
     * @api {get} api/zapier/test/{resource} Test Trigger
     * @apiName TestTrigger
     * @apiGroup Automation
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     * @apiHeader {String} [Authorization] Bearer token (alternative to authtoken header)
     *
     * @apiParam {String} resource Resource name. Must be one of: customers, invoices, leads, tasks, tickets
     *
     * @apiDescription Returns sample data for testing automation triggers. Used by automation platforms to validate trigger configuration and show sample data structure to users.
     *
     * @apiSuccess {Boolean} status Request status
     * @apiSuccess {Object} data Sample data record for the specified resource
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "data": {
     *         "id": 1,
     *         "company": "Sample Company",
     *         "email": "sample@example.com",
     *         "phone": "+1234567890"
     *       }
     *     }
     *
     * @apiError {Boolean} status Request status
     * @apiError {String} message Error message
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *       "status": false,
     *       "message": "Resource parameter required"
     *     }
     */
    public function test_get($resource = '')
    {
        if (empty($resource)) {
            $this->response([
                'status' => false,
                'message' => 'Resource parameter required'
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }
        
        $sampleData = $this->getSampleData($resource);
        
        $this->response([
            'status' => true,
            'data' => $sampleData
        ], REST_Controller::HTTP_OK);
    }
    
    /**
     * @api {get} api/zapier/resources List Available Resources
     * @apiName ListResources
     * @apiGroup Automation
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     * @apiHeader {String} [Authorization] Bearer token (alternative to authtoken header)
     *
     * @apiDescription Returns a list of all available resources that can be used for polling and testing. This endpoint is used by automation platforms to discover available triggers.
     *
     * @apiSuccess {Boolean} status Request status
     * @apiSuccess {Array} data Array of available resources
     * @apiSuccess {String} data[].key Resource key identifier
     * @apiSuccess {String} data[].label Human-readable resource name
     * @apiSuccess {String} data[].description Resource description
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "data": [
     *         {
     *           "key": "customers",
     *           "label": "Customers",
     *           "description": "New or updated customers"
     *         },
     *         {
     *           "key": "invoices",
     *           "label": "Invoices",
     *           "description": "New or updated invoices"
     *         },
     *         {
     *           "key": "leads",
     *           "label": "Leads",
     *           "description": "New or updated leads"
     *         },
     *         {
     *           "key": "tasks",
     *           "label": "Tasks",
     *           "description": "New or updated tasks"
     *         },
     *         {
     *           "key": "tickets",
     *           "label": "Tickets",
     *           "description": "New or updated support tickets"
     *         }
     *       ]
     *     }
     */
    public function resources_get()
    {
        $resources = [
            [
                'key' => 'customers',
                'label' => 'Customers',
                'description' => 'New or updated customers'
            ],
            [
                'key' => 'invoices',
                'label' => 'Invoices',
                'description' => 'New or updated invoices'
            ],
            [
                'key' => 'leads',
                'label' => 'Leads',
                'description' => 'New or updated leads'
            ],
            [
                'key' => 'tasks',
                'label' => 'Tasks',
                'description' => 'New or updated tasks'
            ],
            [
                'key' => 'tickets',
                'label' => 'Tickets',
                'description' => 'New or updated support tickets'
            ]
        ];
        
        $this->response([
            'status' => true,
            'data' => $resources
        ], REST_Controller::HTTP_OK);
    }
    
    /**
     * Get polling data for a resource
     * 
     * @param string $resource Resource name
     * @param int $since Timestamp
     * @param int $limit Limit
     * @return array
     */
    private function getPollingData($resource, $since, $limit)
    {
        $data = [];
        
        switch ($resource) {
            case 'customers':
                $this->load->model('Clients_model');
                $this->db->where(db_prefix() . 'clients.datecreated >=', date('Y-m-d H:i:s', $since));
                $this->db->order_by(db_prefix() . 'clients.datecreated', 'DESC');
                $this->db->limit($limit);
                $result = $this->Clients_model->get('', db_prefix() . 'clients.active=1');
                if (is_array($result)) {
                    $data = $result;
                } elseif (is_object($result)) {
                    $data = [$result];
                }
                break;
                
            case 'invoices':
                $this->load->model('Invoices_model');
                $this->db->where(db_prefix() . 'invoices.date >=', date('Y-m-d', $since));
                $this->db->order_by(db_prefix() . 'invoices.date', 'DESC');
                $this->db->limit($limit);
                $result = $this->Invoices_model->get();
                if (is_array($result)) {
                    $data = $result;
                } elseif (is_object($result)) {
                    $data = [$result];
                }
                break;
                
            case 'leads':
                $this->load->model('Leads_model');
                $this->db->where(db_prefix() . 'leads.dateadded >=', date('Y-m-d H:i:s', $since));
                $this->db->order_by(db_prefix() . 'leads.dateadded', 'DESC');
                $this->db->limit($limit);
                $result = $this->Leads_model->get('', ['junk' => 0]);
                if (is_array($result)) {
                    $data = $result;
                } elseif (is_object($result)) {
                    $data = [$result];
                }
                break;
                
            case 'tasks':
                $this->load->model('Tasks_model');
                // Query tasks directly since Tasks_model->get() expects a numeric ID
                $this->db->select('*');
                $this->db->from(db_prefix() . 'tasks');
                $this->db->where('status !=', 5);
                $this->db->where('dateadded >=', date('Y-m-d H:i:s', $since));
                $this->db->order_by('dateadded', 'DESC');
                $this->db->limit($limit);
                $result = $this->db->get()->result_array();
                
                // Convert to objects and enrich with model data if needed
                $data = [];
                foreach ($result as $task) {
                    // Optionally enrich with full task data using model
                    $fullTask = $this->Tasks_model->get($task['id']);
                    $data[] = $fullTask ?: $task;
                }
                break;
                
            case 'tickets':
                $this->load->model('Tickets_model');
                $this->db->where(db_prefix() . 'tickets.date >=', date('Y-m-d H:i:s', $since));
                $this->db->order_by(db_prefix() . 'tickets.date', 'DESC');
                $this->db->limit($limit);
                $result = $this->Tickets_model->get('', ['status !=' => 5]);
                if (is_array($result)) {
                    $data = $result;
                } elseif (is_object($result)) {
                    $data = [$result];
                }
                break;
        }
        
        return $data;
    }
    
    /**
     * Get sample data for testing
     * 
     * @param string $resource
     * @return array
     */
    private function getSampleData($resource)
    {
        // Get one record as sample
        $data = $this->getPollingData($resource, time() - 86400 * 30, 1);
        
        if (empty($data)) {
            // Return mock data if no real data
            return $this->getMockData($resource);
        }
        
        return is_array($data) ? $data[0] : $data;
    }
    
    /**
     * Get mock data for testing
     * 
     * @param string $resource
     * @return array
     */
    private function getMockData($resource)
    {
        $mock = [
            'customers' => [
                'id' => 1,
                'company' => 'Sample Company',
                'email' => 'sample@example.com',
                'phone' => '+1234567890'
            ],
            'invoices' => [
                'id' => 1,
                'number' => 'INV-001',
                'clientid' => 1,
                'total' => 1000.00,
                'status' => 1
            ],
            'leads' => [
                'id' => 1,
                'title' => 'Sample Lead',
                'email' => 'lead@example.com',
                'status' => 1
            ],
            'tasks' => [
                'id' => 1,
                'name' => 'Sample Task',
                'status' => 1,
                'priority' => 2
            ],
            'tickets' => [
                'id' => 1,
                'subject' => 'Sample Ticket',
                'status' => 1,
                'priority' => 2
            ]
        ];
        
        return $mock[$resource] ?? [];
    }
}
