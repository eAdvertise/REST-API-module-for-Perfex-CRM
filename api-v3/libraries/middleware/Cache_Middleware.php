<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once __DIR__ . '/../Api_Middleware.php';

/**
 * Cache Middleware
 * 
 * Caches GET requests for improved performance
 */
class Cache_Middleware implements Api_Middleware_Interface
{
    private $cache_ttl = 300; // 5 minutes default
    
    public function __construct($cache_ttl = 300)
    {
        $this->cache_ttl = (int)$cache_ttl;
    }
    
    public function handle($request, $next)
    {
        // Only cache GET requests
        if (!isset($request->headers['REQUEST_METHOD']) || 
            strtoupper($request->headers['REQUEST_METHOD']) !== 'GET') {
            return $next($request);
        }
        
        // Generate cache key
        $cache_key = 'api_cache_' . md5($request->controller . $request->method . json_encode($request->args));
        
        // Try to get from cache
        $CI =& get_instance();
        $cached = $CI->cache->get($cache_key);
        
        if ($cached !== false) {
            // Return cached response
            $request->response = $cached;
            $request->response_code = REST_Controller::HTTP_OK;
            $request->skip_controller = true;
            return $request;
        }
        
        // Continue to controller
        $response = $next($request);
        
        // Cache the response if successful
        if (isset($response->response) && isset($response->response_code) && 
            $response->response_code >= 200 && $response->response_code < 300) {
            $CI->cache->save($cache_key, $response->response, $this->cache_ttl);
        }
        
        return $response;
    }
}