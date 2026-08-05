<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require __DIR__ . '/REST_Controller.php';

/**
 * Connector_Polling Controller
 * 
 * Provides endpoints for automation platforms (Zapier, Make, n8n)
 * 
 * Note: Renamed from "Connectors" to avoid conflict with admin route admin/api/connectors
 */
class Connector_Polling extends REST_Controller
{
    public function __construct()
    {
        // Map api_key query parameter to Authtoken (simple, no CodeIgniter dependencies)
        if (isset($_GET['api_key']) && !empty($_GET['api_key'])) {
            $_GET['Authtoken'] = $_GET['api_key'];
            $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $_GET['api_key'];
        }
        
        // Simple constructor - exactly like Customers.php
        parent::__construct();
        $this->load->model('api_model');
    }
    
    /**
     * Polling endpoint for triggers
     * Used by Zapier, Make, n8n to poll for new data
     * 
     * GET /api/connectors/poll/{resource}?since={timestamp}
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
                // Query directly to respect since filter
                $this->db->select('*');
                $this->db->from(db_prefix() . 'clients');
                $this->db->where('active', 1);
                $this->db->group_start();
                $this->db->where('datecreated >=', date('Y-m-d H:i:s', $since));
                $this->db->or_where('last_modified >=', date('Y-m-d H:i:s', $since));
                $this->db->group_end();
                $this->db->order_by('datecreated', 'DESC');
                $this->db->limit($limit);
                $result = $this->db->get()->result_array();
                $data = $result ?: [];
                break;
                
            case 'invoices':
                // Query directly to respect since filter
                $this->db->select('*');
                $this->db->from(db_prefix() . 'invoices');
                $this->db->group_start();
                $this->db->where('date >=', date('Y-m-d', $since));
                $this->db->or_where('last_recurring_date >=', date('Y-m-d', $since));
                $this->db->group_end();
                $this->db->order_by('date', 'DESC');
                $this->db->limit($limit);
                $result = $this->db->get()->result_array();
                $data = $result ?: [];
                break;
                
            case 'leads':
                // Query directly to respect since filter
                $this->db->select('*');
                $this->db->from(db_prefix() . 'leads');
                $this->db->where('dateadded >=', date('Y-m-d H:i:s', $since));
                $this->db->where('junk', 0);
                $this->db->order_by('dateadded', 'DESC');
                $this->db->limit($limit);
                $result = $this->db->get()->result_array();
                $data = $result ?: [];
                break;
                
            case 'tasks':
                // Query directly to respect since filter
                $this->db->select('*');
                $this->db->from(db_prefix() . 'tasks');
                $this->db->where('status !=', 5);
                $this->db->group_start();
                $this->db->where('dateadded >=', date('Y-m-d H:i:s', $since));
                $this->db->or_where('datefinished >=', date('Y-m-d H:i:s', $since));
                $this->db->group_end();
                $this->db->order_by('dateadded', 'DESC');
                $this->db->limit($limit);
                $result = $this->db->get()->result_array();
                $data = $result ?: [];
                break;
                
            case 'tickets':
                // Query directly to respect since filter
                $this->db->select('*');
                $this->db->from(db_prefix() . 'tickets');
                $this->db->where('status !=', 5);
                $this->db->where('date >=', date('Y-m-d H:i:s', $since));
                $this->db->order_by('date', 'DESC');
                $this->db->limit($limit);
                $result = $this->db->get()->result_array();
                $data = $result ?: [];
                break;
        }
        
        return $data;
    }
    
    /**
     * Test trigger endpoint
     * Returns sample data for testing
     * 
     * GET /api/connectors/test/{resource}
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
    
    /**
     * Get available resources for polling
     * 
     * GET /api/connectors/resources
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
}
