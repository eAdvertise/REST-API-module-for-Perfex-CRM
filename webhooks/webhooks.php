<?php

defined('BASEPATH') || exit('No direct script access allowed');
/*
    Module Name: Webhooks
    Description: Connect your eAD-CRM with every service out there, that supports webhook integration.
    Version: 2.0.0
    Requires at least: 3.0.*
    Author: eAdvertise.eu
    Author URI: https://www.eadvertise.eu
*/

use WpOrg\Requests\Requests as Webhooks_Requests;

define('WEB_CTL_PERFEX_VERSION', get_app_version() >= '3.2.0');

/*
 * Define module name
 * Module Name Must be in CAPITAL LETTERS
 */
define('WEBHOOKS_MODULE', 'webhooks');


//get codeigniter instance
$CI = &get_instance();

require __DIR__ . '/vendor/autoload.php';

/*
 *  Register activation module hook
 */
register_activation_hook(WEBHOOKS_MODULE, 'webhooks_module_activation_hook');
function webhooks_module_activation_hook()
{
    $CI = &get_instance();
    require_once __DIR__ . '/install.php';
}

/*
 * Register deactivation module hook
 */
register_deactivation_hook(WEBHOOKS_MODULE, 'webhooks_module_deactivation_hook');
function webhooks_module_deactivation_hook()
{
    // Removing backup files
    $backup_files_list = [
        APPPATH . 'models/Contracts_model.php',
    ];

    foreach ($backup_files_list as $actual_path) {
        if (file_exists($actual_path) && file_exists($actual_path . '.backup')) {
            @unlink($actual_path);
        }
        if (!file_exists($actual_path)) {
            rename($actual_path . '.backup', $actual_path);
        }
    }
}

/*
 *  Register language files, must be registered if the module is using languages
 */
register_language_files(WEBHOOKS_MODULE, [WEBHOOKS_MODULE]);

/*
 *  Load module helper file
 */
$CI->load->helper(WEBHOOKS_MODULE . '/webhooks');
$CI->load->helper(WEBHOOKS_MODULE . '/request_webhook');

require_once __DIR__ . '/includes/assets.php';
require_once __DIR__ . '/includes/staff_permissions.php';
require_once __DIR__ . '/includes/sidebar_menu_links.php';

get_instance()->config->load(WEBHOOKS_MODULE . '/config');

/*
 * Inject email template for webhooks module
 */
hooks()->add_action('after_email_templates', 'add_email_template_webhook');
function add_email_template_webhook()
{
    $data['hasPermissionEdit'] = has_permission('email_templates', '', 'edit');
    $data['webhooks']          = get_instance()->emails_model->get([
        'type'     => 'webhooks',
        'language' => 'english',
    ]);
    get_instance()->load->view('webhooks/mail_lists/email_templates_list', $data, false);
}

hooks()->add_filter('other_merge_fields_available_for', 'add_other_merge_fields_for_webhook');

function add_other_merge_fields_for_webhook($available_for)
{
    $available_for[] = 'webhooks';

    return $available_for;
}

create_email_template('Webhook failed', '', 'webhooks', 'Webhook failed', 'webhook-failed');

register_merge_fields(WEBHOOKS_MODULE . '/merge_fields/webhooks_merge_fields');

/* MyShopify webhooks: Start */
hooks()->add_filter('webhooks_triggers', 'wbhk_myshopify_register_trigger');
function wbhk_myshopify_register_trigger($triggers)
{
    if (!function_exists('module_dir_path') || !is_dir(module_dir_path('myshopify'))) {
        return $triggers;
    }

    $triggers[] = [
        'value'   => 'myshopify',
        'label'   => 'MyShopify',
        'subtext' => 'Triggers when a signed Shopify event is processed.',
    ];

    return $triggers;
}

hooks()->add_action('myshopify_webhook_processed', 'wbhk_myshopify_processed_hook');
function wbhk_myshopify_processed_hook($event)
{
    if (!is_array($event) || empty($event['topic'])) {
        return;
    }

    $topic = strtolower((string) $event['topic']);
    $action = 'status_change';
    if (substr($topic, -7) === '/create') {
        $action = 'add';
    } elseif (substr($topic, -7) === '/update' || substr($topic, -8) === '/updated') {
        $action = 'edit';
    } elseif (substr($topic, -7) === '/delete' || substr($topic, -8) === '/deleted') {
        $action = 'delete';
    }

    $payload = isset($event['payload']) && is_array($event['payload']) ? $event['payload'] : [];
    $data = (object) [
        'source'        => 'shopify',
        'topic'         => $topic,
        'shop_domain'   => (string) ($event['shop_domain'] ?? ''),
        'webhook_id'    => (string) ($event['webhook_id'] ?? ''),
        'api_version'   => (string) ($event['api_version'] ?? ''),
        'resource_type' => strstr($topic, '/', true) ?: $topic,
        'resource_id'   => (string) ($payload['id'] ?? $payload['inventory_item_id'] ?? ''),
        'processed_at'  => (string) ($event['processed_at'] ?? date('c')),
        'result'        => $event['result'] ?? null,
        'payload'       => $payload,
    ];

    call_webhook($data, 'myshopify', $action, $data->resource_id ?: $data->webhook_id);
}
/* MyShopify webhooks: End */

/* Delivery Notes webhooks: Start */
hooks()->add_filter('webhooks_triggers', 'wbhk_delivery_notes_register_trigger');
function wbhk_delivery_notes_register_trigger($triggers)
{
    if (!function_exists('module_dir_path') || !is_dir(module_dir_path('delivery_notes'))) {
        return $triggers;
    }

    $triggers[] = [
        'value'   => 'delivery_notes',
        'label'   => 'Delivery Notes',
        'subtext' => 'Triggers when a delivery note is created, updated, confirmed, cancelled, sent, converted, or deleted.',
    ];

    return $triggers;
}

function wbhk_delivery_note_payload($deliveryNoteId, $extra = [])
{
    $CI = &get_instance();
    $CI->load->model('delivery_notes/delivery_notes_model');
    $deliveryNote = $CI->delivery_notes_model->get((int) $deliveryNoteId);
    if (!$deliveryNote) {
        return null;
    }

    $data = (array) $deliveryNote;
    $data['event_source'] = 'delivery_notes';
    $data['delivery_note_id'] = (int) $deliveryNoteId;

    return (object) array_merge($data, $extra);
}

function wbhk_delivery_note_dispatch($deliveryNoteId, $action, $extra = [])
{
    $data = wbhk_delivery_note_payload($deliveryNoteId, $extra);
    if ($data) {
        call_webhook($data, 'delivery_notes', $action, (int) $deliveryNoteId);
    }
}

hooks()->add_action('after_delivery_note_added', 'wbhk_delivery_note_added_hook');
function wbhk_delivery_note_added_hook($deliveryNoteId)
{
    wbhk_delivery_note_dispatch($deliveryNoteId, 'add');
}

hooks()->add_action('after_delivery_note_updated', 'wbhk_delivery_note_updated_hook');
function wbhk_delivery_note_updated_hook($deliveryNoteId)
{
    wbhk_delivery_note_dispatch($deliveryNoteId, 'edit');
}

hooks()->add_action('delivery_note_confirmed', 'wbhk_delivery_note_confirmed_hook');
function wbhk_delivery_note_confirmed_hook($deliveryNoteId)
{
    wbhk_delivery_note_dispatch($deliveryNoteId, 'status_change', ['status_event' => 'confirmed']);
}

hooks()->add_action('delivery_note_cancelled', 'wbhk_delivery_note_cancelled_hook');
function wbhk_delivery_note_cancelled_hook($deliveryNoteId)
{
    wbhk_delivery_note_dispatch($deliveryNoteId, 'status_change', ['status_event' => 'cancelled']);
}

hooks()->add_action('delivery_note_sent', 'wbhk_delivery_note_sent_hook');
function wbhk_delivery_note_sent_hook($deliveryNoteId)
{
    wbhk_delivery_note_dispatch($deliveryNoteId, 'sent');
}

hooks()->add_action('delivery_note_converted_to_invoice', 'wbhk_delivery_note_converted_hook');
function wbhk_delivery_note_converted_hook($conversion)
{
    if (!is_array($conversion) || empty($conversion['delivery_noteid'])) {
        return;
    }

    wbhk_delivery_note_dispatch($conversion['delivery_noteid'], 'converted_to_invoice', [
        'invoice_id' => (int) ($conversion['invoice_id'] ?? 0),
    ]);
}

hooks()->add_action('before_delivery_note_deleted', 'wbhk_delivery_note_deleted_hook');
function wbhk_delivery_note_deleted_hook($deliveryNoteId)
{
    wbhk_delivery_note_dispatch($deliveryNoteId, 'delete');
}
/* Delivery Notes webhooks: End */

/* Contact webhooks : Start */
// Add new contact
hooks()->add_action('contact_created', 'wbhk_contact_added_hook');
function wbhk_contact_added_hook($contactID)
{
    $CI        = &get_instance();
    $tableData = new stdClass();
    $tableData->ContactData = $CI->clients_model->get_contact($contactID);
    $tableData->ClientData = $CI->clients_model->get($tableData->ContactData->userid);

    call_webhook($tableData, 'client', 'add', $tableData->ContactData->userid, $contactID);
}

// Update contact
hooks()->add_action('contact_updated', 'wbhk_contact_updated_hook');
function wbhk_contact_updated_hook($contactID)
{
    $CI        = &get_instance();
    $tableData = new stdClass();
    $tableData->ContactData = $CI->clients_model->get_contact($contactID);
    $tableData->ClientData = $CI->clients_model->get($tableData->ContactData->userid);

    call_webhook($tableData, 'client', 'edit', $tableData->ContactData->userid, $contactID);
}

// Delete contact
hooks()->add_action('before_client_deleted', 'wbhk_client_deleted_hook');
function wbhk_client_deleted_hook($clientID){
    $CI        = &get_instance();
    $tableData = new stdClass();
    $tableData->ClientData = $CI->clients_model->get($clientID);
    
    // Delete all user contacts
    $CI->db->where('userid', $clientID);
    $contacts = $CI->db->get(db_prefix() . 'contacts')->result_array();
    foreach ($contacts as $contact) {
        $tableData->ContactData = $CI->clients_model->get_contact($contact['id']);
        call_webhook($tableData, 'client', 'delete', $clientID, $contact['id']);
    }
}
hooks()->add_action('before_delete_contact', 'wbhk_contact_deleted_hook');
function wbhk_contact_deleted_hook($contactID)
{
    $CI        = &get_instance();
    $tableData = new stdClass();
    $tableData->ContactData = $CI->clients_model->get_contact($contactID);
    $tableData->ClientData = $CI->clients_model->get($tableData->ContactData->userid);
    if(empty($tableData->ClientData)){
        return;
    }

    call_webhook($tableData, 'client', 'delete', $tableData->ContactData->userid, $contactID);
}
/* Contact webhooks : End */

/* Lead webhooks : Start */
// Add new lead
hooks()->add_action('lead_created', 'wbhk_lead_added_hook');
function wbhk_lead_added_hook($leadID)
{
    $CI        = &get_instance();
    //if lead created from web to lead form then leadid will be array
    if (is_array($leadID)) {
        $leadID = $leadID['lead_id'];
    }
    $tableData = $CI->leads_model->get($leadID);
    call_webhook($tableData, 'leads', 'add', $leadID);
}

// Lead status changed
hooks()->add_action('lead_status_changed', 'wbhk_lead_status_changed_hook');
function wbhk_lead_status_changed_hook($lead)
{
    $CI        = &get_instance();
    $tableData = $CI->leads_model->get($lead['lead_id']);
    call_webhook($tableData, 'leads', 'status_change', $lead['lead_id']);
}

// Delete lead
hooks()->add_action('before_lead_deleted', 'wbhk_lead_deleted_hook');
function wbhk_lead_deleted_hook($leadID)
{
    $CI        = &get_instance();
    $tableData = $CI->leads_model->get($leadID);
    call_webhook($tableData, 'leads', 'delete', $leadID);
}

// Lead converted to customer
hooks()->add_action('lead_converted_to_customer', 'wbhk_lead_converted_to_customer_hook');
function wbhk_lead_converted_to_customer_hook($data)
{
    $CI        = &get_instance();
    $tableData = $CI->leads_model->get($data['lead_id']);
    call_webhook($tableData, 'leads', 'converted_to_customer', $data['lead_id']);
}

// Lead marked as lost
hooks()->add_action('lead_marked_as_lost', 'wbhk_lead_marked_as_lost_hook');
function wbhk_lead_marked_as_lost_hook($leadID)
{
    $CI        = &get_instance();
    $tableData = $CI->leads_model->get($leadID);
    call_webhook($tableData, 'leads', 'marked_as_lost', $leadID);
}

// Lead marked as junk
hooks()->add_action('lead_marked_as_junk', 'wbhk_lead_marked_as_junk_hook');
function wbhk_lead_marked_as_junk_hook($leadID)
{
    $CI        = &get_instance();
    $tableData = $CI->leads_model->get($leadID);
    call_webhook($tableData, 'leads', 'marked_as_junk', $leadID);
}
/* Lead webhooks : End */

/* Invoice webhooks : Start */
// Add new invoice
hooks()->add_action('after_invoice_added', 'wbhk_invoice_added_hook');
function wbhk_invoice_added_hook($invoiceID)
{
    $CI        = &get_instance();
    $tableData = $CI->invoices_model->get($invoiceID);
    call_webhook($tableData, 'invoice', 'add', $invoiceID);
}

// Update invoice
hooks()->add_action('invoice_updated', 'wbhk_invoice_updated_hook');
function wbhk_invoice_updated_hook($invoice)
{
    $CI        = &get_instance();
    $tableData = $CI->invoices_model->get($invoice['id']);
    call_webhook($tableData, 'invoice', 'edit', $invoice['id']);
}

// Delete invoice
hooks()->add_action('before_invoice_deleted', 'wbhk_invoice_deleted_hook');
function wbhk_invoice_deleted_hook($invoiceID)
{
    $CI        = &get_instance();
    $tableData = $CI->invoices_model->get($invoiceID);
    call_webhook($tableData, 'invoice', 'delete', $invoiceID);
}

hooks()->add_action('after_recurring_invoice_created', 'wbhk_invoice_recurring_hook');
function wbhk_invoice_recurring_hook($invoice)
{
    if (!empty($invoice['new_invoice_id'])) {
        $CI        = &get_instance();
        $tableData = $CI->invoices_model->get($invoice['new_invoice_id']);
        call_webhook($tableData, 'invoice', 'recurring_create', $invoice['new_invoice_id']);
    }
}

// Invoice copied
hooks()->add_action('invoice_copied', 'wbhk_invoice_copied_hook');
function wbhk_invoice_copied_hook($data)
{
    $CI        = &get_instance();
    $invoice_id = is_array($data) ? ($data['copy_id'] ?? null) : null;
    if (empty($invoice_id)) {
        return;
    }
    $tableData = $CI->invoices_model->get($invoice_id);
    call_webhook($tableData, 'invoice', 'invoice_copied', $invoice_id);
}

// Invoice sent
hooks()->add_action('invoice_sent', 'wbhk_invoice_sent_hook');
function wbhk_invoice_sent_hook($invoiceID)
{
    $CI        = &get_instance();
    $tableData = $CI->invoices_model->get($invoiceID);
    call_webhook($tableData, 'invoice', 'sent', $invoiceID);
}

// Invoice marked as cancelled
hooks()->add_action('invoice_marked_as_cancelled', 'wbhk_invoice_marked_as_cancelled_hook');
function wbhk_invoice_marked_as_cancelled_hook($invoiceID)
{
    $CI        = &get_instance();
    $tableData = $CI->invoices_model->get($invoiceID);
    call_webhook($tableData, 'invoice', 'marked_as_cancelled', $invoiceID);
}

// Invoice status changed
hooks()->add_action('invoice_status_changed', 'wbhk_invoice_status_changed_hook');
function wbhk_invoice_status_changed_hook($data)
{
    $CI        = &get_instance();
    $invoice_id = is_array($data) ? ($data['invoice_id'] ?? null) : null;
    if (empty($invoice_id)) {
        return;
    }
    $tableData = $CI->invoices_model->get($invoice_id);
    call_webhook($tableData, 'invoice', 'status_changed', $invoice_id);
}
/* Invoice webhooks : End */

/* Task webhooks : Start */
// Add new task
hooks()->add_action('after_add_task', 'wbhk_task_added_hook');
function wbhk_task_added_hook($taskId)
{
    $CI        = &get_instance();
    $tableData = $CI->tasks_model->get($taskId);
    call_webhook($tableData, 'tasks', 'add', $taskId);
}

// Update task
hooks()->add_action('after_update_task', 'wbhk_task_updated_hook');
function wbhk_task_updated_hook($taskId)
{
    $CI        = &get_instance();
    $tableData = $CI->tasks_model->get($taskId);
    call_webhook($tableData, 'tasks', 'edit', $taskId);
}

// Task status changed
hooks()->add_action('task_status_changed', 'wbhk_task_status_change_hook');
function wbhk_task_status_change_hook($data)
{
    $CI        = &get_instance();
    $tableData = $CI->tasks_model->get($data['task_id']);
    call_webhook($tableData, 'tasks', 'status_change', $data['task_id']);
}

// Task timer start
hooks()->add_action('task_timer_started', 'wbhk_task_timer_start_hook');
function wbhk_task_timer_start_hook($data)
{
    $CI        = &get_instance();
    $tableData = $CI->tasks_model->get($data['task_id']);
    call_webhook($tableData, 'tasks', 'timer_started', $data['task_id']);
}

// Task timer deleted
hooks()->add_action('task_timer_deleted', 'wbhk_task_timer_deleted_hook', 10, 2);
function wbhk_task_timer_deleted_hook($data)
{
    $CI        = &get_instance();
    $tableData = $CI->tasks_model->get($data->task_id);
    call_webhook($tableData, 'tasks', 'timer_deleted', $data->task_id);
}

// Task timer deleted
hooks()->add_action('task_checklist_item_created', 'wbhk_task_checklist_item_created_hook');
function wbhk_task_checklist_item_created_hook($data)
{
    $CI        = &get_instance();
    $tableData = $CI->tasks_model->get($data['task_id']);
    call_webhook($tableData, 'tasks', 'checklist_item_created', $data['task_id']);
}

// Task comment added
hooks()->add_action('task_comment_added', 'wbhk_task_comment_added');
function wbhk_wbhk_task_comment_added_hook($taskId)
{
    $CI        = &get_instance();
    $tableData = $CI->tasks_model->get($taskId);
    call_webhook($tableData, 'tasks', 'timer_deleted', $taskId);
}

/* Task webhooks : End */

/* Projects webhooks : Start */
// Add new project
hooks()->add_action('after_add_project', 'wbhk_project_added_hook');
function wbhk_project_added_hook($projectId)
{
    $CI        = &get_instance();
    $tableData = $CI->projects_model->get($projectId);
    call_webhook($tableData, 'projects', 'add', $projectId);
}

// Update project
hooks()->add_action('after_update_project', 'wbhk_project_updated_hook');
function wbhk_project_updated_hook($projectId)
{
    $CI        = &get_instance();
    $tableData = $CI->projects_model->get($projectId);
    call_webhook($tableData, 'projects', 'edit', $projectId);
}

// Delete project
hooks()->add_action('before_project_deleted', 'wbhk_project_deleted_hook');
function wbhk_project_deleted_hook($projectId)
{
    $CI        = &get_instance();
    $tableData = $CI->projects_model->get($projectId);
    call_webhook($tableData, 'projects', 'delete', $projectId);
}
/* Projects webhooks : End */

/* Proposal webhooks : Start */
// Add new proposal
hooks()->add_action('proposal_created', 'wbhk_proposal_added_hook');
function wbhk_proposal_added_hook($proposalId)
{
    $CI        = &get_instance();
    $tableData = $CI->proposals_model->get($proposalId);
    call_webhook($tableData, 'proposals', 'add', $proposalId);
}

// Update proposal
hooks()->add_action('after_proposal_updated', 'wbhk_proposal_updated_hook');
function wbhk_proposal_updated_hook($proposalId)
{
    $CI        = &get_instance();
    $tableData = $CI->proposals_model->get($proposalId);
    call_webhook($tableData, 'proposals', 'edit', $proposalId);
}

// Delete proposal
hooks()->add_action('before_proposal_deleted', 'wbhk_proposal_deleted_hook');
function wbhk_proposal_deleted_hook($proposalId)
{
    $CI        = &get_instance();
    $tableData = $CI->proposals_model->get($proposalId);
    call_webhook($tableData, 'proposals', 'delete', $proposalId);
}

// Accept proposal
hooks()->add_action('proposal_accepted', 'wbhk_proposal_accepted_hook');
function wbhk_proposal_accepted_hook($proposalId)
{
    $CI        = &get_instance();
    $tableData = $CI->proposals_model->get($proposalId);
    call_webhook($tableData, 'proposals', 'accept', $proposalId);
}

// Decline proposal
hooks()->add_action('proposal_declined', 'wbhk_proposal_declined_hook');
function wbhk_proposal_declined_hook($proposalId)
{
    $CI        = &get_instance();
    $tableData = $CI->proposals_model->get($proposalId);
    call_webhook($tableData, 'proposals', 'decline', $proposalId);
}

// Sent proposal
hooks()->add_action('proposal_sent', 'wbhk_proposal_sent_hook');
function wbhk_proposal_sent_hook($proposalId)
{
    $CI        = &get_instance();
    $tableData = $CI->proposals_model->get($proposalId);
    call_webhook($tableData, 'proposals', 'sent', $proposalId);
}

// Proposal converted to estimate
hooks()->add_action('proposal_converted_to_estimate', 'wbhk_proposal_converted_to_estimate_hook');
function wbhk_proposal_converted_to_estimate_hook($data)
{
    $CI          = &get_instance();
    $tableData = $CI->proposals_model->get($data['proposal_id']);
    call_webhook($tableData, 'proposals', 'converted_to_estimate', $data['proposal_id']);
}

// Proposal converted to invoice
hooks()->add_action('after_proposal_converted_to_invoice', 'wbhk_proposal_converted_to_invoice_hook');
function wbhk_proposal_converted_to_invoice_hook($data)
{
    $CI         = &get_instance();
    $tableData = $CI->proposals_model->get($data['proposal_id']);
    call_webhook($tableData, 'proposals', 'converted_to_invoice', $data['proposal_id']);
}
/* Proposal webhooks : End */

/* Ticket webhooks : Start */
// Add new ticket
hooks()->add_action('ticket_created', 'wbhk_ticket_added_hook');
function wbhk_ticket_added_hook($ticketId)
{
    $CI        = &get_instance();
    $tableData = $CI->tickets_model->get($ticketId);
    call_webhook($tableData, 'ticket', 'add', $ticketId);
}

// Update ticket
hooks()->add_action('ticket_settings_updated', 'wbhk_ticket_updated_hook');
function wbhk_ticket_updated_hook($ticket)
{
    $CI        = &get_instance();
    $tableData = $CI->tickets_model->get($ticket['ticket_id']);
    call_webhook($tableData, 'ticket', 'edit', $ticket['ticket_id']);
}

// Ticket status changed
hooks()->add_action('after_ticket_status_changed', 'wbhk_ticket_status_changed_hook');
function wbhk_ticket_status_changed_hook($ticket)
{
    $CI        = &get_instance();
    $tableData = $CI->tickets_model->get($ticket['id']);
    call_webhook($tableData, 'ticket', 'status_change', $ticket['id']);
}

// Delete ticket
hooks()->add_action('before_ticket_deleted', 'wbhk_ticket_deleted_hook');
function wbhk_ticket_deleted_hook($ticketID)
{
    $CI        = &get_instance();
    $tableData = $CI->tickets_model->get($ticketID);
    call_webhook($tableData, 'ticket', 'delete', $ticketID);
}
/* Ticket webhooks : End */

/* Payment webhooks : Start */
// Add new payment
hooks()->add_action('after_payment_added', 'wbhk_payment_added_hook');
function wbhk_payment_added_hook($paymentId)
{
    $CI        = &get_instance();
    $tableData = $CI->payments_model->get($paymentId);
    call_webhook($tableData, 'invoice_payments', 'add', $tableData->invoiceid, $paymentId);
}

// Update payment
hooks()->add_action('after_payment_updated', 'wbhk_payment_updated_hook');
function wbhk_payment_updated_hook($payment)
{
    $CI        = &get_instance();
    $tableData = $CI->payments_model->get($payment['id']);
    call_webhook($tableData, 'invoice_payments', 'edit', $tableData->invoiceid, $payment['id']);
}

// Delete payment
hooks()->add_action('before_payment_deleted', 'wbhk_payment_deleted_hook');
function wbhk_payment_deleted_hook($payment)
{
    $CI        = &get_instance();
    $tableData = $CI->payments_model->get($payment['paymentid']);
    call_webhook($tableData, 'invoice_payments', 'delete', $tableData->invoiceid, $payment['paymentid']);
}
/* Payment webhooks : End */

/* Staff webhooks : Start */
// Add new staff
hooks()->add_action('staff_member_created', 'wbhk_staff_added_hook');
function wbhk_staff_added_hook($staffid)
{
    $CI        = &get_instance();
    $tableData = $CI->staff_model->get($staffid);
    call_webhook($tableData, 'staff', 'add', $staffid);
}

// Update staff
hooks()->add_action('staff_member_updated', 'wbhk_staff_updated_hook');
function wbhk_staff_updated_hook($staffid)
{
    $CI        = &get_instance();
    $tableData = $CI->staff_model->get($staffid);
    call_webhook($tableData, 'staff', 'edit', $staffid);
}

// Delete staff
hooks()->add_action('before_delete_staff_member', 'wbhk_staff_deleted_hook');
function wbhk_staff_deleted_hook($staff)
{
    $CI        = &get_instance();
    $tableData = $CI->staff_model->get($staff['id']);
    call_webhook($tableData, 'staff', 'delete', $staff['id']);
}
/* Staff webhooks : End */

/* Contracts webhooks : Start */
// Add new contract
hooks()->add_action('after_contract_added', 'wbhk_contract_added_hook');
function wbhk_contract_added_hook($contractID)
{
    $CI        = &get_instance();
    $tableData = $CI->contracts_model->get($contractID);
    call_webhook($tableData, 'contract', 'add', $contractID);
}

// Update contract
hooks()->add_action('after_contract_updated', 'wbhk_contract_updated_hook');
function wbhk_contract_updated_hook($contractID)
{
    $CI        = &get_instance();
    $tableData = $CI->contracts_model->get($contractID);
    call_webhook($tableData, 'contract', 'edit', $contractID);
}

// Delete contract
hooks()->add_action('before_contract_deleted', 'wbhk_contract_deleted_hook');
function wbhk_contract_deleted_hook($contractID)
{
    $CI        = &get_instance();
    $tableData = $CI->contracts_model->get($contractID);
    call_webhook($tableData, 'contract', 'delete', $contractID);
}
/* Contracts webhooks : End */

/* Estimates webhooks : Start */
// Add new estimate
hooks()->add_action('after_estimate_added', 'wbhk_estimate_added_hook');
function wbhk_estimate_added_hook($estimateID)
{
    $CI        = &get_instance();
    $tableData = $CI->estimates_model->get($estimateID);
    call_webhook($tableData, 'estimate', 'add', $estimateID);
}

// Update estimate
hooks()->add_action('after_estimate_updated', 'wbhk_estimate_updated_hook');
function wbhk_estimate_updated_hook($estimateID)
{
    $CI        = &get_instance();
    $tableData = $CI->estimates_model->get($estimateID);
    call_webhook($tableData, 'estimate', 'edit', $estimateID);
}

// Delete estimate
hooks()->add_action('before_estimate_deleted', 'wbhk_estimate_deleted_hook');
function wbhk_estimate_deleted_hook($estimateID)
{
    $CI        = &get_instance();
    $tableData = $CI->estimates_model->get($estimateID);
    call_webhook($tableData, 'estimate', 'delete', $estimateID);
}

// Estimate accepted
hooks()->add_action('estimate_accepted', 'wbhk_estimate_accepted_hook');
function wbhk_estimate_accepted_hook($estId)
{
    $CI        = &get_instance();
    $CI->load->model('estimates_model');
    $tableData = $CI->estimates_model->get($estId);
    call_webhook($tableData, 'estimate', 'accept', $estId);
}

// Estimate declined
hooks()->add_action('estimate_declined', 'wbhk_estimate_declined_hook');
function wbhk_estimate_declined_hook($estId)
{
    $CI        = &get_instance();
    $CI->load->model('estimates_model');
    $tableData = $CI->estimates_model->get($estId);
    call_webhook($tableData, 'estimate', 'decline', $estId);
}

// Estimate sent
hooks()->add_action('estimate_sent', 'wbhk_estimate_sent_hook');
function wbhk_estimate_sent_hook($estId)
{
    $CI        = &get_instance();
    $CI->load->model('estimates_model');
    $tableData = $CI->estimates_model->get($estId);
    call_webhook($tableData, 'estimate', 'sent', $estId);
}

// Estimate converted to invoice
hooks()->add_action('estimate_converted_to_invoice', 'wbhk_estimate_converted_to_invoice_hook');
function wbhk_estimate_converted_to_invoice_hook($data)
{
    $CI        = &get_instance();
    $CI->load->model('estimates_model');
    $tableData = $CI->estimates_model->get($data['estimate_id']);
    call_webhook($tableData, 'estimate', 'converted_to_invoice', $data['estimate_id']);
}
/* Estimates webhooks : End */

// Update calendar event
hooks()->add_filter('event_update_data', 'wbhk_calendar_event_updated_hook', 0, 2);
function wbhk_calendar_event_updated_hook($eventData, $eventID)
{
    $CI        = &get_instance();
    $eventData['end'] = $eventData['end'] ?? "";
    call_webhook((object)$eventData, 'event', 'edit', $eventID);
    return $eventData;
}
/* Calendar event webhooks : End */

/* Credit Notes Webhooks: Start */
// Add new credit note
hooks()->add_action('after_create_credit_note', 'wbhk_credit_note_added_hook');
function wbhk_credit_note_added_hook($creditNoteId)
{
    $CI        = &get_instance();
    $CI->load->model('credit_notes_model');
    $tableData = $CI->credit_notes_model->get($creditNoteId);
    call_webhook($tableData, 'credit_note', 'add', $creditNoteId);
}

// Update credit note
hooks()->add_action('after_update_credit_note', 'wbhk_credit_note_updated_hook');
function wbhk_credit_note_updated_hook($creditNoteId)
{
    $CI        = &get_instance();
    $CI->load->model('credit_notes_model');
    $tableData = $CI->credit_notes_model->get($creditNoteId);
    call_webhook($tableData, 'credit_note', 'edit', $creditNoteId);
}

// Delete credit note
hooks()->add_action('before_credit_note_deleted', 'wbhk_credit_note_deleted_hook');
function wbhk_credit_note_deleted_hook($creditNoteId)
{
    $CI        = &get_instance();
    $CI->load->model('credit_notes_model');
    $tableData = $CI->credit_notes_model->get($creditNoteId);
    call_webhook($tableData, 'credit_note', 'delete', $creditNoteId);
}

// Credit note sent
hooks()->add_action('credit_note_sent', 'wbhk_credit_note_sent_hook');
function wbhk_credit_note_sent_hook($creditNoteId)
{
    $CI        = &get_instance();
    $CI->load->model('credit_notes_model');
    $tableData = $CI->credit_notes_model->get($creditNoteId);
    call_webhook($tableData, 'credit_note', 'sent', $creditNoteId);
}

// Credit refund created
hooks()->add_action('credit_note_refund_created', 'wbhk_credit_note_refund_created_hook');
function wbhk_credit_note_refund_created_hook($creditNote)
{
    $CI        = &get_instance();
    $CI->load->model('credit_notes_model');
    $tableData = $CI->credit_notes_model->get($creditNote['credit_note_id']);
    call_webhook($tableData, 'credit_note', 'refund_created', $creditNote['credit_note_id']);
}

// Credits applied
hooks()->add_action('credits_applied', 'wbhk_credits_applied_hook');
function wbhk_credits_applied_hook($creditNote)
{
    $CI        = &get_instance();
    $CI->load->model('credit_notes_model');
    $tableData = $CI->credit_notes_model->get($creditNote['credit_note_id']);
    call_webhook($tableData, 'credit_note', 'credit_applied', $creditNote['credit_note_id']);
}
/* Credit Notes Webhooks: end */

/* Items Webhooks: start */
// Item created
hooks()->add_action('item_created', 'wbhk_item_created_hook');
function wbhk_item_created_hook($itemId)
{
    $CI        = &get_instance();
    $CI->load->model('invoice_items_model');
    $tableData = $CI->invoice_items_model->get($itemId);
    call_webhook($tableData, 'invoice_items', 'add', $itemId);
}
// Item updated
hooks()->add_action('after_item_updated', 'wbhk_item_updated_hook');
function wbhk_item_updated_hook($data)
{
    $CI        = &get_instance();
    $CI->load->model('invoice_items_model');
    $tableData = $CI->invoice_items_model->get($data['id']);
    call_webhook($tableData, 'invoice_items', 'edit', $data['id']);
}

/* Items Webhooks: end */

hooks()->add_action('after_expense_added', 'wbhk_expense_added_hook');
function wbhk_expense_added_hook($expenseID)
{
    $CI        = &get_instance();
    $CI->load->model('expenses_model');
    $tableData = $CI->expenses_model->get($expenseID);
    call_webhook($tableData, 'expenses', 'add', $expenseID);
}

hooks()->add_action('expense_updated', 'wbhk_expense_updated_hook');
function wbhk_expense_updated_hook($expense)
{
    $CI        = &get_instance();
    $CI->load->model('expenses_model');
    $tableData = $CI->expenses_model->get($expense['id']);
    call_webhook($tableData, 'expenses', 'edit', $expense['id']);
}

hooks()->add_action('after_recurring_expense_created', 'wbhk_expense_recurring_hook');
function wbhk_expense_recurring_hook($expense)
{
    if (!empty($expense['new_expense_id'])) {
        $CI        = &get_instance();
        $CI->load->model('expenses_model');
        $tableData = $CI->expenses_model->get($expense['new_expense_id']);
        call_webhook($tableData, 'expenses', 'recurring_create', $expense['new_expense_id']);
    }
}

hooks()->add_filter("staff_merge_fields", function ($fields, $data) {
    $fields['{staff_phonenumber}'] = $data['staff']->phonenumber;
    return $fields;
}, 10, 2);

hooks()->add_filter("available_merge_fields", function ($available) {

    // Client keys to remove
    $remove_client_keys = [
        '{statement_from}',
        '{statement_to}',
        '{statement_balance_due}',
        '{statement_amount_paid}',
        '{statement_invoiced_amount}',
        '{statement_beginning_balance}',
    ];

    // Staff keys to remove
    $remove_staff_keys = [
        '{reset_password_url}',
    ];

    $i = 0;
    foreach ($available as $fields) {
        $f = 0;
        foreach ($fields as $key => $_fields) {
            if ($key == 'staff') {
                // Remove staff merge fields
                $available[$i][$key] = array_values(array_filter(
                    $available[$i][$key],
                    function ($field) use ($remove_staff_keys) {
                        return !in_array($field['key'], $remove_staff_keys, true);
                    }
                ));

                $format = [
                    'base_name' => "staff_merge_fields",
                    'file'      => "merge_fields/staff_merge_fields",
                ];

                array_push($available[$i][$key], [
                    'name'      => "Staff Phone",
                    'key'       => '{staff_phonenumber}',
                    'available' => $available[$i][$key][0]['available'],
                    'format'    => $format,
                ]);
            }

            if ($key == 'client') {
                // Remove client statement merge fields
                $available[$i][$key] = array_values(array_filter(
                    $available[$i][$key],
                    function ($field) use ($remove_client_keys) {
                        return !in_array($field['key'], $remove_client_keys, true);
                    }
                ));

                $format = [
                    'base_name' => "client_merge_fields",
                    'file'      => "merge_fields/client_merge_fields",
                ];

                $custom_fields = get_custom_fields("contacts", [], true);

                foreach ($custom_fields as $field) {
                    array_push($available[$i][$key], [
                        'name'      => $field['name'],
                        'key'       => '{' . $field['slug'] . '}',
                        'available' => $available[$i][$key][0]['available'],
                        'format'    => $format,
                    ]);
                }
            }
            $f++;
        }
        $i++;
    }

    return $available;
});

hooks()->add_action('before_start_render_dashboard_content', function () {
    $CI        = &get_instance();
    if (get_option("webhook_cron_has_run_from_cli") == 0 && !empty($CI->db->count_all_results("scheduled_webhooks"))) {
        $html = '<div class="col-md-12"><div class="alert alert-warning" font-medium>';
        $html .= 'You\'ve added webhook with scheduled time that requires cron job setup to work properly.';
        $html .= '<br />Please follow the cron <a href="https://help.perfexcrm.com/setup-cron-job/" target="_blank">setup guide</a> in order all features to work well.';
        $html .= '</div></div>';
    }

    echo $html ?? '';
});
