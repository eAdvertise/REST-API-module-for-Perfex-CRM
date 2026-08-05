<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API Middleware Interface
 * 
 * All middleware classes must implement this interface
 */
interface Api_Middleware_Interface
{
    /**
     * Handle the incoming request
     * 
     * @param object $request The request object
     * @param callable $next The next middleware in the pipeline
     * @return mixed
     */
    public function handle($request, $next);
}