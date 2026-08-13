<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Admin API routes (must come FIRST before any other routes)
$route['admin/api/generate_manifest/(:any)'] = 'api/generate_connector_manifest/$1';
$route['admin/api/automation_connectors'] = 'api/automation_connectors';
$route['admin/api/connectors'] = 'api/automation_connectors'; // Route to renamed method
$route['admin/api/webhooks'] = 'api/webhooks';
$route['admin/api/webhook/(:num)'] = 'api/webhook/$1';
$route['admin/api/webhook'] = 'api/webhook';
$route['admin/api/delete_webhook/(:num)'] = 'api/delete_webhook/$1';
$route['admin/api/test_webhook/(:num)'] = 'api/test_webhook/$1';
$route['admin/api/webhook_logs/(:num)'] = 'api/webhook_logs/$1';
$route['admin/api/check_update'] = 'api/check_update';
$route['admin/api/run_update'] = 'api/run_update';

// CRITICAL: Automation endpoints MUST come IMMEDIATELY after admin routes
// Using /api/zapier/ pattern to avoid ANY conflict with generic routes
// These routes MUST be defined BEFORE any other API routes to ensure they match first
// CodeIgniter matches routes sequentially, so these specific routes must come before generic routes
// Route to Zapier controller (which extends REST_Controller directly, like Customers)
$route['api/zapier/poll/(:any)'] = 'zapier/poll/$1';
$route['api/zapier/test/(:any)'] = 'zapier/test/$1';
$route['api/zapier/resources'] = 'zapier/resources';
$route['api/zapier'] = 'zapier/resources';

// Keep original /api/connectors/ routes for backwards compatibility (may not work due to eAD-CRM interception)
$route['api/connectors/poll/(:any)'] = 'connector_polling/poll/$1';
$route['api/connectors/test/(:any)'] = 'connector_polling/test/$1';
$route['api/connectors/resources'] = 'connector_polling/resources';
// Block api/connectors from matching generic route - MUST be after specific routes
$route['api/connectors'] = 'api/connectors_blocked';

// Specific API routes (must come before generic routes)
$route['api/playground']               = 'playground/index';
$route['api/playground/swagger']      = 'playground/swagger';
$route['api/sandbox']                  = 'playground/sandbox';
$route['api/sandbox/execute_request'] = 'playground/execute_request';
$route['api/sandbox/get_samples'] = 'playground/get_samples';
$route['api/sandbox/get_endpoints'] = 'playground/get_endpoints';
$route['api/sandbox/get_environment_config'] = 'playground/get_environment_config';
$route['api/sandbox/documentation'] = 'playground/documentation';

$route['api/users/stats/(:num)']   = 'api/user_stats/$1';
$route['api/users/stats']          = 'api/user_stats';

$route['api/reporting']            = 'reporting/index';
$route['api/reporting/get_chart_data'] = 'reporting/get_chart_data';
$route['api/reporting/export']     = 'reporting/export';

// MCP Server v3 (must come before generic routes)
$route['api/mcp'] = 'mcp/index';

// Batch operations v3 (must come before generic routes)
$route['api/batch'] = 'batch/index';

// OpenAPI 3.1 specification v3 (public, like the Postman download)
$route['api/openapi.json'] = 'openapi/json';
$route['api/openapi']      = 'openapi/json';

// Invoices: send by email (must come before generic routes)
$route['api/invoices/(:num)/send']     = 'invoices/send/$1';

// Webhooks REST management v3 (must come before generic routes)
$route['api/webhooks/events']          = 'webhooks/events';
$route['api/webhooks/(:num)/toggle']   = 'webhooks/toggle/$1';
$route['api/webhooks/(:num)/logs']     = 'webhooks/logs/$1';
$route['api/webhooks/(:num)']          = 'webhooks/data/$1';
$route['api/webhooks']                 = 'webhooks/data';

// Knowledge Base + Notes v3 (must come before generic routes)
$route['api/knowledge_base/groups/(:num)'] = 'knowledge_base/groups/$1';
$route['api/knowledge_base/groups']        = 'knowledge_base/groups';
$route['api/knowledge_base/(:num)']        = 'knowledge_base/data/$1';
$route['api/knowledge_base']               = 'knowledge_base/data';
$route['api/notes/(:any)/(:num)']          = 'notes/data/$1/$2';
$route['api/notes/(:num)']                 = 'notes/data/$1';
$route['api/notes']                        = 'notes/data';

// Task comments v3.0.3 (must come before generic routes)
$route['api/tasks/(:num)/comments/(:num)'] = 'tasks/comments_id/$1/$2';
$route['api/tasks/(:num)/comments']        = 'tasks/comments/$1';


// Guest invoices endpoint (create/find guest by email + create invoice)
// Must be before generic routes
$route['api/guest_invoices']            = 'guest_invoices/data';
$route['api/guest_invoices/(:num)']     = 'guest_invoices/data/$1';
$route['api/guest_invoices/checkout']   = 'guest_invoices/checkout';
$route['api/guestinvoices/checkout']    = 'guest_invoices/checkout';

// Warehouse module v3 (must come before generic routes)
$route['api/warehouse']                       = 'warehouse/data';
$route['api/warehouse/(:any)']                = 'warehouse/data/$1';
$route['api/warehouse/(:any)/(:num)']         = 'warehouse/data/$1/$2';

// PaymentsOnAccount module v3 (must come before generic routes)
$route['api/paymentsonaccount']                                      = 'paymentsonaccount/catalog';
$route['api/paymentsonaccount/receipts']                             = 'paymentsonaccount/receipts';
$route['api/paymentsonaccount/receipts/(:num)']                      = 'paymentsonaccount/receipts/$1';
$route['api/paymentsonaccount/receipts/(:num)/applications']         = 'paymentsonaccount/applications/$1';
$route['api/paymentsonaccount/receipts/(:num)/applications/(:num)'] = 'paymentsonaccount/application/$1/$2';
$route['api/paymentsonaccount/receipts/(:num)/email']                = 'paymentsonaccount/email/$1';
$route['api/paymentsonaccount/receipts/(:num)/pdf']                  = 'paymentsonaccount/pdf/$1';
$route['api/paymentsonaccount/clients/(:num)/unpaid-invoices']       = 'paymentsonaccount/unpaid_invoices/$1';
$route['api/paymentsonaccount/clients/(:num)/payment-modes']         = 'paymentsonaccount/client_modes/$1';
$route['api/paymentsonaccount/clients/(:num)/statement']             = 'paymentsonaccount/statement/$1';
$route['api/paymentsonaccount/reports/receipts']                     = 'paymentsonaccount/reports';
$route['api/paymentsonaccount/reports/credits']                      = 'paymentsonaccount/credits';

// Delivery Notes module v3 (must come before generic routes)
$route['api/delivery_notes']                                      = 'delivery_notes/catalog';
$route['api/delivery_notes/statuses']                             = 'delivery_notes/statuses';
$route['api/delivery_notes/notes']                                = 'delivery_notes/notes';
$route['api/delivery_notes/notes/(:num)']                         = 'delivery_notes/notes/$1';
$route['api/delivery_notes/notes/(:num)/status']                  = 'delivery_notes/status/$1';
$route['api/delivery_notes/notes/(:num)/email']                   = 'delivery_notes/email/$1';
$route['api/delivery_notes/notes/(:num)/pdf']                     = 'delivery_notes/pdf/$1';
$route['api/delivery_notes/notes/(:num)/copy']                    = 'delivery_notes/copy/$1';
$route['api/delivery_notes/notes/(:num)/convert-to-invoice']      = 'delivery_notes/convert_to_invoice/$1';
$route['api/delivery_notes/from-invoice/(:num)']                  = 'delivery_notes/from_invoice/$1';
$route['api/delivery_notes/from-estimate/(:num)']                 = 'delivery_notes/from_estimate/$1';
$route['api/delivery_notes/from-purchase-order/(:num)']           = 'delivery_notes/from_purchase_order/$1';

// Sales Commission module v3 (must come before generic routes)
$route['api/commission']                                  = 'commission/catalog';
$route['api/commission/commissions']                      = 'commission/commissions';
$route['api/commission/commissions/(:num)']               = 'commission/commissions/$1';
$route['api/commission/policies']                         = 'commission/policies';
$route['api/commission/policies/(:num)']                  = 'commission/policies/$1';
$route['api/commission/applicable-staff']                 = 'commission/applicable_staff';
$route['api/commission/applicable-staff/(:num)']          = 'commission/applicable_staff/$1';
$route['api/commission/applicable-clients']               = 'commission/applicable_clients';
$route['api/commission/applicable-clients/(:num)']        = 'commission/applicable_clients/$1';
$route['api/commission/hierarchies']                      = 'commission/hierarchies';
$route['api/commission/hierarchies/(:num)']               = 'commission/hierarchies/$1';
$route['api/commission/salesadmin-groups']                = 'commission/salesadmin_groups';
$route['api/commission/salesadmin-groups/(:num)']         = 'commission/salesadmin_groups/$1';
$route['api/commission/receipts']                         = 'commission/receipts';
$route['api/commission/receipts/(:num)']                  = 'commission/receipts/$1';
$route['api/commission/receipts/(:num)/pdf']              = 'commission/pdf/$1';
$route['api/commission/receipts/(:num)/email']            = 'commission/email/$1';
$route['api/commission/chart']                            = 'commission/chart';
$route['api/commission/recalculate']                      = 'commission/recalculate';

// Generic API routes (must come after specific routes)
$route['api/tickets/reply/(:num)'] = 'tickets/data_reply/$1';
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
// Custom table routes (must come before generic routes)
$route['api/thirdparty/customtable/(:any)/(:num)'] = 'thirdparty/customtable_id/$1/$2';
$route['api/thirdparty/customtable/(:any)'] = 'thirdparty/customtable/$1';
$route['api/(:any)/(:num)']        = '$1/data/$2';
$route['api/(:any)']               = '$1/data';

// Postman collection download (public route, similar to playground)
$route['api/postman/download'] = 'postman/download';
$route['api/postman'] = 'postman/download';
