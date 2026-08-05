<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Authorization_Token
 * ----------------------------------------------------------
 * API Token Generate/Validation
 * 
 */
require_once __DIR__.'/../vendor/autoload.php';
use Firebase\JWT\JWT as api_JWT;
use Firebase\JWT\Key as api_Key;
#[\AllowDynamicProperties]
class Authorization_Token 
{
    /**
     * Token Key
     */
    protected $token_key;

    /**
     * Token algorithm
     */
    protected $token_algorithm;

    /**
     * Token Request Header Name
     */
    protected $token_header;

    /**
     * Token Expire Time
     * ------------------
     * Default: 315569260 (~10 years) for backwards compatibility
     * Users should set appropriate expiration times when creating tokens
     * 
     * (1 day) : 60 * 60 * 24 = 86400
     * (1 hour) : 60 * 60 = 3600
     */
    protected $token_expire_time = 315569260; // ~10 years (default) 


    public function __construct()
	{
        $this->CI =& get_instance();

        /** 
         * jwt config file load
         */
        $this->CI->load->config('jwt');

        /**
         * Load Config Items Values 
         */
        $this->token_key        = $this->CI->config->item('jwt_key');
        $this->token_algorithm  = $this->CI->config->item('jwt_algorithm');
        $this->token_header  = $this->CI->config->item('token_header');
        $this->token_expire_time  = $this->CI->config->item('token_expire_time');
    }

    /**
     * Generate Token
     * @param: {array} data
     */
    public function generateToken($data = null)
    {
        if ($data AND is_array($data))
        {
            // add api time key in user array()
            $data['API_TIME'] = time();

            try {
                return api_JWT::encode($data, $this->token_key, $this->token_algorithm);
            }
            catch(Exception $e) {
                return 'Message: ' .$e->getMessage();
            }
        } else {
            return "Token Data Undefined!";
        }
    }


    public function get_token()
    {
        /**
         * Request All Headers
         */
        $headers = $this->CI->input->request_headers();
        
        /**
         * Authorization Header Exists
         */
        $token = $this->token($headers);
        
        // If token not found in headers, check query parameters
        if ($token === 'Token is not defined.' || empty($token)) {
            // Check query parameters directly - authtoken first (primary), then api_key (backwards compatibility)
            if (isset($_GET['authtoken']) && !empty($_GET['authtoken'])) {
                return $_GET['authtoken'];
            } elseif (isset($_GET['Authtoken']) && !empty($_GET['Authtoken'])) {
                return $_GET['Authtoken'];
            } elseif (isset($_GET['api_key']) && !empty($_GET['api_key'])) {
                // Backwards compatibility with api_key parameter
                return $_GET['api_key'];
            }
        }
        
        return $token;
    }
    /**
     * Validate Token with Header
     * @return : user informations
     */
    public function validateToken()
    {
        /**
         * Request All Headers
         */
        $headers = $this->CI->input->request_headers();
        
        /**
         * Authorization Header Exists
         */
        $token_data = $this->tokenIsExist($headers);
        if($token_data['status'] === TRUE)
        {
            try
            {
                /**
                 * Token Decode
                 */
                try {
                    $token_decode = api_JWT::decode($token_data['token'], new api_Key($this->token_key, $this->token_algorithm));
                }
                catch(Exception $e) {
                    return ['status' => FALSE, 'message' => $e->getMessage()];
                }

                if(!empty($token_decode) AND is_object($token_decode))
                {
                    // Check Token API Time [API_TIME]
                    if (empty($token_decode->API_TIME OR !is_numeric($token_decode->API_TIME))) {
                        
                        return ['status' => FALSE, 'message' => 'Token Time Not Define!'];
                    }
                    else
                    {
                        /**
                         * Check Token Time Valid 
                         */
                        $time_difference = strtotime('now') - $token_decode->API_TIME;
                        if( $time_difference >= $this->token_expire_time )
                        {
                            return ['status' => FALSE, 'message' => 'Token Time Expire.'];

                        }else
                        {
                            /**
                             * All Validation False Return Data
                             */
                            return ['status' => TRUE, 'data' => $token_decode];
                        }
                    }
                    
                }else{
                    return ['status' => FALSE, 'message' => 'Forbidden'];
                }
            }
            catch(Exception $e) {
                return ['status' => FALSE, 'message' => $e->getMessage()];
            }
        }else
        {
            // Authorization Header Not Found!
            return ['status' => FALSE, 'message' => $token_data['message'] ];
        }
    }

    /**
     * Token Header Check
     * @param: request headers
     */
    private function tokenIsExist($headers)
    {
        if(!empty($headers) AND is_array($headers)) {
            foreach ($headers as $header_name => $header_value) {
                // Check for configured token header name (usually 'authtoken')
                if (strtolower(trim($header_name)) == strtolower(trim($this->token_header)) && $header_value)
                    return ['status' => TRUE, 'token' => $header_value];
                
                // Also check Authorization header for Bearer token (for automation platforms)
                if (strtolower(trim($header_name)) == 'authorization' && !empty($header_value)) {
                    // Extract Bearer token if present
                    if (preg_match('/Bearer\s+(.*)$/i', $header_value, $matches)) {
                        return ['status' => TRUE, 'token' => trim($matches[1])];
                    }
                    // If no Bearer prefix, use the value directly
                    return ['status' => TRUE, 'token' => trim($header_value)];
                }
            }
        }
        
        // Fallback: Check query parameters for api_key or Authtoken (for automation platforms like Zapier)
        // Use $_GET directly to avoid dependency on CodeIgniter input class initialization
        $queryToken = null;
        if (isset($_GET['api_key']) && !empty($_GET['api_key'])) {
            $queryToken = $_GET['api_key'];
        } elseif (isset($_GET['Authtoken']) && !empty($_GET['Authtoken'])) {
            $queryToken = $_GET['Authtoken'];
        } elseif (isset($_GET['authtoken']) && !empty($_GET['authtoken'])) {
            $queryToken = $_GET['authtoken'];
        }
        
        if (!empty($queryToken)) {
            return ['status' => TRUE, 'token' => $queryToken];
        }
        
        return ['status' => FALSE, 'message' => 'Token is not defined.'];
    }

    private function token($headers)
    {
        if(!empty($headers) AND is_array($headers)) {
            foreach ($headers as $header_name => $header_value) {
                // Check for configured token header name (usually 'authtoken')
                if (strtolower(trim($header_name)) == strtolower(trim($this->token_header)))
                    return $header_value;
                
                // Also check Authorization header for Bearer token (for automation platforms)
                if (strtolower(trim($header_name)) == 'authorization' && !empty($header_value)) {
                    // Extract Bearer token if present
                    if (preg_match('/Bearer\s+(.*)$/i', $header_value, $matches)) {
                        return trim($matches[1]);
                    }
                    // If no Bearer prefix, use the value directly
                    return trim($header_value);
                }
            }
        }
        
        // Fallback: Check query parameters for authtoken (primary) or api_key (backwards compatibility)
        // Use $_GET directly to avoid dependency on CodeIgniter input class initialization
        $queryToken = null;
        if (isset($_GET['authtoken']) && !empty($_GET['authtoken'])) {
            $queryToken = $_GET['authtoken'];
        } elseif (isset($_GET['Authtoken']) && !empty($_GET['Authtoken'])) {
            $queryToken = $_GET['Authtoken'];
        } elseif (isset($_GET['api_key']) && !empty($_GET['api_key'])) {
            // Backwards compatibility with api_key parameter
            $queryToken = $_GET['api_key'];
        }
        
        if (!empty($queryToken)) {
            return $queryToken;
        }
        
        return 'Token is not defined.';
    }
}