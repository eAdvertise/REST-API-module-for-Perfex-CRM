<?php
/**
 * Standalone Manifest Download Script
 * 
 * Bypasses CodeIgniter entirely to avoid HTML wrapping and Session issues
 * Similar to download_postman.php
 * 
 * IMPORTANT: This file must be accessed directly, NOT through CodeIgniter routing
 * Access via: /modules/api/download_manifest.php?platform=zapier
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// IMPORTANT: Do NOT define BASEPATH - this file bypasses CodeIgniter entirely
// If BASEPATH is defined, CodeIgniter will block access with "No direct script access allowed"

// Start output buffering to catch any errors
ob_start();

// Debug logging
$debug_log = [];
$debug_log[] = "=== Manifest Download Debug ===";
$debug_log[] = "GET params: " . print_r($_GET, true);
$debug_log[] = "SERVER PATH_INFO: " . (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : 'not set');
$debug_log[] = "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'];
$debug_log[] = "REQUEST_URI: " . $_SERVER['REQUEST_URI'];

// Get platform from query string
$platform = isset($_GET['platform']) ? $_GET['platform'] : (isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : 'zapier');
$debug_log[] = "Platform detected: " . $platform;

// Validate platform
$validPlatforms = ['zapier', 'make', 'n8n'];
if (!in_array(strtolower($platform), $validPlatforms)) {
    header('Content-Type: application/json');
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Invalid platform. Valid platforms: ' . implode(', ', $validPlatforms)]);
    exit;
}

try {
    // Calculate base path (assuming this file is in modules/api/)
    $basePath = __DIR__;
    $generatorFile = $basePath . '/libraries/Connector_Manifest_Generator.php';
    $debug_log[] = "Base path: " . $basePath;
    $debug_log[] = "Generator file path: " . $generatorFile;
    $debug_log[] = "Generator file exists: " . (file_exists($generatorFile) ? 'YES' : 'NO');
    
    if (!file_exists($generatorFile)) {
        // Clear output and show debug info
        ob_end_clean();
        header('Content-Type: application/json');
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode([
            'error' => 'Connector Manifest Generator library not found',
            'debug' => [
                'base_path' => $basePath,
                'generator_file' => $generatorFile,
                'file_exists' => file_exists($generatorFile),
                'debug_log' => $debug_log
            ]
        ]);
        exit;
    }
    
    $debug_log[] = "Loading generator file...";
    
    // Define BASEPATH before loading generator (it requires it)
    // We'll use a dummy path since we're bypassing CodeIgniter
    if (!defined('BASEPATH')) {
        define('BASEPATH', $basePath . '/../../');
    }
    $debug_log[] = "BASEPATH defined: " . (defined('BASEPATH') ? BASEPATH : 'NOT DEFINED');
    
    require_once $generatorFile;
    $debug_log[] = "Generator file loaded successfully";
    
    // Build base URL manually
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) 
                ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $scriptPath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
    $baseUrl = $protocol . $host . rtrim(dirname($scriptPath), '/') . '/api/';
    $debug_log[] = "Base URL: " . $baseUrl;
    
    // Create generator instance
    $debug_log[] = "Creating generator instance...";
    $generator = new Connector_Manifest_Generator($baseUrl);
    $debug_log[] = "Generator instance created";
    
    // Clear ALL output buffering
    $ob_levels = ob_get_level();
    $debug_log[] = "Output buffer levels before cleanup: " . $ob_levels;
    while (ob_get_level()) {
        ob_end_clean();
    }
    $debug_log[] = "Output buffers cleared";
    
    // Disable compression
    if (function_exists('apache_setenv')) {
        @apache_setenv('no-gzip', 1);
    }
    @ini_set('zlib.output_compression', 0);
    
    // Check for any output before headers
    $output_before = ob_get_contents();
    if (!empty($output_before)) {
        $debug_log[] = "WARNING: Output detected before headers: " . substr($output_before, 0, 200);
    }
    
    $debug_log[] = "Calling exportManifest for platform: " . $platform;
    
    // Export manifest (this will set headers and exit)
    $generator->exportManifest($platform);
    
    // If we get here, something went wrong
    ob_end_clean();
    header('Content-Type: application/json');
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'error' => 'exportManifest did not exit properly',
        'debug' => $debug_log
    ]);
    exit;
    
} catch (Exception $e) {
    // Clear output and send error
    $output_before_error = ob_get_contents();
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json');
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'error' => 'Fatal error exporting manifest: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'debug' => $debug_log,
        'output_before_error' => $output_before_error
    ]);
    exit;
} catch (Error $e) {
    // Clear output and send error
    $output_before_error = ob_get_contents();
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json');
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'error' => 'Fatal error exporting manifest: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'debug' => $debug_log,
        'output_before_error' => $output_before_error
    ]);
    exit;
}
