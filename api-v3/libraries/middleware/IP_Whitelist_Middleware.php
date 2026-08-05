<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once __DIR__ . '/../Api_Middleware.php';

/**
 * IP Whitelist Middleware
 * 
 * Allows only specified IP addresses to access the API
 */
class IP_Whitelist_Middleware implements Api_Middleware_Interface
{
    private $allowed_ips = [];
    
    public function __construct($allowed_ips = [])
    {
        $this->allowed_ips = is_array($allowed_ips) ? $allowed_ips : [];
    }
    
    public function handle($request, $next)
    {
        // If no IPs configured, allow all
        if (empty($this->allowed_ips)) {
            return $next($request);
        }
        
        // Get client IP
        $CI =& get_instance();
        $client_ip = $CI->input->ip_address();
        
        // Check if IP is whitelisted
        $allowed = false;
        foreach ($this->allowed_ips as $allowed_ip) {
            $allowed_ip = trim($allowed_ip);
            if (empty($allowed_ip)) {
                continue;
            }
            
            // Support CIDR notation (e.g., 192.168.1.0/24)
            if (strpos($allowed_ip, '/') !== false) {
                if ($this->ipInRange($client_ip, $allowed_ip)) {
                    $allowed = true;
                    break;
                }
            } else {
                // Exact match or wildcard
                if ($client_ip === $allowed_ip || $allowed_ip === '*') {
                    $allowed = true;
                    break;
                }
            }
        }
        
        if (!$allowed) {
            $request->response = [
                'status' => false,
                'message' => 'Your IP address is not authorized to access this API'
            ];
            $request->response_code = REST_Controller::HTTP_FORBIDDEN;
            $request->skip_controller = true;
            return $request;
        }
        
        return $next($request);
    }
    
    /**
     * Check if IP is in CIDR range
     */
    private function ipInRange($ip, $range)
    {
        list($subnet, $mask) = explode('/', $range);
        $ip_long = ip2long($ip);
        $subnet_long = ip2long($subnet);
        $mask_long = -1 << (32 - (int)$mask);
        
        return ($ip_long & $mask_long) === ($subnet_long & $mask_long);
    }
}
