<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once __DIR__ . '/../Api_Transformer.php';

/**
 * Pagination Transformer
 * 
 * Adds pagination metadata to array responses
 */
class Pagination_Transformer extends Api_Transformer
{
    public function shouldTransform($context = [])
    {
        // Only transform if data is an array and pagination params exist
        return isset($context['args']['page']) || isset($context['args']['limit']);
    }
    
    public function transform($data, $context = [])
    {
        if (!is_array($data) || empty($data)) {
            return $data;
        }
        
        $page = isset($context['args']['page']) ? (int)$context['args']['page'] : 1;
        $limit = isset($context['args']['limit']) ? (int)$context['args']['limit'] : 20;
        $total = count($data);
        
        // If data is already paginated, don't modify
        if (isset($data['data']) && isset($data['pagination'])) {
            return $data;
        }
        
        return [
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ];
    }
}
