<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Postman Collection Export Controller (SECURED)
 * SECURITY UPDATE: Now requires admin authentication
 * This controller generates Postman collections for API testing
 */
class Postman extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        
        // SECURITY CHECK: Require admin authentication
        if (!is_admin()) {
            access_denied('Postman Collection Export');
        }
        
        // Verify API module is active
        $this->load->library('app_modules');
        if (!$this->app_modules->is_active('api')) {
            access_denied('API Module');
        }
    }
    
    /**
     * Generate and download Postman collection
     * SECURITY: Requires admin authentication (enforced in constructor)
     */
    public function download()
    {
        // Clear any output buffering
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Get API key from GET parameter using CodeIgniter input class
        $apiKey = $this->input->get('api_key') ?: '';
        
        // Use CodeIgniter base_url() helper
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
}
