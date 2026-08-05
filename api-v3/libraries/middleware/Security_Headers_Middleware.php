<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once __DIR__ . '/../Api_Middleware.php';

/**
 * Security Headers Middleware
 * 
 * Adds security headers to API responses
 */
class Security_Headers_Middleware implements Api_Middleware_Interface
{
    private $enabled = true;
    
    public function __construct($enabled = true)
    {
        $this->enabled = (bool)$enabled;
    }
    
    public function handle($request, $next)
    {
        if (!$this->enabled) {
            return $next($request);
        }
        
        // Process request
        $response = $next($request);
        
        // Add security headers
        if (!headers_sent()) {
            // Prevent clickjacking
            header('X-Frame-Options: DENY');
            
            // Prevent MIME type sniffing
            header('X-Content-Type-Options: nosniff');
            
            // Enable XSS protection
            header('X-XSS-Protection: 1; mode=block');
            
            // Strict Transport Security (HTTPS only)
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
            }
            
            // Content Security Policy
            header("Content-Security-Policy: default-src 'self'");
            
            // Referrer Policy
            header('Referrer-Policy: strict-origin-when-cross-origin');
        }
        
        return $response;
    }
}
