<?php
namespace modules\api\scontrollers;

defined('BASEPATH') or exit('No direct script access allowed');

// Load the core Controller
require_once APPPATH.'core/Controller.php';

/**
 * Base API Controller with Swagger support
 */
class Controller extends \CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function send_error($message, $code = 400)
    {
        $this->output
            ->set_status_header($code)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'error' => $message,
                'code' => $code
            ]));
    }
}