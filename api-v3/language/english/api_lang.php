<?php

# Version 1.0.0
$lang['api']                                                = 'API';
$lang['api_management']                                     = 'Users Management';
$lang['api_permissions']                                    = 'API Permissions';
$lang['api_guide']                                          = 'Documentation';
$lang['new_user_api']		                                = 'New User';
$lang['edit_user_api']		                                = 'Edit User';
$lang['user_api']		                                    = 'User';
$lang['api_staff_id_optional']                              = 'Staff ID (optional - links this token to a staff member for staff-level data visibility)';
$lang['api_v3_settings']                                    = 'v3 Platform Settings (MCP, Webhooks 2.0, Security)';
$lang['api_mcp_enabled_label']                              = 'Enable MCP Server (AI agents)';
$lang['api_mcp_enabled_help']                               = 'Exposes POST /api/mcp with 148 permission-filtered CRM tools for Claude, ChatGPT, Cursor, n8n and any MCP-compatible client. Disabled by default.';
$lang['api_staff_visibility_label']                         = 'Staff-level data visibility';
$lang['api_staff_visibility_help']                          = 'Tokens linked to a non-admin staff member (Staff ID field) only see data that staff member can access. Unlinked/admin tokens keep full visibility.';
$lang['api_auth_throttle_limit_label']                      = 'Failed-auth throttle limit (per IP / 15 min)';
$lang['api_auth_throttle_limit_help']                       = 'IPs exceeding this many failed authentications within 15 minutes receive HTTP 429. Set 0 to disable.';
$lang['api_webhook_delivery_mode_label']                    = 'Webhook delivery mode';
$lang['api_webhook_mode_immediate']                         = 'Immediate (send when the event fires)';
$lang['api_webhook_mode_queue']                             = 'Queued (async via cron, with retries & backoff)';
$lang['api_webhook_delivery_mode_help']                     = 'Queued mode delivers webhooks in the background via Perfex cron (with an admin-load fallback) and retries failures with exponential backoff.';
$lang['api_webhook_ssrf_strict_label']                      = 'Strict SSRF protection for webhook URLs';
$lang['api_webhook_ssrf_strict_help']                       = 'Cloud-metadata and loopback targets are always blocked; strict mode additionally blocks private LAN ranges (10.x, 172.16.x, 192.168.x).';
$lang['api_webhook_ssl_verify_label']                       = 'Verify TLS certificates on webhook delivery';
$lang['api_webhook_ssl_verify_help']                        = 'Recommended ON. Disable only if your webhook endpoint uses a self-signed certificate.';
$lang['name_api']		                                    = 'Name';
$lang['password_api']		                                = 'Password';
$lang['repeat_passwork_api']	                            = 'Repeat Passwork';
$lang['token_api']		                                    = 'Token';
$lang['expiration_date']		                            = 'Expiration Date';

$lang['permission_get']                                     = 'Get';
$lang['permission_get_value']                               = 'Get Value';
$lang['permission_list']                                    = 'Get List';
$lang['permission_search']                                  = 'Search';
$lang['permission_create']                                  = 'Create';
$lang['permission_delete']                                  = 'Delete';
$lang['permission_update']                                  = 'Update';
$lang['permission_send']                                    = 'Send email';
$lang['api_thirdparty_allowed_tables_label']                = 'Third-Party Custom Tables - allowed tables';
$lang['api_thirdparty_allowed_tables_help']                 = 'Comma-separated table names (with prefix, e.g. tblmymodule_data) that the Third-Party Custom Tables endpoints may read or write. Empty = disabled (no tables exposed). Core tables such as tbloptions, tblstaff and tbluser_api are always blocked, even if listed here.';
$lang['permission_view']                                    = 'View';
$lang['permission_global']                                  = 'Global';

$lang['payment_methods']                                    = 'Payment Methods';

// Sandbox language strings
$lang['api_sandbox']                                        = 'Sandbox';
$lang['api_endpoints']                                      = 'API Endpoints';
$lang['request_builder']                                    = 'Request Builder';
$lang['response']                                           = 'Response';
$lang['sample_requests']                                    = 'Sample Requests';
$lang['method']                                             = 'Method';
$lang['endpoint']                                           = 'Endpoint';
$lang['headers']                                            = 'Headers';

// API Settings
$lang['api_settings']                                       = 'Settings';
$lang['json_response_normalization']                        = 'JSON Response Normalization';
$lang['enable_json_normalization']                          = 'Enable JSON Response Normalization';
$lang['enable_json_normalization_help']                     = 'Enable standardized JSON response format according to industry best practices. This includes consistent structure, field filtering, privacy protection, and pagination metadata. When disabled, API returns the original format for backwards compatibility with existing integrations.';
$lang['save_settings']                                      = 'Save Settings';
$lang['settings_updated_successfully']                      = 'Settings updated successfully';
$lang['request_data']                                       = 'Request Data';
$lang['execute_request']                                    = 'Execute Request';
$lang['clear']                                              = 'Clear';
$lang['load_sample']                                        = 'Load Sample';
$lang['load_this_sample']                                   = 'Load This Sample';
$lang['no_response_yet']                                    = 'No response yet';
$lang['executing_request']                                  = 'Executing request...';
$lang['invalid_json_format']                                = 'Invalid JSON format';

// Quota language strings
$lang['api_quotas']                                         = 'API Quotas';
$lang['add_quota']                                          = 'Add Quota';
$lang['edit_quota']                                         = 'Edit Quota';
$lang['api_key']                                            = 'API Key';
$lang['request_limit']                                      = 'Request Limit';
$lang['time_window']                                        = 'Time Window';
$lang['burst_limit']                                        = 'Burst Limit';
$lang['status']                                             = 'Status';
$lang['options']                                            = 'Options';
$lang['active']                                             = 'Active';
$lang['inactive']                                           = 'Inactive';
$lang['save']                                               = 'Save';
$lang['cancel']                                             = 'Cancel';

// Reporting language strings
$lang['api_reporting']                                      = 'Reports';
$lang['filters']                                            = 'Filters';
$lang['all_api_keys']                                       = 'All API Keys';
$lang['start_date']                                         = 'Start Date';
$lang['end_date']                                           = 'End Date';
$lang['apply_filters']                                      = 'Apply Filters';
$lang['export']                                             = 'Export';
$lang['total_requests']                                     = 'Total Requests';
$lang['success_rate']                                       = 'Success Rate';
$lang['avg_response_time']                                  = 'Avg Response Time';
$lang['error_requests']                                     = 'Error Requests';
$lang['request_timeline']                                   = 'Request Timeline';
$lang['response_codes']                                     = 'Response Codes';
$lang['endpoint_statistics']                                = 'Endpoint Statistics';
$lang['request_count']                                      = 'Request Count';
$lang['success_count']                                      = 'Success Count';
$lang['error_count']                                        = 'Error Count';
$lang['api_user']                                              = 'API User';
$lang['select_api_user']                                       = 'Select API User';
$lang['edit_user_quotas']                                      = 'Edit User Quotas';
$lang['user_statistics']                                       = 'Statistics';
$lang['quota_updated_successfully']                            = 'Quota updated successfully';
$lang['quota_update_failed']                                   = 'Quota update failed';
$lang['request_limit']                                         = 'Request Limit';
$lang['time_window']                                           = 'Time Window';
$lang['quota_active']                                          = 'Quota Active';
$lang['active']                                                = 'Active';
$lang['inactive']                                              = 'Inactive';
$lang['edit_user']                                             = 'Edit User';
$lang['delete_user']                                           = 'Delete User';
$lang['update_quota']                                          = 'Update Quota';
$lang['usage_over_time']                                       = 'Usage Over Time';
$lang['total_requests']                                        = 'Total Requests';
$lang['success_requests']                                      = 'Success Requests';
$lang['error_requests']                                        = 'Error Requests';
$lang['avg_response_time']                                     = 'Avg Response Time';
$lang['endpoint']                                              = 'Endpoint';
$lang['quota_settings']                                        = 'Quota Settings';
$lang['select_api_user_to_view_statistics']                    = 'Select an API user to view statistics';
$lang['quota_summary']                                         = 'Quota Summary';
$lang['api_key_summary']                                    = 'API Key Summary';
$lang['time']                                               = 'Time';
$lang['requests']                                           = 'Requests';

// Documentation language strings
$lang['api_documentation']                                  = 'API Documentation';
$lang['api_swagger']                                        = 'API Swagger';

// Quota statistics language strings
$lang['quota_statistics']                                   = 'Quota Statistics';
$lang['select_api_key']                                     = 'Select API Key';
$lang['time_period']                                        = 'Time Period';
$lang['last_7_days']                                        = 'Last 7 Days';
$lang['last_30_days']                                       = 'Last 30 Days';
$lang['last_90_days']                                       = 'Last 90 Days';
$lang['load_statistics']                                    = 'Load Statistics';
$lang['top_endpoints']                                      = 'Top Endpoints';
$lang['no_data_available']                                  = 'No Data Available';
$lang['select_api_key_to_view_statistics']                  = 'Select an API key to view statistics';
$lang['error_loading_statistics']                           = 'Error loading statistics';

// Permission feature labels (v3.0.3)
$lang['api_notes']                                          = 'Notes';
$lang['api_knowledge_base']                                 = 'Knowledge Base';

// Webhook Management
$lang['api_webhooks']                                       = 'Event Webhooks';
$lang['new_webhook']                                         = 'New Webhook';
$lang['edit_webhook']                                        = 'Edit Webhook';
$lang['webhook_name']                                        = 'Webhook Name';
$lang['webhook_url']                                         = 'Webhook URL';
$lang['webhook_secret']                                      = 'Webhook Secret';
$lang['webhook_secret_help']                                 = 'Optional secret key for generating webhook signatures';
$lang['events_to_trigger']                                   = 'Events to Trigger';
$lang['all_events']                                          = 'All Events';
$lang['webhook_timeout']                                     = 'Timeout (seconds)';
$lang['webhook_retry_count']                                 = 'Retry Count';
$lang['custom_headers']                                      = 'Custom Headers';
$lang['custom_headers_help']                                 = 'JSON object with custom headers to send with webhook';
$lang['webhook_created_successfully']                        = 'Webhook created successfully';
$lang['webhook_updated_successfully']                        = 'Webhook updated successfully';
$lang['webhook_deleted_successfully']                        = 'Webhook deleted successfully';
$lang['test_webhook']                                        = 'Test Webhook';
$lang['webhook_test_success']                                = 'Webhook test sent successfully';
$lang['webhook_test_failed']                                 = 'Webhook test failed';
$lang['webhook_test_error']                                  = 'Error testing webhook';
$lang['download_postman_collection']                        = 'Download Postman Collection';
$lang['without_api_key']                                    = 'Without API Key';
$lang['view_logs']                                           = 'View Logs';
$lang['webhook_logs']                                        = 'Webhook Logs';
$lang['webhook_log_details']                                 = 'Webhook Log Details';
$lang['no_webhooks_configured']                              = 'No webhooks configured';
$lang['success_count']                                        = 'Success Count';
$lang['failure_count']                                        = 'Failure Count';
$lang['last_triggered']                                      = 'Last Triggered';
$lang['view_details']                                        = 'View Details';
$lang['payload']                                             = 'Payload';
$lang['response']                                            = 'Response';
$lang['error_message']                                       = 'Error Message';
$lang['optional_secret_for_signature']                       = 'Optional secret for signature';
$lang['timeout_in_seconds']                                  = 'Timeout in seconds';
$lang['number_of_retries_on_failure']                        = 'Number of retries on failure';
$lang['no_logs_found']                                       = 'No logs found';
$lang['back']                                                = 'Back';
$lang['event']                                               = 'Event';
$lang['response_code']                                       = 'Response Code';
$lang['attempt_number']                                      = 'Attempt Number';
$lang['triggered_at']                                        = 'Triggered At';
$lang['never']                                               = 'Never';
$lang['url']                                                 = 'URL';
$lang['events']                                               = 'Events';

// Automation Connectors
$lang['automation_connectors']                                = 'Automation Connectors';
$lang['zapier_automation_connectors']                         = 'Zapier / Automation Connectors';
$lang['permission_test_triggers']                             = 'Test Triggers';
$lang['permission_poll_data']                                 = 'Poll for Data';
$lang['permission_list_resources']                            = 'List Resources';

// Middleware Settings
$lang['middleware_settings']                                   = 'Middleware Settings';
$lang['middleware_settings_help']                             = 'Configure middleware to enhance API security, performance, and monitoring without coding.';
$lang['enable_request_logging']                                = 'Enable Request Logging';
$lang['enable_request_logging_help']                          = 'Log all API requests and responses for debugging and monitoring.';
$lang['enable_response_caching']                              = 'Enable Response Caching';
$lang['enable_response_caching_help']                         = 'Cache GET request responses to improve performance.';
$lang['cache_ttl']                                            = 'Cache TTL (seconds)';
$lang['cache_ttl_help']                                       = 'Time-to-live for cached responses (default: 300 seconds / 5 minutes).';
$lang['enable_ip_whitelist']                                  = 'Enable IP Whitelist';
$lang['enable_ip_whitelist_help']                             = 'Allow only specified IP addresses to access the API. Leave empty to allow all IPs.';
$lang['ip_whitelist']                                         = 'Allowed IP Addresses';
$lang['ip_whitelist_help']                                     = 'One IP address or CIDR range per line (e.g., 192.168.1.1 or 192.168.1.0/24).';
$lang['enable_ip_blacklist']                                  = 'Enable IP Blacklist';
$lang['enable_ip_blacklist_help']                             = 'Block specified IP addresses from accessing the API.';
$lang['ip_blacklist']                                         = 'Blocked IP Addresses';
$lang['ip_blacklist_help']                                     = 'One IP address or CIDR range per line (e.g., 192.168.1.100 or 10.0.0.0/8).';
$lang['enable_security_headers']                              = 'Enable Security Headers';
$lang['enable_security_headers_help']                          = 'Add security headers (X-Frame-Options, X-Content-Type-Options, etc.) to API responses.';
$lang['enable_request_size_limit']                            = 'Enable Request Size Limit';
$lang['enable_request_size_limit_help']                        = 'Limit the maximum size of request body to prevent abuse.';
$lang['max_request_size_mb']                                  = 'Maximum Request Size (MB)';
$lang['max_request_size_mb_help']                              = 'Maximum allowed request body size in megabytes (default: 10MB).';

// Third-Party Custom Tables
$lang['thirdparty_custom_tables']                              = 'Third-Party Custom Tables';