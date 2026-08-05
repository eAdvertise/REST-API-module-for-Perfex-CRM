<?php

use \WpOrg\Requests\Requests as RestapiRequests;

defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/../libraries/Api_Event_Manager.php';

class Api extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('api_model');
        $this->load->library('app_modules');

        if (!$this->app_modules->is_active('api')) {
            access_denied("Api");
        }

        \modules\api\core\Apiinit::the_da_vinci_code('api');
    }

    /**
     * v3: update status for the admin UI (current/latest version).
     * GET admin/api/check_update (?force=1 bypasses the daily cache)
     */
    public function check_update()
    {
        if (!is_admin()) {
            access_denied('Api');
        }
        require_once __DIR__ . '/../libraries/Api_Self_Update.php';
        $updater = new Api_Self_Update();
        header('Content-Type: application/json');
        echo json_encode($updater->status($this->input->get('force') === '1'));
        exit;
    }

    /**
     * v3: one-click self-update (POST only, admins only).
     * POST admin/api/run_update
     */
    public function run_update()
    {
        if (!is_admin()) {
            access_denied('Api');
        }
        if (strtoupper($this->input->server('REQUEST_METHOD')) !== 'POST') {
            show_error('POST required', 405);
        }
        require_once __DIR__ . '/../libraries/Api_Self_Update.php';
        $updater = new Api_Self_Update();
        $result  = $updater->runUpdate();
        if (!empty($result['success'])) {
            log_activity('API module self-updated: ' . $result['message']);
        }
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
    
    /**
     * Remap to catch connectors route before method resolution
     */
    public function _remap($method, $params = [])
    {
        // Check if this is the connectors route
        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $uri_string = $this->uri->uri_string();
        
        if ($method === 'connectors' || 
            strpos($request_uri, '/admin/api/connectors') !== false ||
            strpos($request_uri, '/admin/api/automation_connectors') !== false) {
            // This is the connectors route - handle it directly
            $this->_handle_connectors_directly();
            return;
        }
        
        // Normal remap for other methods
        if (method_exists($this, $method)) {
            call_user_func_array([$this, $method], $params);
        } else {
            show_404();
        }
    }
    
    /**
     * Handle connectors route directly (bypasses normal method call)
     */
    private function _handle_connectors_directly()
    {
        \modules\api\core\Apiinit::the_da_vinci_code('api');
        
        // Build base URL manually to avoid any potential Session loading issues
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
                    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) 
                    ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $scriptPath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
        $baseUrl = $protocol . $host . rtrim($scriptPath, '/') . '/api/';
        
        $data['title'] = _l('automation_connectors');
        $data['base_url'] = $baseUrl;
        $this->load->view('connectors', $data);
    }

    public function api_management()
    {
        \modules\api\core\Apiinit::the_da_vinci_code('api');

        $data['user_api'] = $this->api_model->get_user();
        $data['title'] = _l('api_management');
        $this->load->view('api_management', $data);
    }

    /* API user statistics */
    public function user_stats($id = '')
    {
        \modules\api\core\Apiinit::ease_of_mind('api');

        if (!is_admin()) {
            access_denied('User Statistics');
        }

        $data['title'] = _l('user_statistics');
        $data['user_id'] = $id;
        
        if ($id) {
            $user_api = $this->api_model->get_user($id);
            $data['user_api'] = $user_api && count($user_api) ? $user_api[0] : null;
            
            if ($data['user_api']) {
                $data['quota_summary'] = $this->api_model->get_quota_summary($data['user_api']['token']);
                $data['quota_stats'] = $this->api_model->get_quota_stats($data['user_api']['token']);
                $data['top_endpoints'] = $this->api_model->get_top_endpoints($data['user_api']['token']);
            }
        }
        
        $data['api_users'] = $this->api_model->get_user();
        $this->load->view('user_stats', $data);
    }

    public function api_guide()
    { 
        fopen(APP_MODULES_PATH . 'api/views/apidoc/index.html', 'r');
    }

    /* Add new user or update existing*/
    public function user()
    {
        \modules\api\core\Apiinit::ease_of_mind('api');

        if (!is_admin()) {
            access_denied('Ticket Priorities');
        }
        if ($this->input->post()) {
            \modules\api\core\Apiinit::the_da_vinci_code('api');

            if (!$this->input->post('id')) {
                $id = $this->api_model->add_user($this->input->post());
               
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('user_api')));
                }
                redirect(admin_url('api/api_management'));
            } else {
                $data           = $this->input->post();
                $id             = $data['id'];
                unset($data['id']);
                $success = $this->api_model->update_user($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('user_api')));
                }
                redirect(admin_url('api/api_management'));
            }
            die;
        }
    }

    /* Update user quotas */
    public function update_user_quotas()
    {
        \modules\api\core\Apiinit::ease_of_mind('api');

        if (!is_admin()) {
            access_denied('User Quotas');
        }
        
        if ($this->input->post()) {
            \modules\api\core\Apiinit::the_da_vinci_code('api');

            $data = $this->input->post();
            $id = $data['id'];
            unset($data['id']);
            
            // Add timestamp for quota update
            $data['quota_updated_at'] = date('Y-m-d H:i:s');
            
            $success = $this->api_model->update_user($data, $id);
            if ($success) {
                set_alert('success', _l('quota_updated_successfully'));
            } else {
                set_alert('danger', _l('quota_update_failed'));
            }
            redirect(admin_url('api/api_management'));
        }
    }

    /* Edit user */
    public function create_user()
    {
        \modules\api\core\Apiinit::ease_of_mind('api');

        if (!is_admin()) {
            access_denied('User');
        }
        $data['title'] = _l('new_user_api');
        $this->load->view('create_user_api', $data);
    }

    /* Edit user */
    public function edit_user($id)
    {
        \modules\api\core\Apiinit::ease_of_mind('api');

        if (!is_admin()) {
            access_denied('User');
        }
        if (!$id) {
            redirect(admin_url('api/api_management'));
        }
        $user_api = $this->api_model->get_user($id);
        $data['user_api'] = $user_api && count($user_api) ? $user_api[0] : null;
        $data['title'] = _l('edit_user_api');
        $this->load->view('edit_user_api', $data);
    }


    /* Delete user */
    public function delete_user($id)
    {
        \modules\api\core\Apiinit::ease_of_mind('api');
        
        if (!is_admin()) {
            access_denied('User');
        }
        if (!$id) {
            redirect(admin_url('api/api_management'));
        }
        $response = $this->api_model->delete_user($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('user_api')));
        }
        redirect(admin_url('api/api_management'));
    }

    /* Get user statistics data via AJAX */
    public function get_user_stats_data()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $user_id = $this->input->post('user_id');
        $days = $this->input->post('days') ?: 30;
        
        if ($user_id) {
            $user_api = $this->api_model->get_user($user_id);
            if ($user_api && count($user_api)) {
                $api_key = $user_api[0]['token'];
                $quota_summary = $this->api_model->get_quota_summary($api_key);
                $quota_stats = $this->api_model->get_quota_stats($api_key, $days);
                $top_endpoints = $this->api_model->get_top_endpoints($api_key);
                
                echo json_encode([
                    'quota_summary' => $quota_summary,
                    'quota_stats' => $quota_stats,
                    'top_endpoints' => $top_endpoints
                ]);
                return;
            }
        }
        
        echo json_encode(['error' => 'User not found']);
    }

    /* Clean old logs */
    public function clean_logs()
    {
        if (!is_admin()) {
            access_denied('api_management');
        }

        $days = $this->input->post('days') ?: 90;
        
        if ($this->api_model->clean_old_logs($days)) {
            set_alert('success', _l('logs_cleaned_successfully'));
        } else {
            set_alert('danger', _l('log_cleaning_failed'));
        }
        
        redirect(admin_url('api/api_management'));
    }

    /**
     * API Settings page
     */
    public function settings()
    {
        \modules\api\core\Apiinit::the_da_vinci_code('api');

        if (!is_admin()) {
            access_denied('API Settings');
        }

        $data['title'] = _l('api_settings');
        $this->load->view('settings', $data);
    }

    /* Save API settings */
    public function save_settings()
    {
        \modules\api\core\Apiinit::ease_of_mind('api');

        if (!is_admin()) {
            access_denied('API Settings');
        }

        if ($this->input->post()) {
            \modules\api\core\Apiinit::the_da_vinci_code('api');

            try {
                // Save JSON normalization setting
                // Checkbox sends '1' if checked, nothing if unchecked, so we check for '1' explicitly
                $enable_normalization = ($this->input->post('api_enable_transformers') === '1') ? '1' : '0';
                update_option('api_enable_transformers', $enable_normalization);

                // v3 platform settings
                $v3_checkboxes = ['api_mcp_enabled', 'api_staff_visibility', 'api_webhook_ssrf_strict', 'api_webhook_ssl_verify'];
                foreach ($v3_checkboxes as $v3_option) {
                    if ($this->input->post($v3_option) !== null) {
                        $v3_value = ($this->input->post($v3_option) === '1') ? '1' : '0';
                        if (!update_option($v3_option, $v3_value)) {
                            add_option($v3_option, $v3_value);
                        }
                    }
                }
                $delivery_mode = $this->input->post('api_webhook_delivery_mode');
                if (in_array($delivery_mode, ['immediate', 'queue'], true)) {
                    if (!update_option('api_webhook_delivery_mode', $delivery_mode)) {
                        add_option('api_webhook_delivery_mode', $delivery_mode);
                    }
                }
                $throttle = $this->input->post('api_auth_throttle_limit');
                if ($throttle !== null && $throttle !== '') {
                    $throttle = (string)max(0, min(1000, (int)$throttle));
                    if (!update_option('api_auth_throttle_limit', $throttle)) {
                        add_option('api_auth_throttle_limit', $throttle);
                    }
                }
                // Third-party custom-table allowlist (comma-separated table names)
                $allowed_tables = $this->input->post('api_thirdparty_allowed_tables');
                if ($allowed_tables !== null) {
                    $tables         = array_filter(array_unique(array_map('trim', explode(',', (string)$allowed_tables))));
                    $allowed_tables = implode(',', $tables);
                    if (!update_option('api_thirdparty_allowed_tables', $allowed_tables)) {
                        add_option('api_thirdparty_allowed_tables', $allowed_tables);
                    }
                }

                // Save middleware configuration
                $middleware_config = [
                    'request_logger' => [
                        'enabled' => ($this->input->post('middleware_request_logger') === '1') ? true : false
                    ],
                    'response_cache' => [
                        'enabled' => ($this->input->post('middleware_response_cache') === '1') ? true : false,
                        'ttl' => (int)$this->input->post('middleware_cache_ttl') ?: 300
                    ],
                    'ip_whitelist' => [
                        'enabled' => ($this->input->post('middleware_ip_whitelist') === '1') ? true : false,
                        'ips' => $this->parseIpList($this->input->post('middleware_ip_whitelist_ips'))
                    ],
                    'ip_blacklist' => [
                        'enabled' => ($this->input->post('middleware_ip_blacklist') === '1') ? true : false,
                        'ips' => $this->parseIpList($this->input->post('middleware_ip_blacklist_ips'))
                    ],
                    'security_headers' => [
                        'enabled' => ($this->input->post('middleware_security_headers') === '1') ? true : false
                    ],
                    'request_size_limit' => [
                        'enabled' => ($this->input->post('middleware_request_size_limit') === '1') ? true : false,
                        'max_size_mb' => (int)$this->input->post('middleware_max_request_size_mb') ?: 10
                    ]
                ];
                
                update_option('api_middleware_config', json_encode($middleware_config));

                set_alert('success', _l('settings_updated_successfully'));
                redirect(admin_url('api/settings'));
            } catch (Exception $e) {
                set_alert('danger', 'Error saving settings: ' . $e->getMessage());
                redirect(admin_url('api/settings'));
            }
        } else {
            redirect(admin_url('api/settings'));
        }
    }

    /**
     * Parse IP list from textarea input
     * 
     * @param string $ipList
     * @return array
     */
    private function parseIpList($ipList)
    {
        if (empty($ipList)) {
            return [];
        }
        
        $ips = [];
        $lines = explode("\n", $ipList);
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $ips[] = $line;
            }
        }
        
        return $ips;
    }

    /**
     * Webhook management page
     */
    // Renamed from webhooks(): the REST Webhooks controller shadows /admin/api/webhooks.
    public function event_webhooks()
    {
        \modules\api\core\Apiinit::the_da_vinci_code('api');
        
        $data['title'] = _l('api_webhooks');
        $data['webhooks'] = $this->api_model->get_webhooks();
        $data['available_events'] = [
            Api_Event_Manager::EVENT_REQUEST_RECEIVED => 'Request Received',
            Api_Event_Manager::EVENT_BEFORE_CONTROLLER => 'Before Controller',
            Api_Event_Manager::EVENT_AFTER_CONTROLLER => 'After Controller',
            Api_Event_Manager::EVENT_BEFORE_RESPONSE => 'Before Response',
            Api_Event_Manager::EVENT_RESPONSE_SENT => 'Response Sent',
            Api_Event_Manager::EVENT_ERROR_OCCURRED => 'Error Occurred',
            Api_Event_Manager::EVENT_RATE_LIMIT_EXCEEDED => 'Rate Limit Exceeded',
            Api_Event_Manager::EVENT_AUTHENTICATION_FAILED => 'Authentication Failed',
            Api_Event_Manager::EVENT_AUTHENTICATION_SUCCESS => 'Authentication Success',
        ];
        
        $this->load->view('webhooks', $data);
    }

    /**
     * Create or update webhook
     */
    public function webhook($id = '')
    {
        \modules\api\core\Apiinit::the_da_vinci_code('api');
        
        if ($this->input->post()) {
            // Process headers JSON - trim and validate
            $headersInput = trim($this->input->post('headers') ?: '');
            $headersJson = null;
            
            if (!empty($headersInput)) {
                // Decode and re-encode to validate JSON and normalize formatting
                $decoded = json_decode($headersInput, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $headersJson = json_encode($decoded, JSON_PRETTY_PRINT);
                } else {
                    // Invalid JSON - store as-is but show warning
                    set_alert('warning', _l('invalid_json_format') . ' - ' . json_last_error_msg());
                    $headersJson = $headersInput;
                }
            }
            
            $data = [
                'name' => $this->input->post('name'),
                'url' => $this->input->post('url'),
                'events' => implode(',', $this->input->post('events') ?: []),
                'secret' => $this->input->post('secret') ?: null,
                'active' => $this->input->post('active') ? 1 : 0,
                'headers' => $headersJson,
                'timeout' => (int)$this->input->post('timeout') ?: 30,
                'retry_count' => (int)$this->input->post('retry_count') ?: 3,
            ];
            
            if ($id) {
                $this->api_model->update_webhook($id, $data);
                set_alert('success', _l('webhook_updated_successfully'));
            } else {
                $id = $this->api_model->create_webhook($data);
                set_alert('success', _l('webhook_created_successfully'));
            }
            
            redirect(admin_url('api/event_webhooks'));
        }
        
        $data['title'] = $id ? _l('edit_webhook') : _l('new_webhook');
        $data['webhook'] = $id ? $this->api_model->get_webhook($id) : null;
        $data['available_events'] = [
            Api_Event_Manager::EVENT_REQUEST_RECEIVED => 'Request Received',
            Api_Event_Manager::EVENT_BEFORE_CONTROLLER => 'Before Controller',
            Api_Event_Manager::EVENT_AFTER_CONTROLLER => 'After Controller',
            Api_Event_Manager::EVENT_BEFORE_RESPONSE => 'Before Response',
            Api_Event_Manager::EVENT_RESPONSE_SENT => 'Response Sent',
            Api_Event_Manager::EVENT_ERROR_OCCURRED => 'Error Occurred',
            Api_Event_Manager::EVENT_RATE_LIMIT_EXCEEDED => 'Rate Limit Exceeded',
            Api_Event_Manager::EVENT_AUTHENTICATION_FAILED => 'Authentication Failed',
            Api_Event_Manager::EVENT_AUTHENTICATION_SUCCESS => 'Authentication Success',
        ];
        
        $this->load->view('webhook_form', $data);
    }

    /**
     * Delete webhook
     */
    public function delete_webhook($id)
    {
        \modules\api\core\Apiinit::the_da_vinci_code('api');
        
        $this->api_model->delete_webhook($id);
        set_alert('success', _l('webhook_deleted_successfully'));
        redirect(admin_url('api/event_webhooks'));
    }

    /**
     * Test webhook
     */
    public function test_webhook($id)
    {
        // Disable any output buffering and ensure clean JSON response
        if (ob_get_level()) {
            ob_clean();
        }
        
        \modules\api\core\Apiinit::the_da_vinci_code('api');
        
        $webhook = $this->api_model->get_webhook($id);
        if (!$webhook) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Webhook not found']));
            return;
        }
        
        try {
            if (!class_exists('Api_Webhook_Service')) {
                $webhookServiceFile = __DIR__ . '/../libraries/Api_Webhook_Service.php';
                if (file_exists($webhookServiceFile)) {
                    require_once $webhookServiceFile;
                }
            }
            
            $webhookService = new Api_Webhook_Service();
            $testData = [
                'test' => true,
                'message' => 'This is a test webhook',
                'timestamp' => time()
            ];
            
            // Convert to array format expected by service
            $webhookArray = (array)$webhook;
            $success = $webhookService->sendWebhook($webhookArray, 'test.event', $testData);
            
            $response = [
                'success' => $success,
                'message' => $success ? 'Webhook sent successfully' : 'Webhook failed to send'
            ];
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        } catch (Error $e) {
            $response = [
                'success' => false,
                'message' => 'Fatal Error: ' . $e->getMessage()
            ];
        }
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    /**
     * Get webhook logs
     */
    public function webhook_logs($webhook_id)
    {
        \modules\api\core\Apiinit::the_da_vinci_code('api');
        
        $data['title'] = _l('webhook_logs');
        $data['webhook'] = $this->api_model->get_webhook($webhook_id);
        $data['logs'] = $this->api_model->get_webhook_logs($webhook_id);
        
        $this->load->view('webhook_logs', $data);
    }

    /**
     * Generate and download Postman collection
     * Follows the exact same pattern as other admin methods (webhooks, settings, etc.)
     */
    public function generate_postman_collection()
    {
        // Ensure we're authenticated (like other admin methods)
        if (!is_admin()) {
            access_denied('Postman Export');
        }
        
        \modules\api\core\Apiinit::the_da_vinci_code('api');
        
        // Clear any output buffering
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Get API key from parameter - use CodeIgniter input normally like other methods
        $apiKey = $this->input->get('api_key') ?: '';
        
        // Get base URL - use CodeIgniter base_url() helper normally like other methods
        $baseUrl = base_url('api/');
        
        // Load and generate collection
        $generatorFile = __DIR__ . '/../libraries/Postman_Generator.php';
        if (!file_exists($generatorFile)) {
            show_error('Postman Generator library not found', 500);
            return;
        }
        
        require_once $generatorFile;
        $generator = new Postman_Generator($baseUrl, $apiKey);
        $generator->exportCollection();
    }
    
    /**
     * Alias method for route compatibility
     */
    public function generate_postman()
    {
        $this->generate_postman_collection();
    }
    
    /**
     * Generate connector manifest
     */
    public function generate_connector_manifest($platform = 'zapier')
    {
        \modules\api\core\Apiinit::the_da_vinci_code('api');
        
        // Validate platform
        $validPlatforms = ['zapier', 'make', 'n8n'];
        if (!in_array(strtolower($platform), $validPlatforms)) {
            // Clear output and send JSON error instead of show_error() to avoid Session
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid platform. Valid platforms: ' . implode(', ', $validPlatforms)]);
            exit;
        }
        
        // Clear any output buffering completely
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Disable output buffering
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Build base URL manually (avoid CodeIgniter base_url() which might need session)
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
                    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) 
                    ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $scriptPath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
        $baseUrl = $protocol . $host . rtrim($scriptPath, '/') . '/api/';
        
        // Load manifest generator
        $generatorFile = __DIR__ . '/../libraries/Connector_Manifest_Generator.php';
        if (!file_exists($generatorFile)) {
            // Clear output and send JSON error instead of show_error() to avoid Session
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="error.json"');
            echo json_encode(['error' => 'Connector Manifest Generator library not found']);
            exit;
        }
        
        require_once $generatorFile;
        $generator = new Connector_Manifest_Generator($baseUrl);
        
        // Ensure no output before this point - critical for proper file download
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Disable any output compression that might interfere
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', 1);
        }
        @ini_set('zlib.output_compression', 0);
        
        // Call export which will set headers and exit
        $generator->exportManifest($platform);
    }
    
    /**
     * Connector setup page (renamed to avoid conflicts)
     */
    public function automation_connectors()
    {
        \modules\api\core\Apiinit::the_da_vinci_code('api');
        
        // Build base URL manually to avoid any potential Session loading issues
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
                    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) 
                    ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $scriptPath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
        $baseUrl = $protocol . $host . rtrim($scriptPath, '/') . '/api/';
        
        $data['title'] = _l('automation_connectors');
        $data['base_url'] = $baseUrl;
        $this->load->view('connectors', $data);
    }
    
    /**
     * Alias for automation_connectors (backwards compatibility)
     */
    public function connectors()
    {
        $this->automation_connectors();
    }
    
    /**
     * Block api/connectors from being accessed directly (without /admin/)
     * This prevents it from matching the generic route
     */
    public function connectors_blocked()
    {
        // If accessed via /api/connectors (without /admin/), redirect to admin route
        // Build admin URL manually to avoid Session loading
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
                    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) 
                    ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $scriptPath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
        $adminUrl = $protocol . $host . rtrim($scriptPath, '/') . '/admin/api/connectors';
        header('Location: ' . $adminUrl);
        exit;
    }
}