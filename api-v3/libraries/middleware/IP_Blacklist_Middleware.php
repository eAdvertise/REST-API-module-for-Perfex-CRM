<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once __DIR__ . '/../Api_Middleware.php';

/**
 * IP Blacklist Middleware
 * 
 * Blocks specified IP addresses from accessing the API
 */
class IP_Blacklist_Middleware implements Api_Middleware_Interface
{
    private $blocked_ips = [];
    
    public function __construct($blocked_ips = [])
    {
        $this->blocked_ips = is_array($blocked_ips) ? $blocked_ips : [];
    }
    
    public function handle($request, $next)
    {
        // If no IPs configured, allow all
        if (empty($this->blocked_ips)) {
            return $next($request);
        }
        
        // Get client IP
        $CI =& get_instance();
        $client_ip = $CI->input->ip_address();
        
        // Check if IP is blacklisted
        $blocked = false;
        foreach ($this->blocked_ips as $blocked_ip) {
            $blocked_ip = trim($blocked_ip);
            if (empty($blocked_ip)) {
                continue;
            }
            
            // Support CIDR notation (e.g., 192.168.1.0/24)
            if (strpos($blocked_ip, '/') !== false) {
                if ($this->ipInRange($client_ip, $blocked_ip)) {
                    $blocked = true;
                    break;
                }
            } else {
                // Exact match
                if ($client_ip === $blocked_ip) {
                    $blocked = true;
                    break;
                }
            }
        }
        
        if ($blocked) {
            $request->response = [
                'status' => false,
                'message' => 'Your IP address has been blocked from accessing this API'
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
