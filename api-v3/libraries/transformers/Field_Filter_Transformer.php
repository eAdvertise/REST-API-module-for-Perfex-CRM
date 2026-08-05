<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once __DIR__ . '/../Api_Transformer.php';

/**
 * Field Filter Transformer
 * 
 * Filters response fields based on 'fields' query parameter
 * Usage: ?fields=id,name,email
 */
class Field_Filter_Transformer extends Api_Transformer
{
    public function shouldTransform($context = [])
    {
        // Only transform if 'fields' parameter is present
        return isset($context['args']['fields']) && !empty($context['args']['fields']);
    }
    
    public function transform($data, $context = [])
    {
        $fields = explode(',', $context['args']['fields']);
        $fields = array_map('trim', $fields);
        $fields = array_filter($fields); // Remove empty values
        
        if (empty($fields)) {
            return $data;
        }
        
        if (is_array($data)) {
            return $this->filterArray($data, $fields);
        } elseif (is_object($data)) {
            return $this->filterObject($data, $fields);
        }
        
        return $data;
    }
    
    private function filterArray($data, $fields)
    {
        // Check if it's an array of objects/arrays
        if (isset($data[0]) && (is_array($data[0]) || is_object($data[0]))) {
            // Array of objects
            return array_map(function($item) use ($fields) {
                if (is_object($item)) {
                    return $this->filterObject($item, $fields);
                }
                return array_intersect_key($item, array_flip($fields));
            }, $data);
        }
        
        // Single array
        return array_intersect_key($data, array_flip($fields));
    }
    
    private function filterObject($data, $fields)
    {
        $result = new stdClass();
        foreach ($fields as $field) {
            if (isset($data->$field)) {
                $result->$field = $data->$field;
            }
        }
        return $result;
    }
}
