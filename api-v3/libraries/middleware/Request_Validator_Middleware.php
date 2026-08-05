<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once __DIR__ . '/../Api_Middleware.php';

/**
 * Request Validator Middleware
 * 
 * Validates required parameters before controller execution
 */
class Request_Validator_Middleware implements Api_Middleware_Interface
{
    private $required_params = [];
    
    public function __construct($required_params = [])
    {
        $this->required_params = $required_params;
    }
    
    public function handle($request, $next)
    {
        if (empty($this->required_params)) {
            return $next($request);
        }
        
        $missing = [];
        foreach ($this->required_params as $param) {
            if (!isset($request->args[$param]) || empty($request->args[$param])) {
                $missing[] = $param;
            }
        }
        
        if (!empty($missing)) {
            $request->response = [
                'status' => false,
                'message' => 'Missing required parameters: ' . implode(', ', $missing)
            ];
            $request->response_code = REST_Controller::HTTP_BAD_REQUEST;
            $request->skip_controller = true;
            return $request;
        }
        
        return $next($request);
    }
}