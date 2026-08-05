<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API Event Manager
 * 
 * Manages API lifecycle events and integrates with Perfex hooks system
 */
class Api_Event_Manager
{
    /**
     * Available API events
     */
    const EVENT_REQUEST_RECEIVED = 'api.request.received';
    const EVENT_BEFORE_CONTROLLER = 'api.before.controller';
    const EVENT_AFTER_CONTROLLER = 'api.after.controller';
    const EVENT_BEFORE_RESPONSE = 'api.before.response';
    const EVENT_RESPONSE_SENT = 'api.response.sent';
    const EVENT_ERROR_OCCURRED = 'api.error.occurred';
    const EVENT_RATE_LIMIT_EXCEEDED = 'api.rate_limit.exceeded';
    const EVENT_AUTHENTICATION_FAILED = 'api.auth.failed';
    const EVENT_AUTHENTICATION_SUCCESS = 'api.auth.success';
    
    /**
     * Fire an API event
     * 
     * @param string $event Event name
     * @param array $data Event data
     * @return void
     */
    public static function fire($event, $data = [])
    {
        // Integrate with Perfex hooks system
        if (function_exists('hooks')) {
            hooks()->do_action($event, $data);
        }
        
        // Trigger webhooks automatically
        if (!class_exists('Api_Webhook_Service')) {
            $webhookServiceFile = __DIR__ . '/Api_Webhook_Service.php';
            if (file_exists($webhookServiceFile)) {
                require_once $webhookServiceFile;
            }
        }
        
        if (class_exists('Api_Webhook_Service')) {
            $webhookService = new Api_Webhook_Service();
            $webhookService->triggerWebhooks($event, $data);
        }
    }
    
    /**
     * Register event listener
     * 
     * @param string $event Event name
     * @param callable $callback Callback function
     * @param int $priority Priority (lower = earlier execution)
     * @return void
     */
    public static function listen($event, $callback, $priority = 10)
    {
        if (function_exists('hooks')) {
            hooks()->add_action($event, $callback, $priority);
        }
    }
    
    /**
     * Get event data structure
     * 
     * @param object $controller Controller instance
     * @param string $method Method name
     * @return array Event data
     */
    public static function getEventData($controller, $method = '')
    {
        $request = isset($controller->request) ? $controller->request : null;
        $rest = isset($controller->rest) ? $controller->rest : null;
        
        return [
            'timestamp' => time(),
            'controller' => get_class($controller),
            'method' => $method,
            'request_method' => isset($request->method) ? $request->method : null,
            'uri' => isset($controller->uri) ? $controller->uri->uri_string() : null,
            'api_key' => isset($rest->key) ? $rest->key : null,
            'user_id' => isset($rest->user_id) ? $rest->user_id : null,
            'ip_address' => isset($controller->input) ? $controller->input->ip_address() : null,
            'user_agent' => isset($controller->input) ? $controller->input->user_agent() : null,
            'args' => isset($controller->_args) ? $controller->_args : []
        ];
    }
}
