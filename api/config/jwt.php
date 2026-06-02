<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------
| JWT Secure Key
|--------------------------------------------------------------------------
*/
$config['jwt_key'] = 'eyJ0eXAiOiJKV1QiLCJhbGciTWeLUzI1NiJ9IiRkYXRhIz';


/*
|-----------------------
| JWT Algorithm Type
|--------------------------------------------------------------------------
*/
$config['jwt_algorithm'] = 'HS256';


/*
|-----------------------
| Token Request Header Name
|--------------------------------------------------------------------------
*/
$config['token_header'] = 'authtoken';

/*
|-----------------------
| Backward-compatible Token Request Header Aliases
|--------------------------------------------------------------------------
| Some integrations/Postman collections confused the REST config key
| `rest_enable_keys` with the token header name. Keep accepting legacy aliases
| while `authtoken` remains the documented canonical header. Prefer the
| hyphenated `rest-enable-keys` alias if a web server drops underscore headers.
*/
$config['token_header_aliases'] = ['rest_enable_keys', 'rest-enable-keys', 'authorization', 'x-api-key', 'x-auth-token'];


/*
|-----------------------
| Token Expire Time

| https://www.tools4noobs.com/online_tools/hh_mm_ss_to_seconds/
|--------------------------------------------------------------------------
| ( 1 Day ) : 60 * 60 * 24 = 86400
| ( 1 Hour ) : 60 * 60     = 3600
| ( 1 Minute ) : 60        = 60
*/
$config['token_expire_time'] = 315569260;