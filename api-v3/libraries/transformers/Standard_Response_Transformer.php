<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once __DIR__ . '/../Api_Transformer.php';

/**
 * Standard Response Transformer
 * 
 * Wraps response in standard format with metadata
 */
class Standard_Response_Transformer extends Api_Transformer
{
    public function transform($data, $context = [])
    {
        // Don't wrap if already in standard format
        if (is_array($data) && isset($data['status']) && isset($data['data'])) {
            return $data;
        }
        
        // Wrap response in standard format
        return [
            'status' => true,
            'data' => $data,
            'meta' => [
                'timestamp' => time(),
                'version' => '1.0',
                'controller' => $context['controller'] ?? null,
                'method' => $context['method'] ?? null
            ]
        ];
    }
}
