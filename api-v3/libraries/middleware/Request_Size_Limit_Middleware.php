<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once __DIR__ . '/../Api_Middleware.php';

/**
 * Request Size Limit Middleware
 * 
 * Limits the maximum size of request body
 */
class Request_Size_Limit_Middleware implements Api_Middleware_Interface
{
    private $max_size_bytes = 10485760; // 10MB default
    
    public function __construct($max_size_mb = 10)
    {
        $this->max_size_bytes = (int)$max_size_mb * 1048576; // Convert MB to bytes
    }
    
    public function handle($request, $next)
    {
        // Check Content-Length header
        $content_length = isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : 0;
        
        if ($content_length > $this->max_size_bytes) {
            $request->response = [
                'status' => false,
                'message' => 'Request body exceeds maximum allowed size of ' . ($this->max_size_bytes / 1048576) . 'MB'
            ];
            $request->response_code = REST_Controller::HTTP_REQUEST_ENTITY_TOO_LARGE;
            $request->skip_controller = true;
            return $request;
        }
        
        // Also check actual input size if available
        $input_size = strlen(file_get_contents('php://input'));
        if ($input_size > $this->max_size_bytes) {
            $request->response = [
                'status' => false,
                'message' => 'Request body exceeds maximum allowed size of ' . ($this->max_size_bytes / 1048576) . 'MB'
            ];
            $request->response_code = REST_Controller::HTTP_REQUEST_ENTITY_TOO_LARGE;
            $request->skip_controller = true;
            return $request;
        }
        
        return $next($request);
    }
}
