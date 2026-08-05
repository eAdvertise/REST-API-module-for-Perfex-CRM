<?php
defined('BASEPATH') or exit('No direct script access allowed');

$route['contactsplus/search_available']['get']                 = 'contactsplus/search_available';
$route['contactsplus/api/link_core_contact/(:num)']['post']    = 'contactsplus/api/link_core_contact/$1';
$route['contactsplus/api/unlink/(:num)']['post']               = 'contactsplus/api/unlink/$1';
$route['contactsplus/api/delete_contact/(:num)']['post']       = 'contactsplus/api/delete_contact/$1';

// Core contact actions
$route['contactsplus/api/unlink_core/(:num)']['post'] = 'contactsplus/api/unlink_core/$1';
$route['contactsplus/api/move_core/(:num)']['post']   = 'contactsplus/api/move_core/$1';

// Search helpers
$route['contactsplus/search_customers']['get']        = 'contactsplus/search_customers';

// PMC contact edit
$route['contactsplus/api/pmc_contact/(:num)']['get']  = 'contactsplus/api/get_pmc_contact/$1';
$route['contactsplus/api/pmc_contact/(:num)']['post'] = 'contactsplus/api/update_pmc_contact/$1';

// Enable Portal (νέο)
$route['contactsplus/api/contacts/(:num)/enable-portal']['post'] = 'contactsplus/api/enable_portal/$1';



// ΕΠΙΣΤΡΟΦΗ emails για αποστολές (invoice/payment/receipt κ.λπ.)
$route['contactsplus/api/emails_for_client']['get'] = 'contactsplus/api/emails_for_client';