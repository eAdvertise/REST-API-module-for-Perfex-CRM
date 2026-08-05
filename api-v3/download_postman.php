<?php
/**
 * Standalone Postman Collection Download
 * Bypasses CodeIgniter entirely to avoid Session loading issues
 * Access via: /modules/api/download_postman.php?api_key=YOUR_KEY
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Get API key from query parameter
$apiKey = isset($_GET['api_key']) ? $_GET['api_key'] : '';

// Build base URL manually
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) 
            ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
// Get script path - remove index.php and modules/api/download_postman.php to get root
$scriptPath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
$scriptPath = str_replace('/modules/api/download_postman.php', '', $scriptPath);
$scriptPath = rtrim($scriptPath, '/');
$baseUrl = $protocol . $host . $scriptPath . '/api/';

// Load Postman Generator
$generatorFile = __DIR__ . '/libraries/Postman_Generator.php';
if (!file_exists($generatorFile)) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Postman Generator library not found',
        'path' => $generatorFile,
        'dir' => __DIR__
    ]);
    exit;
}

// Bootstrap minimal CodeIgniter for Postman_Generator if needed
// But don't load Session
if (!defined('BASEPATH')) {
    define('BASEPATH', true);
}

// Try to load the generator with error handling
try {
    require_once $generatorFile;
    
    // Check if class exists
    if (!class_exists('Postman_Generator')) {
        throw new Exception('Postman_Generator class not found after require');
    }
    
    // Create generator with error handling
    try {
        $generator = new Postman_Generator($baseUrl, $apiKey);
    } catch (Exception $e) {
        throw new Exception('Error creating Postman_Generator: ' . $e->getMessage());
    } catch (Error $e) {
        throw new Exception('Fatal error creating Postman_Generator: ' . $e->getMessage());
    }
    
    // Export collection with error handling
    try {
        $generator->exportCollection();
    } catch (Exception $e) {
        throw new Exception('Error exporting collection: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    } catch (Error $e) {
        throw new Exception('Fatal error exporting collection: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    }
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit;
} catch (Error $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Fatal Error: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit;
}
