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