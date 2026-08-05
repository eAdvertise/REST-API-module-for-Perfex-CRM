<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API Key Header Name
 */
$config['api_key_header_name'] = 'Authtoken';


/**
 * API Key GET Request Parameter Name
 */
$config['api_key_get_name'] = 'key';


/**
 * API Key POST Request Parameter Name
 */
$config['api_key_post_name'] = 'key';


/**
 * Set API Timezone 
 */
$config['api_timezone'] = 'Europe/London';


/**
 * API Limit database table name
 */
$config['api_limit_table_name'] = 'api_usage_logs';

/**
 * API keys database table name 
 */
$config['api_keys_table_name'] = 'user_api';

/**
 * Default API Rate Limit
 * [limit_number, limit_type, time_period]
 * Example: [100, 'ip', 60] = 100 requests per IP per 60 minutes
 * Set to false to disable default rate limiting
 */
$config['api_default_limit'] = [100, 'ip', 60];

/**
 * Enable JSON Response Normalization
 * Set to true to enable standardized JSON response format according to industry best practices
 * Set to false for backwards compatibility with original response format
 * Default: false (backwards compatible)
 */
$config['api_enable_transformers'] = false;