<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once __DIR__ . '/../Api_Transformer.php';

/**
 * Privacy Transformer
 * 
 * Removes sensitive fields from responses
 */
class Privacy_Transformer extends Api_Transformer
{
    private $sensitive_fields = ['password', 'token', 'secret', 'api_key', 'private_key'];
    
    public function __construct($sensitive_fields = [])
    {
        if (!empty($sensitive_fields)) {
            $this->sensitive_fields = $sensitive_fields;
        }
    }
    
    public function transform($data, $context = [])
    {
        if (is_array($data)) {
            return $this->filterArray($data);
        } elseif (is_object($data)) {
            return $this->filterObject($data);
        }
        
        return $data;
    }
    
    private function filterArray($data)
    {
        // Check if it's an array of objects/arrays
        if (isset($data[0]) && (is_array($data[0]) || is_object($data[0]))) {
            return array_map([$this, 'filterItem'], $data);
        }
        
        // Single array
        return $this->filterItem($data);
    }
    
    private function filterObject($data)
    {
        return $this->filterItem($data);
    }
    
    private function filterItem($item)
    {
        if (is_object($item)) {
            foreach ($this->sensitive_fields as $field) {
                if (isset($item->$field)) {
                    unset($item->$field);
                }
            }
        } elseif (is_array($item)) {
            foreach ($this->sensitive_fields as $field) {
                if (isset($item[$field])) {
                    unset($item[$field]);
                }
            }
        }
        
        return $item;
    }
}
