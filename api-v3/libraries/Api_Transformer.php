<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base API Transformer Class
 * 
 * Transformers modify API responses before they are sent to clients
 */
abstract class Api_Transformer
{
    /**
     * Transform the response data
     * 
     * @param mixed $data The response data
     * @param array $context Context information (controller, method, request, etc.)
     * @return mixed Transformed data
     */
    abstract public function transform($data, $context = []);
    
    /**
     * Check if this transformer should be applied
     * 
     * @param array $context Context information
     * @return bool
     */
    public function shouldTransform($context = [])
    {
        return true;
    }
}