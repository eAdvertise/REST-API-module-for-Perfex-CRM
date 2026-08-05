<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require __DIR__ . '/API_Controller.php';

class Login extends API_Controller {
    public function __construct() {
        parent::__construct();
        
        // SECURITY FIX: Respect CORS configuration instead of hardcoded wildcard
        $this->handle_cors();
    }
    
    /**
     * Handle CORS headers properly based on configuration
     * SECURITY: Removed hardcoded "Access-Control-Allow-Origin: *"
     */
    private function handle_cors() {
        $this->load->config('rest');
        
        if ($this->config->item('check_cors') === TRUE) {
            $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
            
            if ($this->config->item('allow_any_cors_domain') === TRUE) {
                header("Access-Control-Allow-Origin: *");
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
                header("Access-Control-Allow-Headers: Content-Type, Authorization, Authtoken");
            } elseif (!empty($origin)) {
                $allowed_origins = $this->config->item('allowed_cors_origins');
                if (is_array($allowed_origins) && in_array($origin, $allowed_origins)) {
                    header("Access-Control-Allow-Origin: " . $origin);
                    header("Access-Control-Allow-Credentials: true");
                    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
                    header("Access-Control-Allow-Headers: Content-Type, Authorization, Authtoken");
                }
            }
        }
    }

    public function login_api() {
        // SECURITY: Removed hardcoded CORS header - now handled in constructor
        // API Configuration
        $this->_apiConfig(['methods' => ['POST'], ]);
        // you user authentication code will go here, you can compare the user with the database or whatever
        $payload = ['id' => "Your User's ID", 'other' => "Some other data"];
        // Load Authorization Library or Load in autoload config file
        $this->load->library('authorization_token');
        // generate a token
        $token = $this->authorization_token->generateToken($payload);
        // return data
        $this->api_return(['status' => true, "result" => ['token' => $token, ], ], 200);
    }

    /**
     * view method
     *
     * @link [api/user/view]
     * @method POST
     * @return Response|void
     */
    public function view() {
        // SECURITY: Removed hardcoded CORS header - now handled in constructor
        // API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig(['methods' => ['POST'], 'requireAuthorization' => true, ]);
        // return data
        $this->api_return(['status' => true, "result" => ['user_data' => $user_data['token_data']], ], 200);
    }
    
    public function api_key() {
        $this->_APIConfig(['methods' => ['POST'], 'key' => ['header', 'Set API Key'], ]);
    }
}
