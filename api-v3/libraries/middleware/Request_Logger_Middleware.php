<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once __DIR__ . '/../Api_Middleware.php';

/**
 * Request Logger Middleware
 * 
 * Logs all API requests and responses
 */
class Request_Logger_Middleware implements Api_Middleware_Interface
{
    public function handle($request, $next)
    {
        // Log request before processing
        log_message('debug', 'API Request: ' . $request->controller . '::' . $request->method);
        log_message('debug', 'API Args: ' . json_encode($request->args));
        
        // Call next middleware
        $response = $next($request);
        
        // Log response after processing
        if (isset($response->response)) {
            log_message('debug', 'API Response: ' . json_encode($response->response));
        }
        
        return $response;
    }
}