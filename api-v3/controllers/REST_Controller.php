<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require_once __DIR__ . '/../libraries/Api_Event_Manager.php';

/**
 * CodeIgniter Rest Controller
 * A fully RESTful server implementation for CodeIgniter using one library, one config file and one controller.
 *
 * @package         CodeIgniter
 * @subpackage      Libraries
 * @category        Libraries
 * @version         3.0.0
 */
abstract class REST_Controller extends CI_Controller {
    // Note: Only the widely used HTTP status codes are documented
    // Informational
    const HTTP_CONTINUE = 100;
    const HTTP_SWITCHING_PROTOCOLS = 101;
    const HTTP_PROCESSING = 102; // RFC2518
    // Success
    
    /**
     * The request has succeeded
     */
    const HTTP_OK = 200;

    /**
     * The server successfully created a new resource
     */
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;

    /**
     * The server successfully processed the request, though no content is returned
     */
    const HTTP_NO_CONTENT = 204;
    const HTTP_RESET_CONTENT = 205;
    const HTTP_PARTIAL_CONTENT = 206;
    const HTTP_MULTI_STATUS = 207; // RFC4918
    const HTTP_ALREADY_REPORTED = 208; // RFC5842
    const HTTP_IM_USED = 226; // RFC3229

    // Redirection
    const HTTP_MULTIPLE_CHOICES = 300;
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_FOUND = 302;
    const HTTP_SEE_OTHER = 303;

    /**
     * The resource has not been modified since the last request
     */
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_USE_PROXY = 305;
    const HTTP_RESERVED = 306;
    const HTTP_TEMPORARY_REDIRECT = 307;
    const HTTP_PERMANENTLY_REDIRECT = 308; // RFC7238

    // Client Error
    
    /**
     * The request cannot be fulfilled due to multiple errors
     */
    const HTTP_BAD_REQUEST = 400;

    /**
     * The user is unauthorized to access the requested resource
     */
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_PAYMENT_REQUIRED = 402;

    /**
     * The requested resource is unavailable at this present time
     */
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_PERMISSION = 403;

    /**
     * The requested resource could not be found
     *
     * Note: This is sometimes used to mask if there was an UNAUTHORIZED (401) or
     * FORBIDDEN (403) error, for security reasons
     */
    const HTTP_NOT_FOUND = 404;

    /**
     * The request method is not supported by the following resource
     */
    const HTTP_METHOD_NOT_ALLOWED = 405;

    /**
     * The request was not acceptable
     */
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    const HTTP_REQUEST_TIMEOUT = 408;

    /**
     * The request could not be completed due to a conflict with the current state
     * of the resource
     */
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    const HTTP_LENGTH_REQUIRED = 411;
    const HTTP_PRECONDITION_FAILED = 412;
    const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    const HTTP_REQUEST_URI_TOO_LONG = 414;
    const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const HTTP_EXPECTATION_FAILED = 417;
    const HTTP_I_AM_A_TEAPOT = 418; // RFC2324
    const HTTP_UNPROCESSABLE_ENTITY = 422; // RFC4918
    const HTTP_LOCKED = 423; // RFC4918
    const HTTP_FAILED_DEPENDENCY = 424; // RFC4918
    const HTTP_RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425; // RFC2817
    const HTTP_UPGRADE_REQUIRED = 426; // RFC2817
    const HTTP_PRECONDITION_REQUIRED = 428; // RFC6585
    const HTTP_TOO_MANY_REQUESTS = 429; // RFC6585
    const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431; // RFC6585

    // Server Error
    
    /**
     * The server encountered an unexpected error
     *
     * Note: This is a generic error message when no specific message
     * is suitable
     */
    const HTTP_INTERNAL_SERVER_ERROR = 500;

    /**
     * The server does not recognise the request method
     */
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_BAD_GATEWAY = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    const HTTP_GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;
    const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506; // RFC2295
    const HTTP_INSUFFICIENT_STORAGE = 507; // RFC4918
    const HTTP_LOOP_DETECTED = 508; // RFC5842
    const HTTP_NOT_EXTENDED = 510; // RFC2774
    const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;

    /**
     * This defines the rest format
     * Must be overridden it in a controller so that it is set
     *
     * @var string|NULL
     */
    protected $rest_format = NULL;

    /**
     * Defines the list of method properties such as limit, log and level
     *
     * @var array
     */
    protected $methods = [];

	/**
	 * Middleware stack for this controller
	 * 
	 * @var array
	 */
	protected $middleware_stack = [];

	/**
	 * Registered transformers
	 * 
	 * @var array
	 */
	protected $transformers = [];

	/**
	 * Current controller method being executed
	 * 
	 * @var string
	 */
	protected $_current_method = '';

    /**
     * List of allowed HTTP methods
     *
     * @var array
     */
    protected $allowed_http_methods = ['get', 'delete', 'post', 'put', 'options', 'patch', 'head'];

    /**
     * Contains details about the request
     * Fields: body, format, method, ssl
     * Note: This is a dynamic object (stdClass)
     *
     * @var object
     */
    protected $request = NULL;

    /**
     * Contains details about the response
     * Fields: format, lang
     * Note: This is a dynamic object (stdClass)
     *
     * @var object
     */
    protected $response = NULL;

    /**
     * Contains details about the REST API
     * Fields: db, ignore_limits, key, level, user_id
     * Note: This is a dynamic object (stdClass)
     *
     * @var object
     */
    protected $rest = NULL;

    /**
     * The arguments for the GET request method
     *
     * @var array
     */
    protected $_get_args = [];

    /**
     * The arguments for the POST request method
     *
     * @var array
     */
    protected $_post_args = [];

    /**
     * The arguments for the PUT request method
     *
     * @var array
     */
    protected $_put_args = [];

    /**
     * The arguments for the DELETE request method
     *
     * @var array
     */
    protected $_delete_args = [];

    /**
     * The arguments for the PATCH request method
     *
     * @var array
     */
    protected $_patch_args = [];

    /**
     * The arguments for the HEAD request method
     *
     * @var array
     */
    protected $_head_args = [];

    /**
     * The arguments for the OPTIONS request method
     *
     * @var array
     */
    protected $_options_args = [];

    /**
     * The arguments for the query parameters
     *
     * @var array
     */
    protected $_query_args = [];

    /**
     * The arguments from GET, POST, PUT, DELETE, PATCH, HEAD and OPTIONS request methods combined
     *
     * @var array
     */
    protected $_args = [];

    /**
     * The insert_id of the log entry (if we have one)
     *
     * @var string
     */
    protected $_insert_id = '';

    /**
     * If the request is allowed based on the API key provided
     *
     * @var bool
     */
    protected $_allow = TRUE;

    /**
     * The LDAP Distinguished Name of the User post authentication
     *
     * @var string
     */
    protected $_user_ldap_dn = '';

    /**
     * The start of the response time from the server
     *
     * @var number
     */
    protected $_start_rtime;

    /**
     * The end of the response time from the server
     *
     * @var number
     */
    protected $_end_rtime;

    /**
     * List all supported methods, the first will be the default format
     *
     * @var array
     */
    protected $_supported_formats = ['json' => 'application/json', 'array' => 'application/json', 'csv' => 'application/csv', 'html' => 'text/html', 'jsonp' => 'application/javascript', 'php' => 'text/plain', 'serialized' => 'application/vnd.php.serialized', 'xml' => 'application/xml'];

    /**
     * Information about the current API user
     *
     * @var object
     */
    protected $_apiuser;

    /**
     * Whether or not to perform a CORS check and apply CORS headers to the request
     *
     * @var bool
     */
    protected $check_cors = NULL;

    /**
     * Enable XSS flag
     * Determines whether the XSS filter is always active when
     * GET, OPTIONS, HEAD, POST, PUT, DELETE and PATCH data is encountered
     * Set automatically based on config setting
     *
     * @var bool
     */
    protected $_enable_xss = FALSE;
    private $is_valid_request = TRUE;

    /**
     * Rate limit log data to be stored after rate limit check
     *
     * @var array|null
     */
    protected $_rate_limit_log_data = null;
    
    // Zapier route detection properties
    protected $_zapier_route_detected = false;
    protected $_zapier_request_uri = '';

    /**
     * HTTP status codes and their respective description
     * Note: Only the widely used HTTP status codes are used
     *
     * @var array
     * @link http://www.restapitutorial.com/httpstatuscodes.html
     */
    protected $http_status_codes = [
        self::HTTP_OK => 'OK',
        self::HTTP_CREATED => 'CREATED',
        self::HTTP_NO_CONTENT => 'NO CONTENT',
        self::HTTP_NOT_MODIFIED => 'NOT MODIFIED',
        self::HTTP_BAD_REQUEST => 'BAD REQUEST',
        self::HTTP_UNAUTHORIZED => 'UNAUTHORIZED',
        self::HTTP_FORBIDDEN => 'FORBIDDEN',
        self::HTTP_NOT_FOUND => 'NOT FOUND',
        self::HTTP_NOT_PERMISSION => 'NOT PERMISSION',
        self::HTTP_METHOD_NOT_ALLOWED => 'METHOD NOT ALLOWED',
        self::HTTP_NOT_ACCEPTABLE => 'NOT ACCEPTABLE',
        self::HTTP_CONFLICT => 'CONFLICT',
        self::HTTP_INTERNAL_SERVER_ERROR => 'INTERNAL SERVER ERROR',
        self::HTTP_NOT_IMPLEMENTED => 'NOT IMPLEMENTED'
    ];

    /**
     * @var Format
     */
    private $format;

    /**
     * @var bool
     */
    private $auth_override;

    /**
     * Extend this function to apply additional checking early on in the process
     *
     * @access protected
     * @return void
     */
	protected function early_checks() {
		// Global middleware - applies to ALL controllers extending REST_Controller
		if (method_exists($this, 'middleware')) {
			// Load middleware configuration from settings
			$middleware_config = get_option('api_middleware_config');
			if (!empty($middleware_config)) {
				$middleware_config = json_decode($middleware_config, true);
			} else {
				$middleware_config = [];
			}
			
			// Request Logger Middleware (always enabled by default, can be disabled)
			if (!isset($middleware_config['request_logger']) || $middleware_config['request_logger']['enabled'] !== false) {
				$this->middleware('Request_Logger_Middleware');
			}
			
			// Response Caching Middleware
			if (isset($middleware_config['response_cache']['enabled']) && $middleware_config['response_cache']['enabled'] === true) {
				$cache_ttl = isset($middleware_config['response_cache']['ttl']) ? (int)$middleware_config['response_cache']['ttl'] : 300;
				$this->middleware('Cache_Middleware', ['ttl' => $cache_ttl]);
			}
			
			// IP Whitelist Middleware (runs before blacklist)
			if (isset($middleware_config['ip_whitelist']['enabled']) && $middleware_config['ip_whitelist']['enabled'] === true) {
				$allowed_ips = isset($middleware_config['ip_whitelist']['ips']) ? $middleware_config['ip_whitelist']['ips'] : [];
				if (!empty($allowed_ips)) {
					$this->middleware('IP_Whitelist_Middleware', ['ips' => $allowed_ips]);
				}
			}
			
			// IP Blacklist Middleware (runs after whitelist)
			if (isset($middleware_config['ip_blacklist']['enabled']) && $middleware_config['ip_blacklist']['enabled'] === true) {
				$blocked_ips = isset($middleware_config['ip_blacklist']['ips']) ? $middleware_config['ip_blacklist']['ips'] : [];
				if (!empty($blocked_ips)) {
					$this->middleware('IP_Blacklist_Middleware', ['ips' => $blocked_ips]);
				}
			}
			
			// Request Size Limit Middleware (runs early to prevent large requests)
			if (isset($middleware_config['request_size_limit']['enabled']) && $middleware_config['request_size_limit']['enabled'] === true) {
				$max_size_mb = isset($middleware_config['request_size_limit']['max_size_mb']) ? (int)$middleware_config['request_size_limit']['max_size_mb'] : 10;
				$this->middleware('Request_Size_Limit_Middleware', ['max_size_mb' => $max_size_mb]);
			}
			
			// Security Headers Middleware (runs last, adds headers to response)
			if (isset($middleware_config['security_headers']['enabled']) && $middleware_config['security_headers']['enabled'] === true) {
				$this->middleware('Security_Headers_Middleware', ['enabled' => true]);
			}
		}
		
		// Global transformers - applies to ALL controllers extending REST_Controller
		// Only enable if option is set (backwards compatibility)
		$enable_transformers = get_option('api_enable_transformers');
		if (empty($enable_transformers)) {
			// Check config file as fallback
			$this->load->config('api');
			$enable_transformers = $this->config->item('api_enable_transformers');
		}
		
		if ($enable_transformers == '1' || $enable_transformers === true) {
			if (method_exists($this, 'addTransformer')) {
				// Add standard response transformer (low priority, runs last)
				$this->addTransformer('Standard_Response_Transformer', ['priority' => 20]);
				
				// Add field filter transformer (high priority, runs first)
				$this->addTransformer('Field_Filter_Transformer', ['priority' => 5]);
				
				// Add privacy transformer (medium priority)
				$this->addTransformer('Privacy_Transformer', ['priority' => 10]);
				
				// Add pagination transformer only for list methods
				$this->addTransformer('Pagination_Transformer', [
					'only' => ['data_get'],
					'priority' => 15
				]);
			}
		}
	}	
	/**
	 * Register middleware for this controller
	 * 
	 * @param string|array $middleware Middleware class name(s) or instance(s)
	 * @param array $options Options for middleware (e.g., ['except' => ['data_get'], 'only' => ['data_post']])
	 * @return void
	 */
	protected function middleware($middleware, $options = [])
	{
		if (!isset($this->middleware_stack)) {
			$this->middleware_stack = [];
		}
		
		if (is_string($middleware)) {
			$middleware = [$middleware];
		}
		
		foreach ($middleware as $mw) {
			$this->middleware_stack[] = [
				'middleware' => $mw,
				'options' => $options
			];
		}
	}

	/**
	 * Execute middleware pipeline
	 * 
	 * @param string $method The controller method being called
	 * @return bool Returns false if middleware should stop execution
	 */
	protected function executeMiddleware($method)
	{
		if (!isset($this->middleware_stack) || empty($this->middleware_stack)) {
			return true;
		}
		
		// Filter middleware based on options
		$applicableMiddleware = [];
		foreach ($this->middleware_stack as $mw) {
			$shouldRun = true;
			
			// Check 'except' option
			if (isset($mw['options']['except']) && in_array($method, $mw['options']['except'])) {
				$shouldRun = false;
			}
			
			// Check 'only' option
			if (isset($mw['options']['only']) && !in_array($method, $mw['options']['only'])) {
				$shouldRun = false;
			}
			
			if ($shouldRun) {
				// Keep full middleware data including options for constructor parameters
				$applicableMiddleware[] = $mw;
			}
		}
		
		if (empty($applicableMiddleware)) {
			return true;
		}
		
		// Build middleware pipeline
		$pipeline = function($request) use ($method) {
			// This will be called after all middleware
			return $request;
		};
		
		// Wrap middleware in reverse order
		foreach (array_reverse($applicableMiddleware) as $mwData) {
			$mw = is_array($mwData) ? $mwData['middleware'] : $mwData;
			$mwOptions = is_array($mwData) && isset($mwData['options']) ? $mwData['options'] : [];
			
			if (is_string($mw)) {
				// Check if it's a full class name or just the class name
				if (strpos($mw, '\\') === false && strpos($mw, '_') !== false) {
					// It's a class name like "Request_Logger_Middleware"
					// Try to load it from module libraries
					$middlewareFile = __DIR__ . '/../libraries/middleware/' . $mw . '.php';
					if (file_exists($middlewareFile)) {
						require_once $middlewareFile;
					}
				}
				
				// Instantiate middleware with constructor parameters from options
				// Check for specific constructor parameters
				if ($mw === 'Cache_Middleware' && isset($mwOptions['ttl'])) {
					// Cache_Middleware constructor expects TTL in seconds
					$middlewareInstance = new $mw($mwOptions['ttl']);
				} elseif ($mw === 'IP_Whitelist_Middleware' && isset($mwOptions['ips'])) {
					$middlewareInstance = new $mw($mwOptions['ips']);
				} elseif ($mw === 'IP_Blacklist_Middleware' && isset($mwOptions['ips'])) {
					$middlewareInstance = new $mw($mwOptions['ips']);
				} elseif ($mw === 'Security_Headers_Middleware' && isset($mwOptions['enabled'])) {
					$middlewareInstance = new $mw($mwOptions['enabled']);
				} elseif ($mw === 'Request_Size_Limit_Middleware' && isset($mwOptions['max_size_mb'])) {
					// Request_Size_Limit_Middleware constructor expects size in MB
					$middlewareInstance = new $mw($mwOptions['max_size_mb']);
				} else {
					$middlewareInstance = new $mw();
				}
			} else {
				$middlewareInstance = $mw;
			}
			
			// Check if it implements the interface
			if (!($middlewareInstance instanceof Api_Middleware_Interface)) {
				continue;
			}
			
			$next = $pipeline;
			$pipeline = function($request) use ($middlewareInstance, $next) {
				return $middlewareInstance->handle($request, $next);
			};
		}
		
		// Execute pipeline
		$request = (object)[
			'method' => $method,
			'controller' => get_class($this),
			'args' => $this->_args,
			'headers' => $this->_head_args,
			'response' => null,
			'response_code' => null,
			'skip_controller' => false
		];
		
		$result = $pipeline($request);
		
		// If middleware set skip_controller, return early
		if (isset($result->skip_controller) && $result->skip_controller === true) {
			if (isset($result->response)) {
				$this->response($result->response, $result->response_code ?? REST_Controller::HTTP_OK);
			}
			return false;
		}
		
		return true;
	}

    /**
     * Constructor for the REST API
     *
     * @access public
     * @param string $config Configuration filename minus the file extension
     * e.g: my_rest.php is passed as 'my_rest'
     */
    public function __construct($config = 'rest') {
        parent::__construct();
        
		// CRITICAL FIX: Load middleware interface BEFORE anything else
		// Use absolute path to avoid autoloader issues
		$interfacePath = __DIR__ . '/../libraries/Api_Middleware.php';
		if (file_exists($interfacePath) && !interface_exists('Api_Middleware_Interface')) {
			require_once $interfacePath;
		}
        $this->preflight_checks();

        // Set the default value of global xss filtering. Same approach as CodeIgniter 3
        $this->_enable_xss = ($this->config->item('global_xss_filtering') === TRUE);

        // Don't try to parse template variables like {elapsed_time} and {memory_usage}

        // when output is displayed for not damaging data accidentally
        $this->output->parse_exec_vars = FALSE;

        // Start the timer for how long the request takes
        $this->_start_rtime = microtime(TRUE);

        // Load the rest.php configuration file
        $this->get_local_config($config);

        // At present the library is bundled with REST_Controller 2.5+, but will eventually be part of CodeIgniter (no citation)
        if (class_exists('Format')) {
            $this->format = new Format();
        } else {
            $this->load->library('Format', NULL, 'libraryFormat');
            $this->format = $this->libraryFormat;
        }

        // Determine supported output formats from configuration
        $supported_formats = $this->config->item('rest_supported_formats');

        // Validate the configuration setting output formats
        if (empty($supported_formats)) {
            $supported_formats = [];
        }
        if (!is_array($supported_formats)) {
            $supported_formats = [$supported_formats];
        }

        // Add silently the default output format if it is missing
        $default_format = $this->_get_default_output_format();
        if (!in_array($default_format, $supported_formats)) {
            $supported_formats[] = $default_format;
        }

        // Now update $this->_supported_formats
        $this->_supported_formats = array_intersect_key($this->_supported_formats, array_flip($supported_formats));

        // Get the language
        $language = $this->config->item('rest_language');
        if ($language === NULL) {
            $language = 'english';
        }

        // Load the language file
        $this->lang->load('rest_controller', $language, FALSE, TRUE, __DIR__ . '/../');

        // Initialise the response, request and rest objects
        $this->request = new stdClass();
        $this->response = new stdClass();
        $this->rest = new stdClass();

        // Check to see if the current IP address is blacklisted
        if ($this->config->item('rest_ip_blacklist_enabled') === TRUE) {
            $this->_check_blacklist_auth();
        }

        // Determine whether the connection is HTTPS
        $this->request->ssl = is_https();

        // How is this request being made? GET, POST, PATCH, DELETE, INSERT, PUT, HEAD or OPTIONS
        $this->request->method = $this->_detect_method();

        // Check for CORS access request
        $check_cors = $this->config->item('check_cors');
        if ($check_cors === TRUE) {
            $this->_check_cors();
        }

        // Create an argument container if it doesn't exist e.g. _get_args
        if (isset($this->{'_' . $this->request->method . '_args'}) === FALSE) {
            $this->{'_' . $this->request->method . '_args'} = [];
        }

        // Set up the query parameters
        $this->_parse_query();

        // Set up the GET variables
        $this->_get_args = array_merge($this->_get_args, $this->uri->ruri_to_assoc());

        // Try to find a format for the request (means we have a request body)
        $this->request->format = $this->_detect_input_format();

        // Not all methods have a body attached with them
        $this->request->body = NULL;
        $this->{'_parse_' . $this->request->method}();

        // Fix parse method return arguments null
        if ($this->{'_' . $this->request->method . '_args'} === null) {
            $this->{'_' . $this->request->method . '_args'} = [];
        }

        // Now we know all about our request, let's try and parse the body if it exists
        if ($this->request->format && $this->request->body) {
            $this->request->body = $this->format->factory($this->request->body, $this->request->format)->to_array();
            // Assign payload arguments to proper method container
            $this->{'_' . $this->request->method . '_args'} = $this->request->body;
        }
        //get header vars
        $this->_head_args = $this->input->request_headers();
        
        // Add playground header with default value "yes" if not already set
        if (!isset($this->_head_args['playground'])) {
            $this->_head_args['playground'] = 'yes';
        }

        // Map authtoken query parameter to Authtoken for compatibility with automation platforms and Postman
        // This ensures ?authtoken=TOKEN works universally across all endpoints
        // Also support api_key for backwards compatibility
        if (isset($this->_get_args['authtoken']) && !empty($this->_get_args['authtoken']) && !isset($this->_get_args['Authtoken'])) {
            $this->_get_args['Authtoken'] = $this->_get_args['authtoken'];
        } elseif (isset($this->_get_args['api_key']) && !empty($this->_get_args['api_key']) && !isset($this->_get_args['Authtoken'])) {
            // Backwards compatibility with api_key parameter
            $this->_get_args['Authtoken'] = $this->_get_args['api_key'];
        }
        // Also check $_GET directly in case it wasn't parsed into _get_args yet
        if (isset($_GET['authtoken']) && !empty($_GET['authtoken']) && !isset($this->_get_args['Authtoken'])) {
            $this->_get_args['Authtoken'] = $_GET['authtoken'];
        } elseif (isset($_GET['api_key']) && !empty($_GET['api_key']) && !isset($this->_get_args['Authtoken'])) {
            // Backwards compatibility with api_key parameter
            $this->_get_args['Authtoken'] = $_GET['api_key'];
        }

        // Merge both for one mega-args variable
        $this->_args = array_merge($this->_get_args, $this->_options_args, $this->_patch_args, $this->_head_args, $this->_put_args, $this->_post_args, $this->_delete_args, $this->{'_' . $this->request->method . '_args'});

        // Which format should the data be returned in?
        $this->response->format = $this->_detect_output_format();

        // Which language should the data be returned in?
        $this->response->lang = $this->_detect_lang();

        // Extend this function to apply additional checking early on in the process
        $this->early_checks();
        $this->load->library('app_modules');
        if (!$this->app_modules->is_active('api')) {
            $this->response(['status' => FALSE, 'message' => "API Module is not active"], REST_Controller::HTTP_UNAUTHORIZED);
        }

        // Load DB if its enabled
        if ($this->config->item('rest_database_group') && ($this->config->item('rest_enable_keys') || $this->config->item('rest_enable_logging'))) {
            $this->rest->db = $this->load->database($this->config->item('rest_database_group'), TRUE);
        }

        // Use whatever database is in use (isset returns FALSE)
        elseif (property_exists($this, 'db')) {
            $this->rest->db = $this->db;
        }

        // Check if there is a specific auth type for the current class/method
        // _auth_override_check could exit so we need $this->rest->db initialized before
        $this->auth_override = $this->_auth_override_check();

        // Checking for keys? GET TO WorK!
        // Skip keys test for $config['auth_override_class_method']['class'['method'] = 'none'
        if ($this->config->item('rest_enable_keys') && $this->auth_override !== TRUE) {
            $this->_allow = $this->_detect_api_key();
            if (!$this->_allow) {
                $message = array('status' => FALSE, 'message' => 'Token is not found');
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }

        // Only allow ajax requests
        if ($this->input->is_ajax_request() === FALSE && $this->config->item('rest_ajax_only')) {
            // Display an error response
            $this->response([$this->config->item('rest_status_field_name') => FALSE, $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_ajax_only') ], self::HTTP_NOT_ACCEPTABLE);
        }

        // When there is no specific override for the current class/method, use the default auth value set in the config
        if ($this->auth_override === FALSE && (!($this->config->item('rest_enable_keys') && $this->_allow === TRUE) || ($this->config->item('allow_auth_and_keys') === TRUE && $this->_allow === TRUE))) {
            $rest_auth = strtolower($this->config->item('rest_auth'));
            switch ($rest_auth) {
                case 'basic':
                    $this->_prepare_basic_auth();
                break;
                case 'digest':
                    $this->_prepare_digest_auth();
                break;
                case 'session':
                    $this->_check_php_session();
                break;
            }
            if ($this->config->item('rest_ip_whitelist_enabled') === TRUE) {
                $this->_check_whitelist_auth();
            }
        }

        // load authorization token library
        $this->load->library('Authorization_Token');
        $this->load->model('Api_model');
        $is_valid_token = $this->authorization_token->validateToken();
        $token = $this->authorization_token->get_token();
        $check_token = $this->Api_model->check_token($token);
        if ($is_valid_token['status'] == false || $check_token === false) {
            // Fire authentication failed event
            Api_Event_Manager::fire(
                Api_Event_Manager::EVENT_AUTHENTICATION_FAILED,
                array_merge(
                    Api_Event_Manager::getEventData($this),
                    ['reason' => $is_valid_token['message']]
                )
            );
            
            $message = array('status' => FALSE, 'message' => $is_valid_token['message']);
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else {
            // Fire authentication success event
            Api_Event_Manager::fire(
                Api_Event_Manager::EVENT_AUTHENTICATION_SUCCESS,
                Api_Event_Manager::getEventData($this)
            );
        }
        
        // Check rate limiting and quotas
        if (!$this->check_rate_limits($token)) {
            // Fire rate limit exceeded event
            Api_Event_Manager::fire(
                Api_Event_Manager::EVENT_RATE_LIMIT_EXCEEDED,
                array_merge(
                    Api_Event_Manager::getEventData($this),
                    ['token' => $token]
                )
            );
            
            $message = array('status' => FALSE, 'message' => 'Rate limit exceeded');
            $this->response($message, REST_Controller::HTTP_TOO_MANY_REQUESTS);
        }
    }

    /**
     * @param $config_file
     */
    private function get_local_config($config_file) {
        if (file_exists(__DIR__ . "/../config/" . $config_file . ".php")) {
            $config = array();
            include (__DIR__ . "/../config/" . $config_file . ".php");
            foreach ($config AS $key => $value) {
                $this->config->set_item($key, $value);
            }
        }
        $this->load->config($config_file, FALSE, TRUE);
    }

    /**
     * De-constructor
     *
     * @author Chris Kacerguis
     * @access public
     * @return void
     */
    public function __destruct() {
        // Get the current timestamp
        $this->_end_rtime = microtime(TRUE);

        // Log the loading time to the log table
        if ($this->config->item('rest_enable_logging') === TRUE) {
            $this->_log_access_time();
        }
    }

    /**
     * Checks to see if we have everything we need to run this library.
     *
     * @access protected
     * @throws Exception
     */
    protected function preflight_checks() {
        // Check to see if PHP is equal to or greater than 5.4.x
        if (is_php('5.4') === FALSE) {
            // CodeIgniter 3 is recommended for v5.4 or above
            throw new Exception('Using PHP v' . PHP_VERSION . ', though PHP v5.4 or greater is required');
        }

        // Check to see if this is CI 3.x
        if (explode('.', CI_VERSION, 2) [0] < 3) {
            throw new Exception('REST Server requires CodeIgniter 3.x');
        }
    }

    /**
     * Requests are not made to methods directly, the request will be for
     * an "object". This simply maps the object and method to the correct
     * Controller method
     *
     * @access public
     * @param string $object_called
     * @param array $arguments The arguments passed to the controller method
     * @throws Exception
     */
    public function _remap($object_called, $arguments = []) {
        // Should we answer if not over SSL?
        if ($this->config->item('force_https') && $this->request->ssl === FALSE) {
            $this->response([$this->config->item('rest_status_field_name') => FALSE, $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_unsupported') ], self::HTTP_FORBIDDEN);
            $this->is_valid_request = false;
        }
        
        // Remove the supported format from the function name e.g. index.json => index
        $object_called = preg_replace('/^(.*)\.(?:' . implode('|', array_keys($this->_supported_formats)) . ')$/', '$1', $object_called);
        $controller_method = $object_called . '_' . $this->request->method;
        
        // Fire request received event
        Api_Event_Manager::fire(
            Api_Event_Manager::EVENT_REQUEST_RECEIVED,
            Api_Event_Manager::getEventData($this, $controller_method)
        );
        
        // Fire before controller event
        Api_Event_Manager::fire(
            Api_Event_Manager::EVENT_BEFORE_CONTROLLER,
            array_merge(
                Api_Event_Manager::getEventData($this, $controller_method),
                ['arguments' => $arguments]
            )
        );
        
		// Execute middleware pipeline BEFORE controller method
		if ($this->executeMiddleware($controller_method) === false) {
			return; // Middleware handled the response
		}

        // Does this method exist? If not, try executing an index method
        if (!method_exists($this, $controller_method)) {
            $controller_method = "index_" . $this->request->method;
            array_unshift($arguments, $object_called);
        }

        // Store current method for transformers
        $this->_current_method = $controller_method;

        // Do we want to log this method (if allowed by config)?
        $log_method = !(isset($this->methods[$controller_method]['log']) && $this->methods[$controller_method]['log'] === FALSE);

        // Use keys for this method?
        $use_key = !(isset($this->methods[$controller_method]['key']) && $this->methods[$controller_method]['key'] === FALSE);

        // They provided a key, but it wasn't valid, so get them out of here
        if ($this->config->item('rest_enable_keys') && $use_key && $this->_allow === FALSE) {
            if ($this->config->item('rest_enable_logging') && $log_method) {
                $this->_log_request();
            }
            // fix cross site to option request error
            if ($this->request->method == 'options') {
                exit;
            }
            $this->response([$this->config->item('rest_status_field_name') => FALSE, $this->config->item('rest_message_field_name') => sprintf($this->lang->line('text_rest_invalid_api_key'), $this->rest->key) ], self::HTTP_FORBIDDEN);
            $this->is_valid_request = false;
        }

        // Check to see if this key has access to the requested controller
        if ($this->config->item('rest_enable_keys') && $use_key && empty($this->rest->key) === FALSE && $this->_check_access() === FALSE) {
            if ($this->config->item('rest_enable_logging') && $log_method) {
                $this->_log_request();
            }
            $this->response([$this->config->item('rest_status_field_name') => FALSE, $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_api_key_unauthorized') ], self::HTTP_UNAUTHORIZED);
            $this->is_valid_request = false;
        }

        // Sure it exists, but can they do anything with it?
        if (!method_exists($this, $controller_method)) {
            $this->response([$this->config->item('rest_status_field_name') => FALSE, $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_unknown_method') ], self::HTTP_METHOD_NOT_ALLOWED);
            $this->is_valid_request = false;
        }

        $object_name = get_class($this);
        $token = $this->authorization_token->get_token();
        
        // For Zapier controller, use 'zapier' as feature and map methods appropriately
        // Zapier methods: test_get, poll_get, resources_get -> capabilities: test, poll, resources
        $feature = strtolower($object_name);
        $capability = str_replace("data_", "", $controller_method);
        
        // Special handling for Zapier controller
        if ($object_name === 'Zapier') {
            $feature = 'zapier';
            // Remove _get, _post, etc. suffix for capability name
            $capability = preg_replace('/_(get|post|put|delete|patch|options|head)$/i', '', $capability);
        }
        
        // Special handling for Thirdparty controller
        if ($object_name === 'Thirdparty') {
            $feature = 'thirdparty';
            // Map customtable methods to capabilities
            // customtable_get, customtable_id_get -> get
            // customtable_post -> post
            // customtable_id_put -> put
            // customtable_id_delete -> delete
            if (preg_match('/^customtable.*_get$/i', $controller_method)) {
                $capability = 'get';
            } elseif (preg_match('/^customtable.*_post$/i', $controller_method)) {
                $capability = 'post';
            } elseif (preg_match('/^customtable.*_put$/i', $controller_method)) {
                $capability = 'put';
            } elseif (preg_match('/^customtable.*_delete$/i', $controller_method)) {
                $capability = 'delete';
            } else {
                // Fallback: extract HTTP method
                $capability = preg_replace('/^customtable.*_(get|post|put|delete|patch|options|head)$/i', '$1', $controller_method);
            }
        }
        
        // Special handling for Common controller
        // Maps /api/common/{type} endpoints to their respective permission features
        if ($object_name === 'Common') {
            // Get the type from URI segment (e.g., expense_category, payment_mode, tax_data)
            $type = $this->uri->segment(3);
            
            // Map type to permission feature name
            $common_permission_map = [
                'expense_category' => 'expense_categories',
                'tax_data' => 'taxes',
                'payment_mode' => 'payment_methods',
            ];
            
            if (isset($common_permission_map[$type])) {
                $feature = $common_permission_map[$type];
            }
            
            // All Common endpoints are GET only
            $capability = 'get';
        }

        // Invoices "Send email" action -> dedicated 'send' capability under 'invoices'
        if ($object_name === 'Invoices' && $controller_method === 'send_post') {
            $feature    = 'invoices';
            $capability = 'send';
        }

        // Webhooks sub-endpoints (events catalog, logs, toggle) fold onto the
        // standard get/put capabilities, so a token only needs webhooks
        // get/post/put/delete rather than per-method capability names.
        if ($object_name === 'Webhooks') {
            if ($controller_method === 'events_get' || $controller_method === 'logs_get') {
                $capability = 'get';
            } elseif ($controller_method === 'toggle_post') {
                $capability = 'put';
            }
        }

        // Task comment sub-endpoints map onto the standard tasks capabilities.
        if ($object_name === 'Tasks') {
            if ($controller_method === 'comments_get') {
                $capability = 'get';
            } elseif ($controller_method === 'comments_post') {
                $capability = 'post';
            } elseif ($controller_method === 'comments_id_put') {
                $capability = 'put';
            } elseif ($controller_method === 'comments_id_delete') {
                $capability = 'delete';
            }
        }

        // Knowledge Base group sub-endpoints (groups_get/post/put/delete) fold
        // onto the standard get/post/put/delete capabilities, so one
        // knowledge_base permission set covers both articles and groups.
        if ($object_name === 'Knowledge_base'
            && preg_match('/^groups_(get|post|put|delete)$/', $controller_method, $kb_m)) {
            $capability = $kb_m[1];
        }

        // PaymentsOnAccount exposes several domain actions; authorize them by
        // HTTP verb under a single paymentsonaccount permission group.
        if ($object_name === 'Paymentsonaccount'
            && preg_match('/_(get|post|put|delete)$/', $controller_method, $poa_m)) {
            $feature = 'paymentsonaccount';
            $capability = $poa_m[1];
        }

        // Delivery Notes domain actions share the module's verb-level API permissions.
        if ($object_name === 'Delivery_notes'
            && preg_match('/_(get|post|put|delete)$/', $controller_method, $delivery_notes_m)) {
            $feature = 'delivery_notes';
            $capability = $delivery_notes_m[1];
        }

        if ($object_name === 'Commission'
            && preg_match('/_(get|post|put|delete)$/', $controller_method, $commission_m)) {
            $feature = 'commission';
            $capability = $commission_m[1];
        }

        if ($object_name === 'Myshopify'
            && preg_match('/_(get|post)$/', $controller_method, $myshopify_m)) {
            $feature = 'myshopify';
            $capability = $myshopify_m[1];
        }

        // v3: block brute-forced tokens before doing any more work
        $this->_auth_throttle_check();

        // MCP performs its own per-tool permission filtering (tools/list and
        // tools/call are scoped inside the Mcp controller), so any valid token
        // may reach the JSON-RPC endpoint itself.
        if ($object_name === 'Mcp' || $object_name === 'Batch') {
            // Mcp and Batch enforce per-operation permissions internally
            $check_token_permission = $this->Api_model->check_token($token);
        } else {
            $check_token_permission = $this->Api_model->check_token_permission($token, $feature, $capability);
        }
        if ($check_token_permission === false) {
            $this->_register_auth_failure();
            $message = array('status' => FALSE, 'message' => $this->lang->line('text_rest_api_key_permissions'));
            $this->response($message, self::HTTP_NOT_PERMISSION);
            $this->is_valid_request = false;
        }

        // v3 (opt-in): staff-level data visibility - bootstrap the staff
        // session context so Perfex core models apply their own scoping
        if ($this->is_valid_request) {
            $this->_apply_staff_visibility($token);
        }

        // Doing key related stuff? Can only do it if they have a key right?
        if ($this->config->item('rest_enable_keys') && empty($this->rest->key) === FALSE) {
            // Check the limit
            if ($this->config->item('rest_enable_limits') && $this->_check_limit($controller_method) === FALSE) {
                $response = [$this->config->item('rest_status_field_name') => FALSE, $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_api_key_time_limit') ];
                $this->response($response, self::HTTP_UNAUTHORIZED);
                $this->is_valid_request = false;
            }
            // If no level is set use 0, they probably aren't using permissions
            $level = isset($this->methods[$controller_method]['level']) ? $this->methods[$controller_method]['level'] : 0;
            // If no level is set, or it is lower than/equal to the key's level
            $authorized = $level <= $this->rest->level;
            // IM TELLIN!
            if ($this->config->item('rest_enable_logging') && $log_method) {
                $this->_log_request($authorized);
            }
            if ($authorized === FALSE) {
                // They don't have good enough perms
                $response = [$this->config->item('rest_status_field_name') => FALSE, $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_api_key_permissions') ];
                $this->response($response, self::HTTP_UNAUTHORIZED);
                $this->is_valid_request = false;
            }
        }
        //check request limit by ip without login
        elseif ($this->config->item('rest_limits_method') == "IP_ADDRESS" && $this->config->item('rest_enable_limits') && $this->_check_limit($controller_method) === FALSE) {
            $response = [$this->config->item('rest_status_field_name') => FALSE, $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_ip_address_time_limit') ];
            $this->response($response, self::HTTP_UNAUTHORIZED);
            $this->is_valid_request = false;
        }
        // No key stuff, but record that stuff is happening
        elseif ($this->config->item('rest_enable_logging') && $log_method) {
            $this->_log_request($authorized = TRUE);
        }

        // Call the controller method and passed arguments
        try {
            if ($this->is_valid_request) {
                // v3: Idempotency-Key replay for POST requests (safe retries)
                $this->_idempotency_check();

                call_user_func_array([$this, $controller_method], $arguments);
                
                // Fire after controller event
                Api_Event_Manager::fire(
                    Api_Event_Manager::EVENT_AFTER_CONTROLLER,
                    array_merge(
                        Api_Event_Manager::getEventData($this, $controller_method),
                        ['success' => true]
                    )
                );
            }
        }
        catch(Exception $ex) {
            // Fire error event
            Api_Event_Manager::fire(
                Api_Event_Manager::EVENT_ERROR_OCCURRED,
                array_merge(
                    Api_Event_Manager::getEventData($this, $controller_method),
                    [
                        'error' => $ex->getMessage(),
                        'trace' => $ex->getTraceAsString()
                    ]
                )
            );
            if ($this->config->item('rest_handle_exceptions') === FALSE) {
                throw $ex;
            }
            // If the method doesn't exist, then the error will be caught and an error response shown
            $_error = & load_class('Exceptions', 'core');
            $_error->show_exception($ex);
        }
    }

    /**
     * Takes mixed data and optionally a status code, then creates the response
     *
     * @access public
     * @param array|NULL $data Data to output to the user
     * @param int|NULL $http_code HTTP status code
     * @param bool $continue TRUE to flush the response to the client and continue
     * running the script; otherwise, exit
     */
    public function response($data = NULL, $http_code = NULL, $continue = FALSE) {
        ob_start();

        // v3: quota visibility headers + idempotent response persistence
        $this->_emit_rate_limit_headers();
        $this->_idempotency_store($data, $http_code);

        // If the HTTP status is not NULL, then cast as an integer
        if ($http_code !== NULL) {
            // So as to be safe later on in the process
            $http_code = (int)$http_code;
        }

        // Set the output as NULL by default
        $output = NULL;

        // If data is NULL and no HTTP status code provided, then display, error and exit
        if ($data === NULL && $http_code === NULL) {
            $http_code = self::HTTP_NOT_FOUND;
        }

        // If data is not NULL and a HTTP status code provided, then continue
        elseif ($data !== NULL) {
            // Fire before response event
            Api_Event_Manager::fire(
                Api_Event_Manager::EVENT_BEFORE_RESPONSE,
                array_merge(
                    Api_Event_Manager::getEventData($this),
                    [
                        'response_data' => $data,
                        'http_code' => $http_code
                    ]
                )
            );
            
            // Apply transformers before formatting
            $method = isset($this->_current_method) ? $this->_current_method : '';
            $data = $this->applyTransformers($data, $method);
            
            // If the format method exists, call and return the output in that format
            if (method_exists($this->format, 'to_' . $this->response->format)) {
                // Set the format header
                $this->output->set_content_type($this->_supported_formats[$this->response->format], strtolower($this->config->item('charset')));
                $output = $this->format->factory($data)->{'to_' . $this->response->format}();
                // An array must be parsed as a string, so as not to cause an array to string error
                // Json is the most appropriate form for such a data type
                if ($this->response->format === 'array') {
                    $output = $this->format->factory($output)->{'to_json'}();
                }
            } else {
                // If an array or object, then parse as a json, so as to be a 'string'
                if (is_array($data) || is_object($data)) {
                    $data = $this->format->factory($data)->{'to_json'}();
                }
                // Format is not supported, so output the raw data as a string
                $output = $data;
            }
        }

        // If not greater than zero, then set the HTTP status code as 200 by default
        // Though perhaps 500 should be set instead, for the developer not passing a
        // correct HTTP status code
        $http_code > 0 || $http_code = self::HTTP_OK;
        $this->output->set_status_header($http_code);

        // JC: Log response code only if rest logging enabled
        if ($this->config->item('rest_enable_logging') === TRUE) {
            $this->_log_response_code($http_code);
        }
        
        // Log API usage for quota tracking and rate limiting
        $response_time = microtime(TRUE) - $this->_start_rtime;
        $api_key = isset($this->rest->key) && !empty($this->rest->key) ? $this->rest->key : null;
        $this->log_api_usage($api_key, $http_code, $response_time);

        // Fire response sent event
        Api_Event_Manager::fire(
            Api_Event_Manager::EVENT_RESPONSE_SENT,
            array_merge(
                Api_Event_Manager::getEventData($this),
                [
                    'http_code' => $http_code,
                    'response_time' => microtime(TRUE) - $this->_start_rtime
                ]
            )
        );
        
        // Output the data
        $this->output->set_output($output);
        if ($continue === FALSE) {
            // Display the data and exit execution
            $this->output->_display();
            exit;
        } else {
            ob_end_flush();
        }

        // Otherwise dump the output automatically        
    }

    /**
     * Takes mixed data and optionally a status code, then creates the response
     * within the buffers of the Output class. The response is sent to the client
     * lately by the framework, after the current controller's method termination.
     * All the hooks after the controller's method termination are executable
     *
     * @access public
     * @param array|NULL $data Data to output to the user
     * @param int|NULL $http_code HTTP status code
     */
    public function set_response($data = NULL, $http_code = NULL) {
        $this->response($data, $http_code, TRUE);
    }

    /**
     * Get the input format e.g. json or xml
     *
     * @access protected
     * @return string|NULL Supported input format; otherwise, NULL
     */
    protected function _detect_input_format() {
        // Get the CONTENT-TYPE value from the SERVER variable
        $content_type = $this->input->server('CONTENT_TYPE');
        if (empty($content_type) === FALSE) {
            // If a semi-colon exists in the string, then explode by ; and get the value of where
            // the current array pointer resides. This will generally be the first element of the array
            $content_type = (strpos($content_type, ';') !== FALSE ? current(explode(';', $content_type)) : $content_type);
            // Check all formats against the CONTENT-TYPE header
            foreach ($this->_supported_formats as $type => $mime) {
                // $type = format e.g. csv
                // $mime = mime type e.g. application/csv
                // If both the mime types match, then return the format
                if ($content_type === $mime) {
                    return $type;
                }
            }
        }
        return NULL;
    }

    /**
     * Gets the default format from the configuration. Fallbacks to 'json'
     * if the corresponding configuration option $config['rest_default_format']
     * is missing or is empty
     *
     * @access protected
     * @return string The default supported input format
     */
    protected function _get_default_output_format() {
        $default_format = (string)$this->config->item('rest_default_format');
        return $default_format === '' ? 'json' : $default_format;
    }

    /**
     * Detect which format should be used to output the data
     *
     * @access protected
     * @return mixed|NULL|string Output format
     */
    protected function _detect_output_format() {
        // Concatenate formats to a regex pattern e.g. \.(csv|json|xml)
        $pattern = '/\.(' . implode('|', array_keys($this->_supported_formats)) . ')($|\/)/';
        $matches = [];

        // Check if a file extension is used e.g. http://example.com/api/index.json?param1=param2
        if (preg_match($pattern, $this->uri->uri_string(), $matches)) {
            return $matches[1];
        }

        // Get the format parameter named as 'format'
        if (isset($this->_get_args['format'])) {
            $format = strtolower($this->_get_args['format']);
            if (isset($this->_supported_formats[$format]) === TRUE) {
                return $format;
            }
        }

        // Get the HTTP_ACCEPT server variable
        $http_accept = $this->input->server('HTTP_ACCEPT');

        // Otherwise, check the HTTP_ACCEPT server variable
        if ($this->config->item('rest_ignore_http_accept') === FALSE && $http_accept !== NULL) {
            // Check all formats against the HTTP_ACCEPT header
            foreach (array_keys($this->_supported_formats) as $format) {
                // Has this format been requested?
                if (strpos($http_accept, $format) !== FALSE) {
                    if ($format !== 'html' && $format !== 'xml') {
                        // If not HTML or XML assume it's correct
                        return $format;
                    } elseif ($format === 'html' && strpos($http_accept, 'xml') === FALSE) {
                        // HTML or XML have shown up as a match
                        // If it is truly HTML, it wont want any XML
                        return $format;
                    } else if ($format === 'xml' && strpos($http_accept, 'html') === FALSE) {
                        // If it is truly XML, it wont want any HTML
                        return $format;
                    }
                }
            }
        }

        // Check if the controller has a default format
        if (empty($this->rest_format) === FALSE) {
            return $this->rest_format;
        }

        // Obtain the default format from the configuration
        return $this->_get_default_output_format();
    }

    /**
     * Get the HTTP request string e.g. get or post
     *
     * @access protected
     * @return string|NULL Supported request method as a lowercase string; otherwise, NULL if not supported
     */
    protected function _detect_method() {
        // Declare a variable to store the method
        $method = NULL;

        // Determine whether the 'enable_emulate_request' setting is enabled
        if ($this->config->item('enable_emulate_request') === TRUE) {
            $method = $this->input->post('_method');
            if ($method === NULL) {
                $method = $this->input->server('HTTP_X_HTTP_METHOD_OVERRIDE');
            }
            $method = strtolower($method??'');
        }
        if (empty($method)) {
            // Get the request method as a lowercase string
            $method = $this->input->method();
        }
        return in_array($method, $this->allowed_http_methods) && method_exists($this, '_parse_' . $method) ? $method : 'get';
    }

    /**
     * See if the user has provided an API key
     *
     * @access protected
     * @return bool
     */
    protected function _detect_api_key() {
        // Get the api key name variable set in the rest config file
        $api_key_variable = $this->config->item('rest_key_name');

        // Work out the name of the SERVER entry based on config
        $key_name = 'HTTP_' . strtoupper(str_replace('-', '_', $api_key_variable));
        $this->rest->key = NULL;
        $this->rest->level = NULL;
        $this->rest->user_id = NULL;
        $this->rest->ignore_limits = FALSE;

        // Find the key from server, arguments, or query parameters
        // Check in order: _args (includes query params), server headers, then direct $_GET for authtoken
        $key = null;
        if (isset($this->_args[$api_key_variable]) && !empty($this->_args[$api_key_variable])) {
            $key = $this->_args[$api_key_variable];
        } elseif ($this->input->server($key_name)) {
            $key = $this->input->server($key_name);
        } elseif (isset($_GET['authtoken']) && !empty($_GET['authtoken'])) {
            // Support authtoken query parameter (primary) - matches Postman requests
            $key = $_GET['authtoken'];
        } elseif (isset($_GET[$api_key_variable]) && !empty($_GET[$api_key_variable])) {
            // Support Authtoken query parameter (case-sensitive)
            $key = $_GET[$api_key_variable];
        } elseif (isset($_GET['api_key']) && !empty($_GET['api_key'])) {
            // Backwards compatibility with api_key parameter
            $key = $_GET['api_key'];
        }
        
        if ($key) {
            if (!($row = $this->rest->db->where($this->config->item('rest_key_column'), $key)->get($this->config->item('rest_keys_table'))->row())) {
                return FALSE;
            }
            
            $this->rest->key = $row->{$this->config->item('rest_key_column') };
            isset($row->user_id) && $this->rest->user_id = $row->user_id;
            isset($row->level) && $this->rest->level = $row->level;
            isset($row->ignore_limits) && $this->rest->ignore_limits = $row->ignore_limits;
            $this->_apiuser = $row;
            /*
            
             * If "is private key" is enabled, compare the ip address with the list
            
             * of valid ip addresses stored in the database
            
            */
            if (empty($row->is_private_key) === FALSE) {
                // Check for a list of valid ip addresses
                if (isset($row->ip_addresses)) {
                    // multiple ip addresses must be separated using a comma, explode and loop
                    $list_ip_addresses = explode(',', $row->ip_addresses);
                    $found_address = FALSE;
                    foreach ($list_ip_addresses as $ip_address) {
                        if ($this->input->ip_address() === trim($ip_address)) {
                            // there is a match, set the the value to TRUE and break out of the loop
                            $found_address = TRUE;
                            break;
                        }
                    }
                    return $found_address;
                } else {
                    // There should be at least one IP address for this private key
                    return FALSE;
                }
            }
            return TRUE;
        }

        // No key has been sent
        return FALSE;
    }

    /**
     * Preferred return language
     *
     * @access protected
     * @return string|NULL|array The language code
     */
    protected function _detect_lang() {
        $lang = $this->input->server('HTTP_ACCEPT_LANGUAGE');
        if ($lang === NULL) {
            return NULL;
        }

        // It appears more than one language has been sent using a comma delimiter
        if (strpos($lang, ',') !== FALSE) {
            $langs = explode(',', $lang);
            $return_langs = [];
            foreach ($langs as $lang) {
                // Remove weight and trim leading and trailing whitespace
                list($lang) = explode(';', $lang);
                $return_langs[] = trim($lang);
            }
            return $return_langs;
        }

        // Otherwise simply return as a string
        return $lang;
    }

    /**
     * Add the request to the log table
     *
     * @access protected
     * @param bool $authorized TRUE the user is authorized; otherwise, FALSE
     * @return bool TRUE the data was inserted; otherwise, FALSE
     */
    protected function _log_request($authorized = FALSE) {
        // Insert the request into the log table
        $is_inserted = $this->rest->db->insert($this->config->item('rest_logs_table'), ['uri' => $this->uri->uri_string(), 'method' => $this->request->method, 'params' => $this->_args ? ($this->config->item('rest_logs_json_params') === TRUE ? json_encode($this->_args) : serialize($this->_args)) : NULL, 'api_key' => isset($this->rest->key) ? $this->rest->key : '', 'ip_address' => $this->input->ip_address(), 'time' => time(), 'authorized' => $authorized]);

        // Get the last insert id to update at a later stage of the request
        $this->_insert_id = $this->rest->db->insert_id();
        return $is_inserted;
    }

    /**
     * Check if the requests to a controller method exceed a limit
     *
     * @access protected
     * @param string $controller_method The method being called
     * @return bool TRUE the call limit is below the threshold; otherwise, FALSE
     */
    protected function _check_limit($controller_method) {
        // They are special, or it might not even have a limit
        if (empty($this->rest->ignore_limits) === FALSE) {
            // Everything is fine
            return TRUE;
        }
        
        $api_key = isset($this->rest->key) ? $this->rest->key : '';
        $ip_address = $this->input->ip_address();
        $endpoint = $this->uri->uri_string();
        
        // Determine the identifier based on limit method
        switch ($this->config->item('rest_limits_method')) {
            case 'IP_ADDRESS':
                $identifier = $ip_address;
                $identifier_type = 'ip';
            break;
            case 'API_KEY':
                $identifier = $api_key;
                $identifier_type = 'api_key';
            break;
            case 'METHOD_NAME':
                $identifier = $ip_address; // Use IP for method-based limiting
                $endpoint = $controller_method;
                $identifier_type = 'method';
            break;
            case 'ROUTED_URL':
            default:
                $identifier = $api_key ?: $ip_address;
                $identifier_type = 'url';
                if (strpos(strrev($endpoint), strrev($this->response->format)) === 0) {
                    $endpoint = substr($endpoint, 0, -strlen($this->response->format) - 1);
                }
                $endpoint = $endpoint . ':' . $this->request->method;
            break;
        }

        // Get custom quota from user_api table if API key is provided
        $custom_quota = null;
        if (!empty($api_key)) {
            $this->rest->db->select('request_limit, time_window, burst_limit, quota_active');
            $this->rest->db->from(db_prefix() . 'user_api');
            $this->rest->db->where('token', $api_key);
            $custom_quota = $this->rest->db->get()->row();
        }

        // Determine limit and time window
        if ($custom_quota && $custom_quota->quota_active) {
            // Use custom quota from user_api table
            $limit = $custom_quota->request_limit;
            $time_limit = $custom_quota->time_window;
            $burst_limit = $custom_quota->burst_limit;
        } elseif (isset($this->methods[$controller_method]['limit'])) {
            // Use method-specific limit
            $limit = $this->methods[$controller_method]['limit'];
            $time_limit = isset($this->methods[$controller_method]['time']) ? $this->methods[$controller_method]['time'] : 3600;
            $burst_limit = 0;
        } else {
            // Use default limit from config
            $default_limit = $this->config->item('rest_default_limit');
            if ($default_limit === FALSE || empty($default_limit)) {
                // No limit configured, everything is fine
                return TRUE;
            }
            $limit = $default_limit;
            $time_limit = $this->config->item('rest_default_limit_time') ?: 3600;
            $burst_limit = 0;
        }

        // Calculate time threshold
        $time_threshold = time() - $time_limit;

        // Build query based on identifier type
        $this->rest->db->select('COUNT(*) as request_count');
        $this->rest->db->from($this->config->item('rest_limits_table'));
        $this->rest->db->where('timestamp >=', $time_threshold);
        
        if ($identifier_type === 'ip' || ($identifier_type !== 'api_key' && empty($api_key))) {
            $this->rest->db->where('ip_address', $ip_address);
        } else {
            $this->rest->db->where('api_key', $identifier);
        }
        
        // Add endpoint filtering if needed
        if ($identifier_type === 'method' || $identifier_type === 'url') {
            $this->rest->db->where('endpoint', $endpoint);
        }
        
        $result = $this->rest->db->get()->row();
        $current_count = $result ? $result->request_count : 0;

        // Check if limit is exceeded
        if ($current_count >= $limit) {
            // Check burst limit if configured
            if ($burst_limit > 0) {
                $burst_threshold = time() - 60; // 1 minute window for burst
                
                $this->rest->db->select('COUNT(*) as burst_count');
                $this->rest->db->from($this->config->item('rest_limits_table'));
                $this->rest->db->where('timestamp >=', $burst_threshold);
                
                if ($identifier_type === 'ip' || ($identifier_type !== 'api_key' && empty($api_key))) {
                    $this->rest->db->where('ip_address', $ip_address);
                } else {
                    $this->rest->db->where('api_key', $identifier);
                }
                
                $burst_result = $this->rest->db->get()->row();
                $burst_count = $burst_result ? $burst_result->burst_count : 0;
                
                if ($burst_count >= $burst_limit) {
                    // Store rate limit info for potential logging
                    $this->_rate_limit_log_data = [
                        'endpoint' => $endpoint,
                        'api_key' => $api_key ?: $ip_address,
                        'ip_address' => $ip_address,
                        'identifier_type' => $identifier_type,
                        'limit_type' => 'burst',
                        'limit' => $burst_limit,
                        'current_count' => $burst_count,
                        'time_window' => 60
                    ];
                    return FALSE;
                }
            } else {
                // Store rate limit info for potential logging
                $this->_rate_limit_log_data = [
                    'endpoint' => $endpoint,
                    'api_key' => $api_key ?: $ip_address,
                    'ip_address' => $ip_address,
                    'identifier_type' => $identifier_type,
                    'limit_type' => 'standard',
                    'limit' => $limit,
                    'current_count' => $current_count,
                    'time_window' => $time_limit
                ];
                return FALSE;
            }
        }

        // Store success rate limit check data
        $this->_rate_limit_log_data = [
            'endpoint' => $endpoint,
            'api_key' => $api_key ?: $ip_address,
            'ip_address' => $ip_address,
            'identifier_type' => $identifier_type,
            'limit_type' => 'success',
            'limit' => $limit,
            'current_count' => $current_count,
            'time_window' => $time_limit
        ];

        return TRUE;
    }

    /**
     * Check if there is a specific auth type set for the current class/method/HTTP-method being called
     *
     * @access protected
     * @return bool
     */
    protected function _auth_override_check() {
        // Assign the class/method auth type override array from the config
        $auth_override_class_method = $this->config->item('auth_override_class_method');

        // Check to see if the override array is even populated
        if (!empty($auth_override_class_method)) {
            // Check for wildcard flag for rules for classes
            if (!empty($auth_override_class_method[$this->router->class]['*'])) // Check for class overrides
            {
                // No auth override found, prepare nothing but send back a TRUE override flag
                if ($auth_override_class_method[$this->router->class]['*'] === 'none') {
                    return TRUE;
                }
                // Basic auth override found, prepare basic
                if ($auth_override_class_method[$this->router->class]['*'] === 'basic') {
                    $this->_prepare_basic_auth();
                    return TRUE;
                }
                // Digest auth override found, prepare digest
                if ($auth_override_class_method[$this->router->class]['*'] === 'digest') {
                    $this->_prepare_digest_auth();
                    return TRUE;
                }
                // Session auth override found, check session
                if ($auth_override_class_method[$this->router->class]['*'] === 'session') {
                    $this->_check_php_session();
                    return TRUE;
                }
                // Whitelist auth override found, check client's ip against config whitelist
                if ($auth_override_class_method[$this->router->class]['*'] === 'whitelist') {
                    $this->_check_whitelist_auth();
                    return TRUE;
                }
            }
            // Check to see if there's an override value set for the current class/method being called
            if (!empty($auth_override_class_method[$this->router->class][$this->router->method])) {
                // None auth override found, prepare nothing but send back a TRUE override flag
                if ($auth_override_class_method[$this->router->class][$this->router->method] === 'none') {
                    return TRUE;
                }
                // Basic auth override found, prepare basic
                if ($auth_override_class_method[$this->router->class][$this->router->method] === 'basic') {
                    $this->_prepare_basic_auth();
                    return TRUE;
                }
                // Digest auth override found, prepare digest
                if ($auth_override_class_method[$this->router->class][$this->router->method] === 'digest') {
                    $this->_prepare_digest_auth();
                    return TRUE;
                }
                // Session auth override found, check session
                if ($auth_override_class_method[$this->router->class][$this->router->method] === 'session') {
                    $this->_check_php_session();
                    return TRUE;
                }
                // Whitelist auth override found, check client's ip against config whitelist
                if ($auth_override_class_method[$this->router->class][$this->router->method] === 'whitelist') {
                    $this->_check_whitelist_auth();
                    return TRUE;
                }
            }
        }

        // Assign the class/method/HTTP-method auth type override array from the config
        $auth_override_class_method_http = $this->config->item('auth_override_class_method_http');

        // Check to see if the override array is even populated
        if (!empty($auth_override_class_method_http)) {
            // check for wildcard flag for rules for classes
            if (!empty($auth_override_class_method_http[$this->router->class]['*'][$this->request->method])) {
                // None auth override found, prepare nothing but send back a TRUE override flag
                if ($auth_override_class_method_http[$this->router->class]['*'][$this->request->method] === 'none') {
                    return TRUE;
                }
                // Basic auth override found, prepare basic
                if ($auth_override_class_method_http[$this->router->class]['*'][$this->request->method] === 'basic') {
                    $this->_prepare_basic_auth();
                    return TRUE;
                }
                // Digest auth override found, prepare digest
                if ($auth_override_class_method_http[$this->router->class]['*'][$this->request->method] === 'digest') {
                    $this->_prepare_digest_auth();
                    return TRUE;
                }
                // Session auth override found, check session
                if ($auth_override_class_method_http[$this->router->class]['*'][$this->request->method] === 'session') {
                    $this->_check_php_session();
                    return TRUE;
                }
                // Whitelist auth override found, check client's ip against config whitelist
                if ($auth_override_class_method_http[$this->router->class]['*'][$this->request->method] === 'whitelist') {
                    $this->_check_whitelist_auth();
                    return TRUE;
                }
            }
            // Check to see if there's an override value set for the current class/method/HTTP-method being called
            if (!empty($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method])) {
                // None auth override found, prepare nothing but send back a TRUE override flag
                if ($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method] === 'none') {
                    return TRUE;
                }
                // Basic auth override found, prepare basic
                if ($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method] === 'basic') {
                    $this->_prepare_basic_auth();
                    return TRUE;
                }
                // Digest auth override found, prepare digest
                if ($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method] === 'digest') {
                    $this->_prepare_digest_auth();
                    return TRUE;
                }
                // Session auth override found, check session
                if ($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method] === 'session') {
                    $this->_check_php_session();
                    return TRUE;
                }
                // Whitelist auth override found, check client's ip against config whitelist
                if ($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method] === 'whitelist') {
                    $this->_check_whitelist_auth();
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    /**
     * Parse the GET request arguments
     *
     * @access protected
     * @return void
     */
    protected function _parse_get() {
        // Merge both the URI segments and query parameters
        $this->_get_args = array_merge($this->_get_args, $this->_query_args);
    }

    /**
     * Parse the POST request arguments
     *
     * @access protected
     * @return void
     */
    protected function _parse_post() {
        // Handle JSON data for POST requests
        if ($this->request->format === 'json' || $this->input->get_request_header('Content-Type') === 'application/json') {
            $json_data = json_decode($this->security->xss_clean(file_get_contents("php://input")), true);
            if (!empty($json_data)) {
                $_POST = $json_data;
            }
        }

        $this->parse_json_data();

        $this->_post_args = $_POST;
        if ($this->request->format) {
            $this->request->body = $this->input->raw_input_stream;
        }
        
        // Automatically prepare form validation data for POST requests
        $this->prepare_form_validation();
    }

    /**
     * Parse the PUT request arguments
     *
     * @access protected
     * @return void
     */
    protected function _parse_put() {
        // Handle JSON data for PUT requests
        if ($this->request->format === 'json' || $this->input->get_request_header('Content-Type') === 'application/json') {
            $json_data = json_decode($this->security->xss_clean(file_get_contents("php://input")), true);
            if (!empty($json_data)) {
                $_POST = $json_data;
            }
        }
        
        $this->parse_json_data();
        
        if ($this->request->format) {
            $this->request->body = $this->input->raw_input_stream;
            if ($this->request->format === 'json') {
                $this->_put_args = json_decode($this->input->raw_input_stream);
            }
        } else if ($this->input->method() === 'put') {
            // If no file type is provided, then there are probably just arguments
            $this->_put_args = $this->input->input_stream();
        }
        
        // Automatically prepare form validation data for PUT requests
        $this->prepare_form_validation();
    }

    /**
     * Parse the HEAD request arguments
     *
     * @access protected
     * @return void
     */
    protected function _parse_head() {
        // Parse the HEAD variables
        parse_str(parse_url($this->input->server('REQUEST_URI'), PHP_URL_QUERY), $head);

        // Merge both the URI segments and HEAD params
        $this->_head_args = array_merge($this->_head_args, $head);
    }

    /**
     * Parse the OPTIONS request arguments
     *
     * @access protected
     * @return void
     */
    protected function _parse_options() {
        // Parse the OPTIONS variables
        parse_str(parse_url($this->input->server('REQUEST_URI'), PHP_URL_QUERY), $options);

        // Merge both the URI segments and OPTIONS params
        $this->_options_args = array_merge($this->_options_args, $options);
    }

    /**
     * Parse the PATCH request arguments
     *
     * @access protected
     * @return void
     */
    protected function _parse_patch() {
        // It might be a HTTP body
        if ($this->request->format) {
            $this->request->body = $this->input->raw_input_stream;
        } else if ($this->input->method() === 'patch') {
            // If no file type is provided, then there are probably just arguments
            $this->_patch_args = $this->input->input_stream();
        }
    }

    /**
     * Parse the DELETE request arguments
     *
     * @access protected
     * @return void
     */
    protected function _parse_delete() {
        // These should exist if a DELETE request
        if ($this->input->method() === 'delete') {
            $this->_delete_args = $this->input->input_stream();
        }
    }

    /**
     * Parse the query parameters
     *
     * @access protected
     * @return void
     */
    protected function _parse_query() {
        $this->_query_args = $this->input->get();
    }

    // INPUT FUNCTION --------------------------------------------------------------
    
    /**
     * Retrieve a value from a GET request
     *
     * @access public
     * @param NULL $key Key to retrieve from the GET request
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the GET request; otherwise, NULL
     */
    public function get($key = NULL, $xss_clean = NULL) {
        if ($key === NULL) {
            return $this->_get_args;
        }
        return isset($this->_get_args[$key]) ? $this->_xss_clean($this->_get_args[$key], $xss_clean) : NULL;
    }

    /**
     * Retrieve a value from a OPTIONS request
     *
     * @access public
     * @param NULL $key Key to retrieve from the OPTIONS request.
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the OPTIONS request; otherwise, NULL
     */
    public function options($key = NULL, $xss_clean = NULL) {
        if ($key === NULL) {
            return $this->_options_args;
        }
        return isset($this->_options_args[$key]) ? $this->_xss_clean($this->_options_args[$key], $xss_clean) : NULL;
    }

    /**
     * Retrieve a value from a HEAD request
     *
     * @access public
     * @param NULL $key Key to retrieve from the HEAD request
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the HEAD request; otherwise, NULL
     */
    public function head($key = NULL, $xss_clean = NULL) {
        if ($key === NULL) {
            return $this->_head_args;
        }
        return isset($this->_head_args[$key]) ? $this->_xss_clean($this->_head_args[$key], $xss_clean) : NULL;
    }

    /**
     * Retrieve a value from a POST request
     *
     * @access public
     * @param NULL $key Key to retrieve from the POST request
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the POST request; otherwise, NULL
     */
    public function post($key = NULL, $xss_clean = NULL) {
        if ($key === NULL) {
            return $this->_post_args;
        }
        return isset($this->_post_args[$key]) ? $this->_xss_clean($this->_post_args[$key], $xss_clean) : NULL;
    }

    /**
     * Retrieve a value from a PUT request
     *
     * @access public
     * @param NULL $key Key to retrieve from the PUT request
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the PUT request; otherwise, NULL
     */
    public function put($key = NULL, $xss_clean = NULL) {
        if ($key === NULL) {
            return $this->_put_args;
        }
        return isset($this->_put_args[$key]) ? $this->_xss_clean($this->_put_args[$key], $xss_clean) : NULL;
    }

    /**
     * Retrieve a value from a DELETE request
     *
     * @access public
     * @param NULL $key Key to retrieve from the DELETE request
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the DELETE request; otherwise, NULL
     */
    public function delete($key = NULL, $xss_clean = NULL) {
        if ($key === NULL) {
            return $this->_delete_args;
        }
        return isset($this->_delete_args[$key]) ? $this->_xss_clean($this->_delete_args[$key], $xss_clean) : NULL;
    }

    /**
     * Retrieve a value from a PATCH request
     *
     * @access public
     * @param NULL $key Key to retrieve from the PATCH request
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the PATCH request; otherwise, NULL
     */
    public function patch($key = NULL, $xss_clean = NULL) {
        if ($key === NULL) {
            return $this->_patch_args;
        }
        return isset($this->_patch_args[$key]) ? $this->_xss_clean($this->_patch_args[$key], $xss_clean) : NULL;
    }

    /**
     * Retrieve a value from the query parameters
     *
     * @access public
     * @param NULL $key Key to retrieve from the query parameters
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the query parameters; otherwise, NULL
     */
    public function query($key = NULL, $xss_clean = NULL) {
        if ($key === NULL) {
            return $this->_query_args;
        }
        return isset($this->_query_args[$key]) ? $this->_xss_clean($this->_query_args[$key], $xss_clean) : NULL;
    }

    /**
     * Sanitizes data so that Cross Site Scripting Hacks can be
     * prevented
     *
     * @access protected
     * @param string $value Input data
     * @param bool $xss_clean Whether to apply XSS filtering
     * @return string
     */
    protected function _xss_clean($value, $xss_clean) {
        is_bool($xss_clean) || $xss_clean = $this->_enable_xss;
        return $xss_clean === TRUE ? $this->security->xss_clean($value) : $value;
    }

    /**
     * Retrieve the validation errors
     *
     * @access public
     * @return array
     */
    public function validation_errors() {
        $string = strip_tags($this->form_validation->error_string() ?? '');
        return explode(PHP_EOL, trim($string, PHP_EOL));
    }

    // API v3 LIST/RESPONSE HELPERS ----------------------------------------------
    // All helpers below are strictly opt-in: they only change behaviour when the
    // caller sends the corresponding query parameters (page, per_page, sort,
    // fields, created_after/created_before). Requests without those parameters
    // keep the exact legacy response shape, so existing integrations are safe.

    /**
     * Parse the standard list query parameters.
     *
     * Supported: page (>=1), per_page (1-100, alias: limit ONLY when page is
     * also present - bare legacy ?limit= keeps its old SQL-level meaning),
     * sort=field,-other  (leading "-" = descending),
     * fields=id,company,... (column filtering),
     * created_after / created_before (aliases: date_from / date_to).
     *
     * @access protected
     * @return array
     */
    protected function api_list_params() {
        $page     = $this->get('page');
        $per_page = $this->get('per_page');

        // "limit" doubles as per_page, but only when the caller signals the new
        // paginated mode by also sending "page" - avoids double-limiting for
        // legacy clients whose ?limit= is applied at SQL level in the model.
        if ($per_page === NULL && $page !== NULL) {
            $per_page = $this->get('limit');
        }

        $has_pagination = ($page !== NULL || $per_page !== NULL);

        $page     = max(1, (int)($page !== NULL ? $page : 1));
        $per_page = (int)($per_page !== NULL ? $per_page : 25);
        $per_page = max(1, min(100, $per_page));

        $sort_raw = (string)($this->get('sort') ?? '');
        $sorts    = [];
        foreach (array_filter(array_map('trim', explode(',', $sort_raw))) as $part) {
            $dir = 'asc';
            if ($part[0] === '-') {
                $dir  = 'desc';
                $part = substr($part, 1);
            }
            if ($part !== '') {
                $sorts[] = ['field' => $part, 'dir' => $dir];
            }
        }

        $fields_raw = (string)($this->get('fields') ?? '');
        $fields     = array_values(array_filter(array_map('trim', explode(',', $fields_raw))));

        $created_after  = $this->get('created_after');
        $created_after  = $created_after !== NULL ? $created_after : $this->get('date_from');
        $created_before = $this->get('created_before');
        $created_before = $created_before !== NULL ? $created_before : $this->get('date_to');

        return [
            'has_pagination' => $has_pagination,
            'page'           => $page,
            'per_page'       => $per_page,
            'sorts'          => $sorts,
            'fields'         => $fields,
            'created_after'  => $created_after,
            'created_before' => $created_before,
        ];
    }

    /**
     * Apply ?fields= column filtering to a row or a list of rows.
     * The id column (when present) is always kept.
     *
     * @access protected
     * @param array $data Row (assoc) or list of rows
     * @param array|NULL $fields Wanted columns; NULL reads the query parameter
     * @return array
     */
    protected function api_apply_fields($data, $fields = NULL) {
        if ($fields === NULL) {
            $params = $this->api_list_params();
            $fields = $params['fields'];
        }
        if (!is_array($data) || empty($fields)) {
            return $data;
        }

        $wanted = array_unique(array_merge(['id'], $fields));
        $filter_row = function ($row) use ($wanted) {
            $row = is_object($row) ? (array)$row : $row;
            if (!is_array($row)) {
                return $row;
            }
            return array_intersect_key($row, array_flip($wanted));
        };

        // Assoc row (single resource) vs list of rows
        if ($data !== [] && array_keys($data) !== range(0, count($data) - 1)) {
            return $filter_row($data);
        }
        return array_map($filter_row, $data);
    }

    /**
     * Apply date filtering, sorting, field filtering and pagination to an
     * in-memory result set. Returns [rows, meta] where meta is NULL when the
     * caller did not request pagination (legacy mode).
     *
     * @access protected
     * @param array $rows List of rows (arrays or objects)
     * @param array $options ['date_field' => string] override for date filtering
     * @return array [array $rows, array|NULL $meta]
     */
    protected function api_apply_list_features(array $rows, $options = []) {
        $params = $this->api_list_params();

        // Controllers that always paginated historically (via apply_pagination)
        // keep that behaviour through these options.
        if (!empty($options['force_pagination'])) {
            $params['has_pagination'] = TRUE;
        }
        if (isset($options['default_per_page']) && $this->get('per_page') === NULL
            && !($this->get('page') !== NULL && $this->get('limit') !== NULL)) {
            $params['per_page'] = max(1, min(100, (int)$options['default_per_page']));
        }

        // Normalise objects to arrays so downstream handling is uniform
        $rows = array_map(function ($row) {
            return is_object($row) ? (array)$row : $row;
        }, array_values($rows));

        // 1. Date-range filtering (only when requested and resolvable)
        if ($params['created_after'] !== NULL || $params['created_before'] !== NULL) {
            $after  = $params['created_after'] !== NULL ? strtotime((string)$params['created_after']) : NULL;
            $before = $params['created_before'] !== NULL ? strtotime((string)$params['created_before']) : NULL;

            if (($params['created_after'] !== NULL && $after === FALSE)
                || ($params['created_before'] !== NULL && $before === FALSE)) {
                $this->api_validation_error(
                    ['created_after' => 'Dates must be ISO formatted, e.g. 2026-01-31 or 2026-01-31 23:59:59'],
                    'Invalid date filter'
                );
            }

            $candidates = isset($options['date_field'])
                ? [$options['date_field']]
                : ['datecreated', 'date_created', 'dateadded', 'created_at', 'date', 'startdate'];

            $rows = array_values(array_filter($rows, function ($row) use ($after, $before, $candidates) {
                if (!is_array($row)) {
                    return TRUE;
                }
                $value = NULL;
                foreach ($candidates as $field) {
                    if (isset($row[$field]) && $row[$field] !== '' && $row[$field] !== NULL) {
                        $value = strtotime((string)$row[$field]);
                        break;
                    }
                }
                if ($value === NULL || $value === FALSE) {
                    return TRUE; // rows without a usable date are kept
                }
                if ($after !== NULL && $value < $after) {
                    return FALSE;
                }
                if ($before !== NULL && $value > $before) {
                    return FALSE;
                }
                return TRUE;
            }));
        }

        // 2. Sorting (numeric-aware, multi-key, "-" prefix = descending)
        if (!empty($params['sorts'])) {
            $sorts = $params['sorts'];
            usort($rows, function ($a, $b) use ($sorts) {
                foreach ($sorts as $sort) {
                    $av = is_array($a) && isset($a[$sort['field']]) ? $a[$sort['field']] : NULL;
                    $bv = is_array($b) && isset($b[$sort['field']]) ? $b[$sort['field']] : NULL;
                    if ($av == $bv) {
                        continue;
                    }
                    if (is_numeric($av) && is_numeric($bv)) {
                        $cmp = (float)$av <=> (float)$bv;
                    } else {
                        $cmp = strcasecmp((string)$av, (string)$bv);
                    }
                    if ($cmp !== 0) {
                        return $sort['dir'] === 'desc' ? -$cmp : $cmp;
                    }
                }
                return 0;
            });
        }

        // 3. Pagination (envelope mode only when explicitly requested)
        $meta  = NULL;
        $total = count($rows);
        if ($params['has_pagination']) {
            $total_pages = (int)max(1, ceil($total / $params['per_page']));
            // Clamp page to the last page (legacy apply_pagination behaviour)
            $page        = min($params['page'], $total_pages);
            $offset      = ($page - 1) * $params['per_page'];
            $rows        = array_slice($rows, $offset, $params['per_page']);
            $meta        = [
                // New documented keys
                'page'         => $page,
                'per_page'     => $params['per_page'],
                'total'        => $total,
                'total_pages'  => $total_pages,
                'has_more'     => $page < $total_pages,
                // Legacy keys kept for integrations built on apply_pagination()
                'current_page' => $page,
                'last_page'    => $total_pages,
            ];
        }

        // 4. Column filtering
        if (!empty($params['fields'])) {
            $rows = $this->api_apply_fields($rows, $params['fields']);
        }

        return [$rows, $meta];
    }

    /**
     * Respond with a list. Legacy callers (no pagination parameters) receive
     * the plain array exactly as before; paginated callers receive
     * { status, data, meta }.
     *
     * @access protected
     * @param array $rows List of rows
     * @param array $options Options forwarded to api_apply_list_features()
     * @return void
     */
    protected function api_list_response(array $rows, $options = []) {
        list($rows, $meta) = $this->api_apply_list_features($rows, $options);

        if ($meta === NULL) {
            $this->response($rows, self::HTTP_OK);
            return;
        }

        // Same envelope shape the always-paginated controllers already use
        $this->response([
            'data' => $rows,
            'meta' => $meta,
        ], self::HTTP_OK);
    }

    /**
     * Drop payload keys that are neither real columns of the target table nor
     * explicitly whitelisted virtual inputs (custom_fields, tags, newitems...).
     * Prevents "Unknown column" SQL errors (HTTP 500) on partial updates when
     * callers send extra/unrecognized fields.
     *
     * @access protected
     * @param string $table Table name WITHOUT the Perfex prefix
     * @param array $payload Raw update payload
     * @param array $extra_allowed Non-column input keys to keep
     * @return array Sanitized payload
     */
    protected function api_sanitize_update_payload($table, array $payload, array $extra_allowed = []) {
        $columns = [];
        try {
            if (isset($this->db) && function_exists('db_prefix')) {
                $columns = $this->db->list_fields(db_prefix() . $table);
            }
        } catch (Exception $e) {
            $columns = [];
        } catch (Throwable $e) {
            $columns = [];
        }

        // If the column list cannot be resolved, keep the payload untouched
        // rather than dropping everything.
        if (empty($columns)) {
            return $payload;
        }

        $allowed = array_flip(array_merge($columns, $extra_allowed));
        return array_intersect_key($payload, $allowed);
    }

    /**
     * Merge a partial update payload over the existing record so that fields
     * the caller did not send keep their current values (instead of being
     * blanked or failing "required" validation rules).
     *
     * Only keys present on the existing record are merged in from it; virtual
     * inputs (custom_fields, items...) pass through only when the caller sent
     * them, so downstream handlers never see keys the caller did not intend.
     *
     * @access protected
     * @param array|object $existing Current record
     * @param array $payload Caller-provided fields
     * @param array $skip Existing fields never to copy back (e.g. id, hash)
     * @return array Complete update dataset
     */
    protected function api_merge_partial_update($existing, array $payload, array $skip = ['id']) {
        $existing = is_object($existing) ? (array)$existing : (array)$existing;
        $merged   = $payload;

        foreach ($existing as $field => $value) {
            if (in_array($field, $skip, TRUE)) {
                continue;
            }
            if (!array_key_exists($field, $merged)) {
                $merged[$field] = $value;
            }
        }

        return $merged;
    }

    /**
     * Accept a GET-shaped items[] array on POST/PUT and convert it to the
     * legacy newitems[] structure the Perfex models expect. Taxes may be given
     * as taxname strings ("VAT|19.00"), arrays of those strings, or as
     * taxes: [{name, rate}] objects.
     *
     * No-op when the caller already sends newitems[] or sends no items at all.
     *
     * @access protected
     * @return void
     */
    protected function api_normalize_line_items() {
        if (!empty($_POST['newitems']) || empty($_POST['items']) || !is_array($_POST['items'])) {
            return;
        }

        $newitems = [];
        $position = 1;
        foreach (array_values($_POST['items']) as $index => $item) {
            if (!is_array($item)) {
                continue;
            }
            $row = [
                'description'      => isset($item['description']) ? $item['description'] : '',
                'long_description' => isset($item['long_description']) ? $item['long_description'] : '',
                'qty'              => isset($item['qty']) ? $item['qty'] : 1,
                'rate'             => isset($item['rate']) ? $item['rate'] : 0,
                'unit'             => isset($item['unit']) ? $item['unit'] : '',
                'order'            => isset($item['order']) ? $item['order'] : $position,
            ];

            $taxnames = [];
            if (!empty($item['taxname'])) {
                $taxnames = is_array($item['taxname']) ? $item['taxname'] : [$item['taxname']];
            } elseif (!empty($item['taxes']) && is_array($item['taxes'])) {
                foreach ($item['taxes'] as $tax) {
                    if (is_array($tax) && isset($tax['name'], $tax['rate'])) {
                        $taxnames[] = $tax['name'] . '|' . $tax['rate'];
                    } elseif (is_string($tax) && $tax !== '') {
                        $taxnames[] = $tax;
                    }
                }
            }
            if (!empty($taxnames)) {
                $row['taxname'] = $taxnames;
            }

            $newitems[$index + 1] = $row;
            $position++;
        }

        if (!empty($newitems)) {
            $_POST['newitems'] = $newitems;
            unset($_POST['items']);
        }
    }

    /**
     * Server-side totals: when the caller omits subtotal and/or total they are
     * calculated from newitems[] (qty x rate), per-item taxes ("NAME|RATE"),
     * discount (discount_percent or discount_total) and adjustment - so API
     * clients no longer have to pre-calculate invoice math themselves.
     *
     * Caller-provided values always win; nothing is overwritten.
     *
     * @access protected
     * @return void
     */
    protected function api_autocalculate_totals() {
        $has_subtotal = isset($_POST['subtotal']) && $_POST['subtotal'] !== '';
        $has_total    = isset($_POST['total']) && $_POST['total'] !== '';
        if (($has_subtotal && $has_total) || empty($_POST['newitems']) || !is_array($_POST['newitems'])) {
            return;
        }

        $subtotal  = 0.0;
        $tax_total = 0.0;
        foreach ($_POST['newitems'] as $item) {
            if (!is_array($item)) {
                continue;
            }
            $qty  = isset($item['qty']) ? (float)$item['qty'] : 0.0;
            $rate = isset($item['rate']) ? (float)$item['rate'] : 0.0;
            $line = $qty * $rate;
            $subtotal += $line;

            if (!empty($item['taxname'])) {
                $taxes = is_array($item['taxname']) ? $item['taxname'] : [$item['taxname']];
                foreach ($taxes as $tax) {
                    if (is_string($tax) && strpos($tax, '|') !== FALSE) {
                        $tax_rate = (float)substr($tax, strrpos($tax, '|') + 1);
                    } else {
                        $tax_rate = (float)$tax;
                    }
                    $tax_total += $line * $tax_rate / 100.0;
                }
            }
        }

        $discount = 0.0;
        if (isset($_POST['discount_percent']) && (float)$_POST['discount_percent'] > 0) {
            $discount = $subtotal * (float)$_POST['discount_percent'] / 100.0;
            if (!isset($_POST['discount_total']) || $_POST['discount_total'] === '') {
                $_POST['discount_total'] = round($discount, 2);
            }
        } elseif (isset($_POST['discount_total'])) {
            $discount = (float)$_POST['discount_total'];
        }
        $adjustment = isset($_POST['adjustment']) ? (float)$_POST['adjustment'] : 0.0;

        if (!$has_subtotal) {
            $_POST['subtotal'] = round($subtotal, 2);
        }
        if (!$has_total) {
            $_POST['total'] = round($subtotal - $discount + $tax_total + $adjustment, 2);
        }
    }

    /** Idempotency-Key pending storage marker (v3) */
    protected $_api_idempotency_key = NULL;

    /**
     * Reject requests from IPs with too many recent failed authentications
     * (v3 brute-force protection; window 15 minutes, default limit 20,
     * configurable via api_auth_throttle_limit, '0' disables).
     *
     * @access protected
     * @return void
     */
    protected function _auth_throttle_check() {
        try {
            $limit_option = function_exists('get_option') ? get_option('api_auth_throttle_limit') : '';
            if ($limit_option === '0') {
                return;
            }
            $limit = (int)$limit_option > 0 ? (int)$limit_option : 20;

            $count = $this->db
                ->where('uri', 'api_auth_failure')
                ->where('ip_address', $this->input->ip_address())
                ->where('time >=', time() - 900)
                ->count_all_results(db_prefix() . 'api_limit');

            if ($count >= $limit) {
                $this->response([
                    'status'  => FALSE,
                    'message' => 'Too many failed authentication attempts. Try again later.',
                ], 429);
            }
        } catch (Exception $e) {
            // Throttling must never take the API down
        }
    }

    /**
     * Record a failed authentication for the throttle window (v3).
     *
     * @access protected
     * @return void
     */
    protected function _register_auth_failure() {
        try {
            $this->db->insert(db_prefix() . 'api_limit', [
                'uri'        => 'api_auth_failure',
                'class'      => 'auth',
                'method'     => 'token',
                'ip_address' => $this->input->ip_address(),
                'time'       => time(),
            ]);
        } catch (Exception $e) {
            // Logging failures must never break the response
        }
    }

    /**
     * Opt-in staff-level data visibility (v3): when api_staff_visibility is
     * enabled and the token is linked to a non-admin staff member
     * (user_api.staff_id), bootstrap the staff session context so Perfex core
     * models scope data exactly as they do in the admin panel.
     *
     * @access protected
     * @param string $token
     * @return void
     */
    protected function _apply_staff_visibility($token) {
        try {
            if (!function_exists('get_option') || get_option('api_staff_visibility') !== '1' || $token === '') {
                return;
            }
            $columns = $this->db->list_fields(db_prefix() . 'user_api');
            if (!in_array('staff_id', $columns, TRUE)) {
                return;
            }
            $row = $this->db->where('token', $token)->get(db_prefix() . 'user_api')->row_array();
            if (!$row || empty($row['staff_id'])) {
                return;
            }
            $staff = $this->db->where('staffid', (int)$row['staff_id'])->get(db_prefix() . 'staff')->row_array();
            if (!$staff || (int)$staff['admin'] === 1 || (int)$staff['active'] !== 1) {
                return; // admins and missing/inactive staff keep full API visibility
            }

            $context = [
                'staff_user_id'   => (int)$staff['staffid'],
                'staff_logged_in' => TRUE,
            ];
            if (isset($this->session) && is_object($this->session) && method_exists($this->session, 'set_userdata')) {
                $this->session->set_userdata($context);
            } else {
                foreach ($context as $sessionKey => $sessionValue) {
                    $_SESSION[$sessionKey] = $sessionValue;
                }
            }
        } catch (Exception $e) {
            // Visibility bootstrap is best-effort; never break the request
        }
    }

    /**
     * Emit X-RateLimit-* headers from the last quota check (v3).
     *
     * @access protected
     * @return void
     */
    protected function _emit_rate_limit_headers() {
        if (headers_sent() || empty($this->_rate_limit_log_data) || empty($this->_rate_limit_log_data['limit'])) {
            return;
        }
        $data = $this->_rate_limit_log_data;
        header('X-RateLimit-Limit: ' . (int)$data['limit']);
        header('X-RateLimit-Remaining: ' . max(0, (int)$data['limit'] - (int)$data['current_count']));
        if (!empty($data['time_window'])) {
            header('X-RateLimit-Reset: ' . (time() + (int)$data['time_window']));
        }
    }

    /**
     * Replay a stored response when the caller repeats a POST with the same
     * Idempotency-Key (v3). Runs right before the controller method executes;
     * when a stored response exists it is sent (with Idempotency-Replayed:
     * true) and the request never re-executes.
     *
     * @access protected
     * @return void
     */
    protected function _idempotency_check() {
        if (!isset($this->request->method) || $this->request->method !== 'post') {
            return;
        }
        $key = isset($_SERVER['HTTP_IDEMPOTENCY_KEY']) ? trim((string)$_SERVER['HTTP_IDEMPOTENCY_KEY']) : '';
        if ($key === '' || strlen($key) > 191) {
            return;
        }
        if (!$this->db->table_exists(db_prefix() . 'api_idempotency_keys')) {
            return;
        }

        $token = isset($this->rest->key) ? (string)$this->rest->key : '';
        $row   = $this->db
            ->where('idem_key', $key)
            ->where('api_key', $token)
            ->get(db_prefix() . 'api_idempotency_keys')
            ->row_array();

        if ($row) {
            if (!headers_sent()) {
                header('Idempotency-Replayed: true');
            }
            $decoded = json_decode($row['response_body'], TRUE);
            $this->response($decoded !== NULL ? $decoded : $row['response_body'], (int)$row['response_code']);
            return; // unreachable - response() exits
        }

        // Nothing stored yet: remember the key so response() persists the outcome
        $this->_api_idempotency_key = $key;
    }

    /**
     * Persist the outgoing response under the pending Idempotency-Key (v3).
     * Server errors (5xx) are never stored so transient failures can retry.
     *
     * @access protected
     * @param mixed $data
     * @param int|NULL $http_code
     * @return void
     */
    protected function _idempotency_store($data, $http_code) {
        if ($this->_api_idempotency_key === NULL || $data === NULL) {
            return;
        }
        $code = $http_code !== NULL ? (int)$http_code : 200;
        $key  = $this->_api_idempotency_key;
        $this->_api_idempotency_key = NULL; // single-shot

        if ($code >= 500) {
            return;
        }

        try {
            $token = isset($this->rest->key) ? (string)$this->rest->key : '';
            // INSERT IGNORE: a concurrent retry racing us keeps the first row
            $this->db->query(
                'INSERT IGNORE INTO `' . db_prefix() . 'api_idempotency_keys`
                 (idem_key, api_key, method, endpoint, response_code, response_body, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?)',
                [
                    $key,
                    $token,
                    isset($this->request->method) ? $this->request->method : 'post',
                    isset($this->uri) ? $this->uri->uri_string() : '',
                    $code,
                    json_encode($data),
                    date('Y-m-d H:i:s'),
                ]
            );
        } catch (Exception $e) {
            // Persistence problems must never break the actual response
        }
    }

    /**
     * Fire a webhook event from an API mutation (used by resources that write
     * directly to the database and therefore never pass through Perfex core
     * hooks). Never throws - webhook failures must not break API responses.
     *
     * @access protected
     * @param string $event Catalog event name
     * @param array $data Event payload
     * @return void
     */
    protected function api_fire_event($event, array $data = []) {
        try {
            if (!$this->db->table_exists(db_prefix() . 'api_webhooks')) {
                return;
            }
            if (!class_exists('Api_Webhook_Service')) {
                require_once __DIR__ . '/../libraries/Api_Webhook_Service.php';
            }
            $service = new Api_Webhook_Service();
            $service->triggerWebhooks($event, $data);
        } catch (Exception $e) {
            if (function_exists('log_message')) {
                log_message('error', 'API webhook fire (' . $event . '): ' . $e->getMessage());
            }
        }
    }

    /**
     * Respond with a structured 422 validation error:
     * { status, error, message, errors: { field: message } }.
     * With no explicit $errors the current form_validation errors are used.
     *
     * @access protected
     * @param array|string|NULL $errors Per-field errors
     * @param string $message Human summary
     * @return void
     */
    protected function api_validation_error($errors = NULL, $message = 'Validation failed') {
        if ($errors === NULL && isset($this->form_validation) && method_exists($this->form_validation, 'error_array')) {
            $errors = $this->form_validation->error_array();
        }
        if (!is_array($errors)) {
            $errors = ['_general' => (string)$errors];
        }

        $this->response([
            'status'  => FALSE,
            'error'   => 'validation_failed',
            'message' => $message,
            'errors'  => $errors,
        ], self::HTTP_UNPROCESSABLE_ENTITY);
    }

    // SECURITY FUNCTIONS ---------------------------------------------------------
    
    /**
     * Perform LDAP Authentication
     *
     * @access protected
     * @param string $username The username to validate
     * @param string $password The password to validate
     * @return bool
     */
    protected function _perform_ldap_auth($username = '', $password = NULL) {
        if (empty($username)) {
            log_message('debug', 'LDAP Auth: failure, empty username');
            return FALSE;
        }
        log_message('debug', 'LDAP Auth: Loading configuration');
        $this->config->load('ldap', TRUE);
        $ldap = ['timeout' => $this->config->item('timeout', 'ldap'), 'host' => $this->config->item('server', 'ldap'), 'port' => $this->config->item('port', 'ldap'), 'rdn' => $this->config->item('binduser', 'ldap'), 'pass' => $this->config->item('bindpw', 'ldap'), 'basedn' => $this->config->item('basedn', 'ldap'), ];
        log_message('debug', 'LDAP Auth: Connect to ' . (isset($ldaphost) ? $ldaphost : '[ldap not configured]'));

        // Connect to the ldap server
        $ldapconn = ldap_connect($ldap['host'], $ldap['port']);
        if ($ldapconn) {
            log_message('debug', 'Setting timeout to ' . $ldap['timeout'] . ' seconds');
            ldap_set_option($ldapconn, LDAP_OPT_NETWORK_TIMEOUT, $ldap['timeout']);
            log_message('debug', 'LDAP Auth: Binding to ' . $ldap['host'] . ' with dn ' . $ldap['rdn']);
            // Binding to the ldap server
            $ldapbind = ldap_bind($ldapconn, $ldap['rdn'], $ldap['pass']);
            // Verify the binding
            if ($ldapbind === FALSE) {
                log_message('error', 'LDAP Auth: bind was unsuccessful');
                return FALSE;
            }
            log_message('debug', 'LDAP Auth: bind successful');
        }

        // Search for user
        if (($res_id = ldap_search($ldapconn, $ldap['basedn'], "uid=$username")) === FALSE) {
            log_message('error', 'LDAP Auth: User ' . $username . ' not found in search');
            return FALSE;
        }
        if (ldap_count_entries($ldapconn, $res_id) !== 1) {
            log_message('error', 'LDAP Auth: Failure, username ' . $username . 'found more than once');
            return FALSE;
        }
        if (($entry_id = ldap_first_entry($ldapconn, $res_id)) === FALSE) {
            log_message('error', 'LDAP Auth: Failure, entry of search result could not be fetched');
            return FALSE;
        }
        if (($user_dn = ldap_get_dn($ldapconn, $entry_id)) === FALSE) {
            log_message('error', 'LDAP Auth: Failure, user-dn could not be fetched');
            return FALSE;
        }

        // User found, could not authenticate as user
        if (($link_id = ldap_bind($ldapconn, $user_dn, $password)) === FALSE) {
            log_message('error', 'LDAP Auth: Failure, username/password did not match: ' . $user_dn);
            return FALSE;
        }
        log_message('debug', 'LDAP Auth: Success ' . $user_dn . ' authenticated successfully');
        $this->_user_ldap_dn = $user_dn;
        ldap_close($ldapconn);
        return TRUE;
    }

    /**
     * Perform Library Authentication - Override this function to change the way the library is called
     *
     * @access protected
     * @param string $username The username to validate
     * @param string $password The password to validate
     * @return bool
     */
    protected function _perform_library_auth($username = '', $password = NULL) {
        if (empty($username)) {
            log_message('error', 'Library Auth: Failure, empty username');
            return FALSE;
        }
        $auth_library_class = strtolower($this->config->item('auth_library_class'));
        $auth_library_function = strtolower($this->config->item('auth_library_function'));
        if (empty($auth_library_class)) {
            log_message('debug', 'Library Auth: Failure, empty auth_library_class');
            return FALSE;
        }
        if (empty($auth_library_function)) {
            log_message('debug', 'Library Auth: Failure, empty auth_library_function');
            return FALSE;
        }
        if (is_callable([$auth_library_class, $auth_library_function]) === FALSE) {
            $this->load->library($auth_library_class);
        }
        return $this->{$auth_library_class}->$auth_library_function($username, $password);
    }

    /**
     * Check if the user is logged in
     *
     * @access protected
     * @param string $username The user's name
     * @param bool|string $password The user's password
     * @return bool
     */
    protected function _check_login($username = NULL, $password = FALSE) {
        if (empty($username)) {
            return FALSE;
        }
        $auth_source = strtolower($this->config->item('auth_source'));
        $rest_auth = strtolower($this->config->item('rest_auth'));
        $valid_logins = $this->config->item('rest_valid_logins');
        if (!$this->config->item('auth_source') && $rest_auth === 'digest') {
            // For digest we do not have a password passed as argument
            return md5($username . ':' . $this->config->item('rest_realm') . ':' . (isset($valid_logins[$username]) ? $valid_logins[$username] : ''));
        }
        if ($password === FALSE) {
            return FALSE;
        }
        if ($auth_source === 'ldap') {
            log_message('debug', "Performing LDAP authentication for $username");
            return $this->_perform_ldap_auth($username, $password);
        }
        if ($auth_source === 'library') {
            log_message('debug', "Performing Library authentication for $username");
            return $this->_perform_library_auth($username, $password);
        }
        if (array_key_exists($username, $valid_logins) === FALSE) {
            return FALSE;
        }
        if ($valid_logins[$username] !== $password) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Check to see if the user is logged in with a PHP session key
     *
     * @access protected
     * @return void
     */
    protected function _check_php_session() {
        // Get the auth_source config item
        $key = $this->config->item('auth_source');

        // If false, then the user isn't logged in
        if (!$this->session->userdata($key)) {
            // Display an error response
            $this->response([$this->config->item('rest_status_field_name') => FALSE, $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_unauthorized') ], self::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Prepares for basic authentication
     *
     * @access protected
     * @return void
     */
    protected function _prepare_basic_auth() {
        // If whitelist is enabled it has the first chance to kick them out
        if ($this->config->item('rest_ip_whitelist_enabled')) {
            $this->_check_whitelist_auth();
        }

        // Returns NULL if the SERVER variables PHP_AUTH_USER and HTTP_AUTHENTICATION don't exist
        $username = $this->input->server('PHP_AUTH_USER');
        $http_auth = $this->input->server('HTTP_AUTHENTICATION') ? : $this->input->server('HTTP_AUTHORIZATION');
        $password = NULL;
        if ($username !== NULL) {
            $password = $this->input->server('PHP_AUTH_PW');
        } elseif ($http_auth !== NULL) {
            // If the authentication header is set as basic, then extract the username and password from
            // HTTP_AUTHORIZATION e.g. my_username:my_password. This is passed in the .htaccess file
            if (strpos(strtolower($http_auth), 'basic') === 0) {
                // Search online for HTTP_AUTHORIZATION workaround to explain what this is doing
                list($username, $password) = explode(':', base64_decode(substr($this->input->server('HTTP_AUTHORIZATION'), 6)));
            }
        }

        // Check if the user is logged into the system
        if ($this->_check_login($username, $password) === FALSE) {
            $this->_force_login();
        }
    }

    /**
     * Prepares for digest authentication
     *
     * @access protected
     * @return void
     */
    protected function _prepare_digest_auth() {
        // If whitelist is enabled it has the first chance to kick them out
        if ($this->config->item('rest_ip_whitelist_enabled')) {
            $this->_check_whitelist_auth();
        }

        // We need to test which server authentication variable to use,
        // because the PHP ISAPI module in IIS acts different from CGI
        $digest_string = $this->input->server('PHP_AUTH_DIGEST');
        if ($digest_string === NULL) {
            $digest_string = $this->input->server('HTTP_AUTHORIZATION');
        }
        $unique_id = uniqid();

        // The $_SESSION['error_prompted'] variable is used to ask the password
        // again if none given or if the user enters wrong auth information
        if (empty($digest_string)) {
            $this->_force_login($unique_id);
        }

        // We need to retrieve authentication data from the $digest_string variable
        $matches = [];
        preg_match_all('@(username|nonce|uri|nc|cnonce|qop|response)=[\'"]?([^\'",]+)@', $digest_string, $matches);
        $digest = (empty($matches[1]) || empty($matches[2])) ? [] : array_combine($matches[1], $matches[2]);

        // For digest authentication the library function should return already stored md5(username:restrealm:password) for that username see rest.php::auth_library_function config
        $username = $this->_check_login($digest['username'], TRUE);
        if (array_key_exists('username', $digest) === FALSE || $username === FALSE) {
            $this->_force_login($unique_id);
        }
        $md5 = md5(strtoupper($this->request->method) . ':' . $digest['uri']);
        $valid_response = md5($username . ':' . $digest['nonce'] . ':' . $digest['nc'] . ':' . $digest['cnonce'] . ':' . $digest['qop'] . ':' . $md5);

        // Check if the string don't compare (case-insensitive)
        if (strcasecmp($digest['response'], $valid_response) !== 0) {
            // Display an error response
            $this->response([$this->config->item('rest_status_field_name') => FALSE, $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_invalid_credentials') ], self::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Checks if the client's ip is in the 'rest_ip_blacklist' config and generates a 401 response
     *
     * @access protected
     * @return void
     */
    protected function _check_blacklist_auth() {
        // Match an ip address in a blacklist e.g. 127.0.0.0, 0.0.0.0
        $pattern = sprintf('/(?:,\s*|^)\Q%s\E(?=,\s*|$)/m', $this->input->ip_address());

        // Returns 1, 0 or FALSE (on error only). Therefore implicitly convert 1 to TRUE
        if (preg_match($pattern, $this->config->item('rest_ip_blacklist'))) {
            // Display an error response
            $this->response([$this->config->item('rest_status_field_name') => FALSE, $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_ip_denied') ], self::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Check if the client's ip is in the 'rest_ip_whitelist' config and generates a 401 response
     *
     * @access protected
     * @return void
     */
    protected function _check_whitelist_auth() {
        $whitelist = explode(',', $this->config->item('rest_ip_whitelist'));
        array_push($whitelist, '127.0.0.1', '0.0.0.0');
        foreach ($whitelist as & $ip) {
            // As $ip is a reference, trim leading and trailing whitespace, then store the new value
            // using the reference
            $ip = trim($ip ?? '');
        }
        if (in_array($this->input->ip_address(), $whitelist) === FALSE) {
            $this->response([$this->config->item('rest_status_field_name') => FALSE, $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_ip_unauthorized') ], self::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Force logging in by setting the WWW-Authenticate header
     *
     * @access protected
     * @param string $nonce A server-specified data string which should be uniquely generated
     * each time
     * @return void
     */
    protected function _force_login($nonce = '') {
        $rest_auth = $this->config->item('rest_auth');
        $rest_realm = $this->config->item('rest_realm');
        if (strtolower($rest_auth) === 'basic') {
            // See http://tools.ietf.org/html/rfc2617#page-5
            header('WWW-Authenticate: Basic realm="' . $rest_realm . '"');
        } elseif (strtolower($rest_auth) === 'digest') {
            // See http://tools.ietf.org/html/rfc2617#page-18
            header('WWW-Authenticate: Digest realm="' . $rest_realm . '", qop="auth", nonce="' . $nonce . '", opaque="' . md5($rest_realm) . '"');
        }
        if ($this->config->item('strict_api_and_auth') === true) {
            $this->is_valid_request = false;
        }

        // Display an error response
        $this->response([$this->config->item('rest_status_field_name') => FALSE, $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_unauthorized') ], self::HTTP_UNAUTHORIZED);
    }

    /**
     * Updates the log table with the total access time
     *
     * @access protected
     * @author Chris Kacerguis
     * @return bool TRUE log table updated; otherwise, FALSE
     */
    protected function _log_access_time() {
        if ($this->_insert_id == '') {
            return false;
        }
        $payload['rtime'] = $this->_end_rtime - $this->_start_rtime;
        return $this->rest->db->update($this->config->item('rest_logs_table'), $payload, ['id' => $this->_insert_id]);
    }

    /**
     * Updates the log table with HTTP response code
     *
     * @access protected
     * @author Justin Chen
     * @param $http_code int HTTP status code
     * @return bool TRUE log table updated; otherwise, FALSE
     */
    protected function _log_response_code($http_code) {
        if ($this->_insert_id == '') {
            return false;
        }
        $payload['response_code'] = $http_code;
        return $this->rest->db->update($this->config->item('rest_logs_table'), $payload, ['id' => $this->_insert_id]);
    }

    /**
     * Check to see if the API key has access to the controller and methods
     *
     * @access protected
     * @return bool TRUE the API key has access; otherwise, FALSE
     */
    protected function _check_access() {
        // If we don't want to check access, just return TRUE
        if ($this->config->item('rest_enable_access') === FALSE) {
            return TRUE;
        }
        //check if the key has all_access
        $accessRow = $this->rest->db->where('key', $this->rest->key)->get($this->config->item('rest_access_table'))->row_array();
        if (!empty($accessRow) && !empty($accessRow['all_access'])) {
            return TRUE;
        }

        // Fetch controller based on path and controller name
        $controller = implode('/', [$this->router->directory, $this->router->class]);

        // Remove any double slashes for safety
        $controller = str_replace('//', '/', $controller);

        // Query the access table and get the number of results
        return $this->rest->db->where('key', $this->rest->key)->where('controller', $controller)->get($this->config->item('rest_access_table'))->num_rows() > 0;
    }

    /**
     * Checks allowed domains, and adds appropriate headers for HTTP access control (CORS)
     *
     * @access protected
     * @return void
     */
    protected function _check_cors() {
        // Convert the config items into strings
        $allowed_headers = implode(', ', $this->config->item('allowed_cors_headers'));
        $allowed_methods = implode(', ', $this->config->item('allowed_cors_methods'));

        // If we want to allow any domain to access the API
        if ($this->config->item('allow_any_cors_domain') === TRUE) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Headers: ' . $allowed_headers);
            header('Access-Control-Allow-Methods: ' . $allowed_methods);
        } else {
            // We're going to allow only certain domains access
            // Store the HTTP Origin header
            $origin = $this->input->server('HTTP_ORIGIN');
            if ($origin === NULL) {
                $origin = '';
            }
            // If the origin domain is in the allowed_cors_origins list, then add the Access Control headers
            if (in_array($origin, $this->config->item('allowed_cors_origins'))) {
                header('Access-Control-Allow-Origin: ' . $origin);
                header('Access-Control-Allow-Headers: ' . $allowed_headers);
                header('Access-Control-Allow-Methods: ' . $allowed_methods);
            }
        }

        // If the request HTTP method is 'OPTIONS', kill the response and send it to the client
        if ($this->input->method() === 'options') {
            exit;
        }
    }
    
    /**
     * Check rate limits for API key
     * 
     * @param string $api_key
     * @return bool
     */
    protected function check_rate_limits($api_key)
    {
        // Check burst limit first
        if (!$this->Api_model->check_burst_limit($api_key)) {
            return false;
        }
        
        // Check regular quota
        $endpoint = $this->uri->uri_string();
        return $this->Api_model->check_quota($api_key, $endpoint);
    }
    
    /**
     * Drop rate-limit log columns if the api_usage_logs table was created before
     * those columns existed (migration in modules/api/install.php). Prevents
     * "Unknown column 'rate_limit_checked'" on INSERT for older databases.
     *
     * @param array $log_data
     * @return array
     */
    protected function filter_api_usage_log_for_table_schema(array $log_data)
    {
        $table = $this->config->item('rest_limits_table');
        if ($table === '' || $table === null || !function_exists('db_prefix')) {
            return $log_data;
        }
        $full_table = (strpos($table, db_prefix()) === 0) ? $table : db_prefix() . $table;
        if (!$this->db->field_exists('rate_limit_checked', $full_table)) {
            foreach (['rate_limit_checked', 'rate_limit_type', 'rate_limit_limit', 'rate_limit_current', 'rate_limit_exceeded'] as $col) {
                unset($log_data[$col]);
            }
        }

        return $log_data;
    }

    /**
     * Log API usage
     * 
     * @param string $api_key
     * @param int $response_code
     * @param float $response_time
     */
    protected function log_api_usage($api_key, $response_code, $response_time = 0)
    {
        $endpoint = $this->uri->uri_string();
        $ip_address = $this->input->ip_address();
        
        // If no API key, use IP address as identifier for rate limiting
        $identifier = $api_key ?: $ip_address;
        
        // Get user_api_id if API key is provided
        $user_api_id = 0;
        if (!empty($api_key)) {
            $this->rest->db->select('id');
            $this->rest->db->from(db_prefix() . $this->config->item('rest_keys_table'));
            $this->rest->db->where($this->config->item('rest_key_column'), $api_key);
            $user_api = $this->rest->db->get()->row();
            if ($user_api) {
                $user_api_id = $user_api->id;
            }
        
            // Prepare log data
            $log_data = [
                'user_api_id' => $user_api_id,
                'api_key' => $identifier,
                'endpoint' => $endpoint,
                'response_code' => $response_code,
                'response_time' => round($response_time, 4),
                'timestamp' => time(),
                'ip_address' => $ip_address,
                'user_agent' => $this->input->user_agent(),
                'rate_limit_checked' => 1
            ];
            
            // Add rate limit data if available from _check_limit
            if (!empty($this->_rate_limit_log_data)) {
                $log_data['rate_limit_type'] = $this->_rate_limit_log_data['limit_type'] ?? 'unknown';
                $log_data['rate_limit_limit'] = $this->_rate_limit_log_data['limit'] ?? 0;
                $log_data['rate_limit_current'] = $this->_rate_limit_log_data['current_count'] ?? 0;
                $log_data['rate_limit_exceeded'] = ($this->_rate_limit_log_data['limit_type'] === 'burst' || 
                                                    $this->_rate_limit_log_data['limit_type'] === 'standard') ? 1 : 0;
            } else {
                // No rate limit was checked (might be disabled)
                $log_data['rate_limit_checked'] = 0;
                $log_data['rate_limit_type'] = null;
                $log_data['rate_limit_limit'] = null;
                $log_data['rate_limit_current'] = null;
                $log_data['rate_limit_exceeded'] = 0;
            }
            
            // Insert log entry
            $log_data = $this->filter_api_usage_log_for_table_schema($log_data);
            $this->rest->db->insert($this->config->item('rest_limits_table'), $log_data);
        }
        
        // Reset rate limit log data for next request
        $this->_rate_limit_log_data = null;
    }

	protected function parse_json_data() {        
		$object_keys = [
			"custom_fields",
			"items",
			"additionalProperties",
			"newitems",
			"expenses",
			"groups_in",
		];
		
		foreach ($_POST as $post_key => $post_arg) {
			if (in_array($post_key, $object_keys)) {
				// If it's a string, try to decode it as JSON
				if (is_string($post_arg) && !empty($post_arg)) {
					$decoded = json_decode($post_arg, true);
					// Only replace if decoding was successful
					if (json_last_error() === JSON_ERROR_NONE) {
						$_POST[$post_key] = $decoded;
					}
				}
				// If it's already an array, leave it as is
			}
			
			if (is_array($_POST[$post_key])) {
				foreach ($_POST[$post_key] as $post_sub_key => $post_sub_arg) {
					if (in_array($post_sub_key, $object_keys)) {
						// Only decode strings
						if (is_string($post_sub_arg) && !empty($post_sub_arg)) {
							$decoded = json_decode($post_sub_arg, true);
							if (json_last_error() === JSON_ERROR_NONE) {
								$_POST[$post_key][$post_sub_key] = $decoded;
							}
						}
					}
				}
			}
		}
	}

    /**
     * Sanitize POST data by converting null values to empty strings
     * This prevents trim() deprecation warnings in form validation
     * 
     * @param array $data The POST data to sanitize
     * @return array Sanitized data with null values converted to empty strings
     */
    protected function sanitize_post_data($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        return array_map(function($value) {
            if (is_array($value)) {
                return $this->sanitize_post_data($value);
            }
            return $value === null ? '' : $value;
        }, $data);
    }

    /**
     * Prepare form validation data by sanitizing $_POST and setting it for validation
     * This prevents trim() deprecation warnings in form validation
     * 
     * @return void
     */
    protected function prepare_form_validation()
    {
        // Sanitize POST data to prevent trim() deprecation warnings
        $_POST = $this->sanitize_post_data($_POST);
        $this->form_validation->set_data($_POST);
    }

    /**
     * Apply pagination to data arrays
     * Returns data in standardized format with meta information
     * 
     * @param array $data The data array to paginate
     * @return array Paginated response with data and meta sections
     */
    protected function apply_pagination($data)
    {
        // Check if data is already paginated
        if (isset($data['data']) && isset($data['meta'])) {
            return $data;
        }

        // Ensure data is an array
        if (!is_array($data)) {
            return $data;
        }

        // Unified v3 pipeline: keeps the historical contract of this helper
        // (always-enveloped, default per_page 20, page clamped to last page)
        // while adding ?sort=, ?fields= and created_after/created_before
        // support to every controller that already calls it.
        list($rows, $meta) = $this->api_apply_list_features($data, [
            'force_pagination' => TRUE,
            'default_per_page' => 20,
        ]);

        return [
            'data' => $rows,
            'meta' => $meta,
        ];
    }

    /**
     * Register a transformer
     * 
     * @param string|Api_Transformer $transformer Transformer class name or instance
     * @param array $options Options (e.g., ['only' => ['data_get'], 'priority' => 10])
     * @return void
     */
    protected function addTransformer($transformer, $options = [])
    {
        if (!isset($this->transformers)) {
            $this->transformers = [];
        }
        
        $priority = isset($options['priority']) ? $options['priority'] : 10;
        
        $this->transformers[] = [
            'transformer' => $transformer,
            'options' => $options,
            'priority' => $priority
        ];
        
        // Sort by priority (lower number = higher priority)
        usort($this->transformers, function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
    }

    /**
     * Apply registered transformers to response data
     * 
     * @param mixed $data Response data
     * @param string $method Controller method name
     * @return mixed Transformed data
     */
    protected function applyTransformers($data, $method = '')
    {
        if (empty($this->transformers)) {
            return $data;
        }
        
        // Load base transformer class first
        if (!class_exists('Api_Transformer')) {
            $baseTransformerFile = __DIR__ . '/../libraries/Api_Transformer.php';
            if (file_exists($baseTransformerFile)) {
                require_once $baseTransformerFile;
            } else {
                return $data;
            }
        }
        
        $context = [
            'controller' => get_class($this),
            'method' => $method,
            'request' => $this->request,
            'args' => $this->_args,
            'api_key' => isset($this->rest->key) ? $this->rest->key : null,
            'user_id' => isset($this->rest->user_id) ? $this->rest->user_id : null
        ];
        
        foreach ($this->transformers as $transformerConfig) {
            $transformer = $transformerConfig['transformer'];
            $options = $transformerConfig['options'];
            
            // Check if transformer should be applied
            if (isset($options['only']) && !in_array($method, $options['only'])) {
                continue;
            }
            
            if (isset($options['except']) && in_array($method, $options['except'])) {
                continue;
            }
            
            // Instantiate transformer if needed
            if (is_string($transformer)) {
                // Load transformer class if it exists
                $transformerFile = __DIR__ . '/../libraries/transformers/' . $transformer . '.php';
                
                if (!file_exists($transformerFile)) {
                    // Skip this transformer if file doesn't exist
                    continue;
                }
                
                require_once $transformerFile;
                
                // Check if class exists after loading
                if (!class_exists($transformer)) {
                    // Skip if class doesn't exist
                    continue;
                }
                
                try {
                    $transformer = new $transformer();
                } catch (Exception $e) {
                    // Skip if instantiation fails
                    continue;
                }
            }
            
            // Check if transformer extends Api_Transformer
            if (!($transformer instanceof Api_Transformer)) {
                continue;
            }
            
            // Check if transformer wants to transform
            if (!$transformer->shouldTransform($context)) {
                continue;
            }
            
            // Apply transformation
            try {
                $data = $transformer->transform($data, $context);
            } catch (Exception $e) {
                // If transformation fails, continue with next transformer
                continue;
            }
        }
        
        return $data;
    }
}
