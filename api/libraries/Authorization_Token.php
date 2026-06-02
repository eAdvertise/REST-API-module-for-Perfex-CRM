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
     * Backward-compatible token request header aliases
     */
    protected $token_header_aliases = [];

    /**
     * Token Expire Time
     * ------------------
     * (1 day) : 60 * 60 * 24 = 86400
     * (1 hour) : 60 * 60 = 3600
     */
    protected $token_expire_time = 315569260; 


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
        $this->token_header_aliases = $this->normalize_token_header_aliases($this->CI->config->item('token_header_aliases'));
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
        return $this->token($headers);
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

    private function is_token_header($header_name)
    {
        $header_name = $this->normalize_header_name($header_name);
        if ($header_name === $this->normalize_header_name($this->token_header)) {
            return true;
        }

        foreach ($this->token_header_aliases as $alias) {
            if ($header_name === $this->normalize_header_name($alias)) {
                return true;
            }
        }

        return false;
    }

    private function normalize_header_name($header_name)
    {
        return str_replace('_', '-', strtolower(trim((string)$header_name)));
    }

    private function normalize_token_header_aliases($aliases)
    {
        if (empty($aliases)) {
            return [];
        }

        if (is_string($aliases)) {
            $aliases = explode(',', $aliases);
        }

        if (!is_array($aliases)) {
            return [];
        }

        $normalized = [];
        foreach ($aliases as $alias) {
            $alias = trim((string)$alias);
            if ($alias !== '' && $this->normalize_header_name($alias) !== $this->normalize_header_name($this->token_header)) {
                $normalized[] = $alias;
            }
        }

        return array_values(array_unique($normalized));
    }

    private function extract_token_from_header($header_name, $header_value)
    {
        if (!$this->is_token_header($header_name)) {
            return null;
        }

        $header_value = trim((string)$header_value);
        if ($header_value === '') {
            return null;
        }

        if ($this->normalize_header_name($header_name) === 'authorization' && stripos($header_value, 'bearer ') === 0) {
            return trim(substr($header_value, 7));
        }

        return $header_value;
    }

    private function find_token($headers)
    {
        if(!empty($headers) AND is_array($headers)) {
            foreach ($headers as $header_name => $header_value) {
                $token = $this->extract_token_from_header($header_name, $header_value);
                if ($token !== null) {
                    return $token;
                }
            }
        }

        foreach ($this->server_token_header_keys() as $server_key) {
            if (!empty($_SERVER[$server_key])) {
                $header_name = preg_replace('/^REDIRECT_/', '', $server_key);
                $header_name = preg_replace('/^HTTP_/', '', $header_name);
                $token = $this->extract_token_from_header(str_replace('_', '-', $header_name), $_SERVER[$server_key]);
                if ($token !== null) {
                    return $token;
                }
            }
        }

        return null;
    }

    private function server_token_header_keys()
    {
        $headers = array_merge([$this->token_header], $this->token_header_aliases);
        $keys = [];

        foreach ($headers as $header) {
            $key = strtoupper(str_replace('-', '_', trim((string)$header)));
            if ($key === '') {
                continue;
            }

            $keys[] = 'HTTP_' . $key;
            $keys[] = 'REDIRECT_HTTP_' . $key;
        }

        $keys[] = 'HTTP_AUTHORIZATION';
        $keys[] = 'REDIRECT_HTTP_AUTHORIZATION';

        return array_values(array_unique($keys));
    }

    private function tokenIsExist($headers)
    {
        $token = $this->find_token($headers);
        if ($token !== null) {
            return ['status' => TRUE, 'token' => $token];
        }

        return ['status' => FALSE, 'message' => 'Token is not defined. Send the token in the authtoken header. If you use a legacy alias, prefer rest-enable-keys because some web servers drop headers that contain underscores.'];
    }

    private function token($headers)
    {
        $token = $this->find_token($headers);
        if ($token !== null) {
            return $token;
        }

        return 'Token is not defined.';
    }
}