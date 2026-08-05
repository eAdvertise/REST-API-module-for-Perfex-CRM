<?php

defined('BASEPATH') || exit('No direct script access allowed');
$lang['module_name'] = 'WEBHOOK';
$lang['request_url'] = 'REQUEST URL';
$lang['request_method'] = 'REQUEST METHOD';
$lang['new_web_hook'] = 'NEW WEBHOOK';
$lang['request_format'] = 'REQUEST FORMAT';
$lang['webhook_for'] = 'WEBHOOK FOR';
$lang['active'] = 'ACTIVE';
$lang['actions'] = 'ACTIONS';
$lang['json'] = 'JSON';
$lang['get'] = 'GET';
$lang['post'] = 'POST';
$lang['put'] = 'PUT';
$lang['patch'] = 'PATCH';
$lang['delete'] = 'DELETE';
$lang['form'] = 'FORM';
$lang['request_headers'] = 'REQUEST HEADERS';
$lang['request_body'] = 'REQUEST BODY';
$lang['name'] = 'Name';
$lang['value'] = 'VALUE';
$lang['all_field'] = 'ALL FIELDS';
$lang['select_field'] = 'SELECT FIELDS';
$lang['field_value'] = 'Add Custom Field Values';
$lang['key'] = 'KEY';
$lang['webhook_condition'] = 'WEBHOOK CONDITION';
$lang['enable_condition'] = 'Enable Condition';
$lang['select_a_name'] = 'SELECT A NAME';
$lang['select_a_field'] = 'SELECT A FIELD';

$lang['selectrequestbody'] = 'Select Request Body';
$lang['selectwebhookcondition'] = 'Select Webhook Condition';
$lang['webhook_feed_name'] = 'WEBHOOK ACTION NAME';
$lang['webhook_action'] = 'WEBHOOK ACTION';
$lang['webhooks'] = 'Webhooks';
$lang['webhook'] = 'Webhook';
$lang['debug_mode'] = 'RUN IN DEBUG MODE';
$lang['debug_mode_tootlip'] = 'Turn debug mode on or off';
$lang['webhook_log'] = 'Log history';
$lang['recorded_on'] = 'Recorded On';

$lang['tootltip_request_action_name'] = 'Enter a feed name to uniquely identify this setup.';
$lang['tootltip_request_url'] = 'Enter the URL to be used in the webhook request.';
$lang['tootltip_request_webhook_for'] = 'Select the format for the webhook request.';
$lang['tootltip_request_webhook_actions'] = 'Setup the HTTP headers to be sent with the webhook request.';
$lang['tootltip_request_url'] = 'Enter the URL to be used in the webhook request.';
$lang['tootltip_request_method'] = 'Select the HTTP method used for the webhook request.';
$lang['tootltip_request_format'] = 'Select the format for the webhook request.';
$lang['tootltip_request_headers'] = 'Setup the HTTP headers to be sent with the webhook request.';
$lang['tootltip_request_body'] = 'Select fields should be sent with the webhook request.';
$lang['response_code'] = 'Response Code';
$lang['request_details'] = 'Request Details';
$lang['headers'] = 'Headers';
$lang['raw_content'] = 'Raw Content';
$lang['press_@_key'] = 'press @ key to Populate Fields';
$lang['Response'] = 'Response';
$lang['format_type'] = 'Format Type';

$lang['webhook_copy_success'] = 'Webhook cloned successfully';
$lang['webhook_copy_fail'] = 'Webhook cloning failed';

$lang['webhook_email_templates'] = 'Webhooks Email Templates';
$lang['calendar_event'] = 'Calendar Event';

$lang['webhook_after_number'] = 'Number';
$lang['webhook_after_type'] = 'Select';

$lang['webhooks_cron'] = 'Webhooks Cron';
$lang['webhooks_cron_job'] = 'Webhooks Cron Job';
$lang['trigger_after'] = 'Trigger After';
$lang['webhook_after_minute'] = 'Minute(s)';
$lang['webhook_after_hour'] = 'Hour(s)';
$lang['webhook_after_day'] = 'Day(s)';
$lang['webhooks_cron_job_queue'] = 'Webhooks Cron Job Queue';
$lang['webhooks_cron_job_settings'] = 'Settings';
$lang['webhooks_cron_job_queue_enabled'] = 'Enable Webhooks Cron Job Queue';

$lang['type'] = 'Type';
$lang['action'] = 'Action';
$lang['status'] = 'Status';

$lang['webhook_trigger_delay_note'] = 'Leave blank in order NOT to use a trigger delay';

$lang['scheduled_at'] = 'Scheduled At';
$lang['executed_at'] = 'Executed At';

$lang['total'] = 'Total';
$lang['pending'] = 'Pending';
$lang['success'] = 'Success';
$lang['failed'] = 'Failed';

// Webhook Triggers
$lang['triggers_when_new_estimate_created'] = 'Triggers when a new estimate is created';
$lang['triggers_when_new_calendar_event_created'] = 'Triggers when a new calendar event is created';
$lang['triggers_when_new_ticket_created'] = 'Triggers when a new ticket is created';
$lang['triggers_when_new_payment_created'] = 'Triggers when a new payment is created';
$lang['triggers_when_new_staff_created'] = 'Triggers when a new staff member is added';
$lang['triggers_when_new_contract_created'] = 'Triggers when a new contract is added';
$lang['triggers_when_new_task_created'] = 'Triggers when new task created';
$lang['triggers_when_new_project_created'] = 'Triggers when new project created';
$lang['triggers_when_new_proposal_created'] = 'Triggers when new proposal created';
$lang['triggers_when_new_lead_created'] = 'Triggers when new lead created';
$lang['triggers_when_new_contact_created'] = 'Triggers when new contact created ';
$lang['triggers_when_new_invoice_created'] = 'Triggers when new invoice created';
$lang['triggers_when_new_expenses_created'] = 'Triggers when new expense created';
$lang['triggers_when_new_credit_notes_created'] = 'Triggers when new credit notes created';
$lang['triggers_when_new_item_created'] = 'Triggers when new item created';

// Webhook Actions
$lang['webhook_permission_update'] = 'Update';
$lang['webhook_permission_status_change'] = 'Status change';
$lang['webhook_permission_accept'] = 'Accept';
$lang['webhook_permission_decline'] = 'Decline';
$lang['webhook_permission_sent'] = 'Sent';
$lang['webhook_permission_refund_created'] = 'Refund Created';
$lang['webhook_permission_credit_applied'] = 'Credit Applied';
$lang['webhook_permission_recurring_create'] = 'Recurring Create';
$lang['webhook_permission_task_timer_started'] = 'Timer Started';
$lang['webhook_permission_task_timer_deleted'] = 'Timer Deleted';
$lang['webhook_permission_task_comment_added'] = 'Comment Added';
$lang['webhook_permission_task_checklist_item_created'] = 'Checklist Item Created';
$lang['webhook_permission_task_checklist_item_finished'] = 'Checklist Item Finished';
$lang['webhook_permission_converted_to_invoice'] = 'Converted to invoice';
$lang['webhook_permission_proposal_converted_to_invoice'] = 'Converted to Invoice';
$lang['webhook_permission_proposal_converted_to_estimate'] = 'Converted to Estimate';
$lang['webhook_permission_lead_converted_to_customer'] = 'Converted to Customer';
$lang['webhook_permission_lead_marked_as_lost'] = 'Marked as Lost';
$lang['webhook_permission_lead_marked_as_junk'] = 'Marked as Junk';
$lang['webhook_permission_invoice_copied'] = 'Copied';
$lang['webhook_permission_invoice_sent'] = 'Sent';
$lang['webhook_permission_invoice_marked_as_cancelled'] = 'Marked as Cancelled';
$lang['webhook_permission_invoice_status_changed'] = 'Status Changed';

// Test Webhook
$lang['test_webhook'] = 'Test Webhook';
$lang['webhook_test_success'] = 'Test webhook sent successfully';
$lang['webhook_test_failed'] = 'Test webhook failed';
$lang['webhook_test_exception'] = 'Could not send test webhook';
$lang['webhook_test_invalid_url'] = 'Please enter a valid request URL before testing';
$lang['webhook_test_json_not_supported'] = 'GET/DELETE methods do not support JSON format';

// Logs filters
$lang['success_2xx'] = 'Success (2XX)';
$lang['redirection_3xx'] = 'Redirection (3XX)';
$lang['client_error_4xx'] = 'Client error (4XX)';
$lang['server_error_5xx'] = 'Server error (5XX)';
$lang['server_error_5xx'] = 'Server error (5XX)';
$lang['yesterday'] = 'Yesterday';
$lang['all_time'] = 'All Time';
$lang['clear_activity_log'] = 'Clear All Logs';