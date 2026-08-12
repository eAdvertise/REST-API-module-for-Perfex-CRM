<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Warehouse integration. Keep these routes before the generic API routes so
// the resource name and optional identifier are passed to the dispatcher.
$route['api/warehouse']                       = 'warehouse/data';
$route['api/warehouse/(:any)']                = 'warehouse/data/$1';
$route['api/warehouse/(:any)/(:num)']         = 'warehouse/data/$1/$2';

$route['api/delete/(:any)/(:num)'] = '$1/data/$2';
$route['api/(:any)/search/(:any)'] = '$1/data_search/$2';
$route['api/(:any)/search']        = '$1/data_search';
$route['api/login/auth']           = 'login/login_api';
$route['api/login/view']           = 'login/view';
$route['api/login/key']            = 'login/api_key';
$route['api/(:any)/(:any)/(:num)'] = '$1/data/$2/$3';
$route['api/(:any)/(:num)/(:num)'] = '$1/data/$2/$3';
$route['api/custom_fields/(:any)/(:num)'] = 'custom_fields/data/$1/$2';
$route['api/custom_fields/(:any)'] = 'custom_fields/data/$1';
$route['api/common/(:any)/(:num)'] = 'common/data/$1/$2';
$route['api/common/(:any)'] = 'common/data/$1';
$route['api/(:any)/(:num)']        = '$1/data/$2';
$route['api/(:any)']               = '$1/data';

// Guest invoices endpoint (create/find guest by email + create invoice)
// Must be before generic routes
$route['api/guest_invoices']            = 'guest_invoices/data';
$route['api/guest_invoices/(:num)']     = 'guest_invoices/data/$1';
$route['api/guest_invoices/checkout'] = 'guest_invoices/checkout';
$route['api/guestinvoices/checkout'] = 'guest_invoices/checkout';
