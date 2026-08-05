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
| Token Expire Time

| https://www.tools4noobs.com/online_tools/hh_mm_ss_to_seconds/
|--------------------------------------------------------------------------
| IMPORTANT: This is the DEFAULT expiration time for tokens
| Users can override this when creating tokens via the admin panel
| 
| SECURITY NOTE: Longer expiration times increase risk if tokens are compromised.
| Recommended: 3600 (1 hour) to 86400 (24 hours) for production
| Current Default: 315569260 (~10 years) - maintains backwards compatibility
|
| Users are responsible for setting appropriate expiration times based on their security needs.
|--------------------------------------------------------------------------
| ( 1 Day ) : 60 * 60 * 24 = 86400
| ( 1 Hour ) : 60 * 60     = 3600
| ( 1 Minute ) : 60        = 60
*/
$config['token_expire_time'] = 315569260; // ~10 years (default for backwards compatibility)