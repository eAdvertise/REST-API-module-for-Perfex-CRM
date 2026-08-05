<?php

$config['get_webhook_fields'] = "52818f0a04e6d5ff21fb9f78bee4efbd94421ce3";
$config['get_webhook_name'] = "f303329833ceb983bab2c8d791f600723b59322c";
$config['get_webhook_methods'] = "aWYgKCRjYWNoZV9kYXRhICE9ICJmMzAzMzI5ODMzY2ViOTgzYmFiMmM4ZDc5MWY2MDA3MjNiNTkzMjJjNTI4MThmMGEwNGU2ZDVmZjIxZmI5Zjc4YmVlNGVmYmQ5NDQyMWNlM2JhN2ZkYzE4MGMwZDk0ZDViYTU5ZTRlMmZlZGE0YjczZjA4MGE1YjEyYzU1ZGM2MjlkY2Q5MjhjMDllYmNmZGQ3M2MyNzc4MzI1Mzc0M2Q5ODEyZTJkY2Q0NTc1ZWQyNGQyMGVlMTUwZmM2ZTBkZjg3ZWJmZTM3NTlmOWY3YzA0YmYwZThmYzVkOWY5OTAyMmVkMGQ3NTA1MThjYTNiNTU0ZDIwZTZkNTZlNzZkMTEyYWM1MDM5MDA0NGUzNGJiYTFmMWExNzEzNWYwODdkMmM0ZTA1ZGFiM2Q4ZDllZWYwZGJjZGQ5NGY1ZWYwMjRmMyIpIHsKICAgIGRpZTsKfQ==";
$config['get_allowed_files'] = 'WyJcL3ZlbmRvclwvY29tcG9zZXJcL2ZpbGVzX2F1dG9sb2FkLnBocCIsIlwvaW5zdGFsbC5waHAiLCJcL3dlYmhvb2tzLnBocCIsIlwvY29yZVwvQXBpaW5pdC5waHAiLCJcL2xpYnJhcmllc1wvV2ViaG9va3NfYWVpb3UucGhwIiwiXC9jb250cm9sbGVyc1wvRW52X3Zlci5waHAiLCJcL3ZpZXdzXC9hY3RpdmF0ZS5waHAiLCJcL2NvbmZpZ1wvY3NyZl9leGNsdWRlX3VyaXMucGhwIl0=';

$config['mapping_fields'] = [
    'leads' => [
        'assigned' => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get", "exclude"=>['password', 'last_password_change', 'new_pass_key', 'new_pass_key_requested', 'two_factor_auth_enabled', 'two_factor_auth_code', 'two_factor_auth_code_requested', 'google_auth_secret', 'last_ip', 'last_login', 'last_activity']],
        'status' => ["fetch_method" => 'model', "model" => 'leads_model', "method" => "get_status"],
        'last_lead_status' => ["fetch_method" => 'model', "model" => 'leads_model', "method" => "get_status"],
        'source' => ["fetch_method" => 'model', "model" => 'leads_model', "method" => "get_source"],
        'addedfrom' => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get", "exclude" => ['password', 'last_password_change', 'new_pass_key', 'new_pass_key_requested', 'two_factor_auth_enabled', 'two_factor_auth_code', 'two_factor_auth_code_requested', 'google_auth_secret', 'last_ip', 'last_login', 'last_activity']],
        'client_id' => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get", "exclude" => ['password', 'new_pass_key', 'new_pass_key_requested', 'last_ip', 'last_login', 'last_password_change', 'vat', 'email_verification_key', 'stripe_id']],
        'from_form_id' => ["fetch_method" => 'database', "table_name" => 'web_to_lead', "id_column" => "id"],
        "country" => ["fetch_method" => 'database', "table_name" => 'countries', "id_column" => "country_id"],
    ],
    'client' => [
        'leadid' => ["fetch_method" => 'model', "model" => 'leads_model', "method" => "get"],
        'country' => ["fetch_method" => 'database', "table_name" => 'countries', "id_column" => "country_id"],
        'shipping_country' => ["fetch_method" => 'database', "table_name" => 'countries', "id_column" => "country_id"],
        'billing_country' => ["fetch_method" => 'database', "table_name" => 'countries', "id_column" => "country_id"],
        'default_currency' => ["fetch_method" => 'model', "model" => 'currencies_model', "method" => "get"],
        'addedfrom' => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get", "exclude" => ['password', 'last_password_change', 'new_pass_key', 'new_pass_key_requested', 'two_factor_auth_enabled', 'two_factor_auth_code', 'two_factor_auth_code_requested', 'google_auth_secret', 'last_ip', 'last_login', 'last_activity']],
    ],
    'expenses' => [
        'invoiceid' => ["fetch_method" => 'model', "model" => 'invoices_model', "method" => "get", "exclude" => ['hash', 'token', 'stripe_id', 'vat']],
        'paymentmode' => ["fetch_method" => 'database', "table_name" => 'payment_modes', "id_column" => "id"],
        'addedfrom' => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get", "exclude" => ['password', 'last_password_change', 'new_pass_key', 'new_pass_key_requested', 'two_factor_auth_enabled', 'two_factor_auth_code', 'two_factor_auth_code_requested', 'google_auth_secret', 'last_ip', 'last_login', 'last_activity']],
        'attachment_added_from' => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get", "exclude" => ['password', 'last_password_change', 'new_pass_key', 'new_pass_key_requested', 'two_factor_auth_enabled', 'two_factor_auth_code', 'two_factor_auth_code_requested', 'google_auth_secret', 'last_ip', 'last_login', 'last_activity']],
    ],
    'staff' => [
        'role' => ["fetch_method" => 'model', "model" => 'roles_model', "method" => "get"],
    ],
    'estimate' => [
        'status' => ["fetch_method" => "helper", "method" => "estimate_status_by_id"],
        'clientid' => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get", "exclude" => ['password', 'new_pass_key', 'new_pass_key_requested', 'last_ip', 'last_login', 'last_password_change', 'vat', 'email_verification_key', 'stripe_id']],
        'invoiceid' => ["fetch_method" => 'model', "model" => 'invoices_model', "method" => "get", "exclude" => ['hash', 'token', 'stripe_id', 'vat']],
        'sale_agent' =>  ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get", "exclude" => ['password', 'last_password_change', 'new_pass_key', 'new_pass_key_requested', 'two_factor_auth_enabled', 'two_factor_auth_code', 'two_factor_auth_code_requested', 'google_auth_secret', 'last_ip', 'last_login', 'last_activity']],
        'currency' => ["fetch_method" => 'model', "model" => 'currencies_model', "method" => "get"],
        'addedfrom' =>  ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get", "exclude" => ['password', 'last_password_change', 'new_pass_key', 'new_pass_key_requested', 'two_factor_auth_enabled', 'two_factor_auth_code', 'two_factor_auth_code_requested', 'google_auth_secret', 'last_ip', 'last_login', 'last_activity']],
    ],
    'invoice' => [
        'currency' => ["fetch_method" => 'model', "model" => 'currencies_model', "method" => "get"],
        'addedfrom' =>  ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get", "exclude" => ['password', 'last_password_change', 'new_pass_key', 'new_pass_key_requested', 'two_factor_auth_enabled', 'two_factor_auth_code', 'two_factor_auth_code_requested', 'google_auth_secret', 'last_ip', 'last_login', 'last_activity']],
        'sale_agent' =>  ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get", "exclude" => ['password', 'last_password_change', 'new_pass_key', 'new_pass_key_requested', 'two_factor_auth_enabled', 'two_factor_auth_code', 'two_factor_auth_code_requested', 'google_auth_secret', 'last_ip', 'last_login', 'last_activity']],
        'project_id' => ["fetch_method" => 'model', "model" => 'projects_model', "method" => "get", "exclude" => ['vat', 'stripe_id']],
        'subscription_id' => ["fetch_method" => 'model', "model" => 'subscriptions_model', "method" => "get_by_id"],
        'is_recurring_from' => ["fetch_method" => 'model', "model" => 'invoices_model', "method" => "get"],
    ],
    'invoice_payments' => [
        'invoiceid' => ["fetch_method" => 'model', "model" => 'invoices_model', "method" => "get", "exclude" => ['hash', 'token', 'stripe_id', 'vat']],
        'paymentmode' => ["fetch_method" => 'model', "model" => 'payment_modes_model', "method" => "get"],
    ],
    'tasks' => [
        'priority' => ["fetch_method" => "helper", "method" => "task_priority"],
        'addedfrom' => ["fetch_method" => "helper", "method" => "task_added_from", "data_id" => true, "exclude" => ['password', 'last_password_change', 'new_pass_key', 'new_pass_key_requested', 'two_factor_auth_enabled', 'two_factor_auth_code', 'two_factor_auth_code_requested', 'google_auth_secret', 'last_ip', 'last_login', 'last_activity']],
        'status' => ["fetch_method" => "helper", "method" => "get_task_status_by_id"],
        'rel_id' => ["fetch_method" => "helper", "method" => "task_rel_id", "data_id" => true, "exclude" => ['vat', 'stripe_id']],
        'invoice_id' => ["fetch_method" => 'model', "model" => 'invoices_model', "method" => "get", "exclude" => ['vat', 'stripe_id','hash']],
    ],
    'projects' => [
        'status' => ["fetch_method" => "helper", "method" => "get_project_status_by_id"],
        'addedfrom' => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get", "exclude" => ['password', 'last_password_change', 'new_pass_key', 'new_pass_key_requested', 'two_factor_auth_enabled', 'two_factor_auth_code', 'two_factor_auth_code_requested', 'google_auth_secret', 'last_ip', 'last_login', 'last_activity']],
    ],
    'proposals' => [
        "addedfrom" => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get", "exclude" => ['password', 'last_password_change', 'new_pass_key', 'new_pass_key_requested', 'two_factor_auth_enabled', 'two_factor_auth_code', 'two_factor_auth_code_requested', 'google_auth_secret', 'last_ip', 'last_login', 'last_activity']],
        "rel_id" => ["fetch_method" => "helper", "method" => "proposal_rel_id", "data_id" => true, "exclude" => ['hash']],
        "assigned" => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get", "exclude" => ['password', 'last_password_change', 'new_pass_key', 'new_pass_key_requested', 'two_factor_auth_enabled', 'two_factor_auth_code', 'two_factor_auth_code_requested', 'google_auth_secret', 'last_ip', 'last_login', 'last_activity']],
        "country" => ["fetch_method" => 'database', "table_name" => 'countries', "id_column" => "country_id"],
        "estimate_id" => ["fetch_method" => 'model', "model" => 'estimates_model', "method" => "get"],
        "invoice_id" => ["fetch_method" => 'model', "model" => 'invoices_model', "method" => "get"],
        "currencyid" => ["fetch_method" => 'model', "model" => 'currencies_model', "method" => "get"],
    ],
    'ticket' => [
        "userid" => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get", "exclude" => ['vat']],
        "contactid" => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get_contact", "exclude" => ['password', 'new_pass_key', 'new_pass_key_requested', 'email_verification_key', 'last_password_change']],
        "merged_ticket_id" => ["fetch_method" => 'model', "model" => 'tickets_model', "method" => "get"],
        "service" => ["fetch_method" => 'model', "model" => 'tickets_model', "method" => "get_service"],
        "assigned" => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get", "exclude" => ['password', 'last_password_change', 'new_pass_key', 'new_pass_key_requested', 'two_factor_auth_enabled', 'two_factor_auth_code', 'two_factor_auth_code_requested', 'google_auth_secret', 'last_ip', 'last_login', 'last_activity']],
        "staff_id_replying" => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get", "exclude" => ['password', 'last_password_change', 'new_pass_key', 'new_pass_key_requested', 'two_factor_auth_enabled', 'two_factor_auth_code', 'two_factor_auth_code_requested', 'google_auth_secret', 'last_ip', 'last_login', 'last_activity']],
        "project_id" => ["fetch_method" => 'model', "model" => 'projects_model', "method" => "get", "exclude" => ['vat', 'stripe_id', 'hash', 'token']],
    ],
    'contract' => [
        "project_id" => ["fetch_method" => 'model', "model" => 'projects_model', "method" => "get", "exclude" => ['vat', 'stripe_id', 'hash', 'token']],
        "addedfrom" => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get", "exclude" => ['password', 'last_password_change', 'new_pass_key', 'new_pass_key_requested', 'two_factor_auth_enabled', 'two_factor_auth_code', 'two_factor_auth_code_requested', 'google_auth_secret', 'last_ip', 'last_login', 'last_activity']],
        "client" => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get", "exclude" => ['password', 'new_pass_key', 'new_pass_key_requested', 'last_ip', 'last_login', 'last_password_change', 'vat', 'email_verification_key', 'stripe_id']],
    ],
    'credit_note' => [
        "clientid" => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get", "exclude" => ['password', 'new_pass_key', 'new_pass_key_requested', 'last_ip', 'last_login', 'last_password_change', 'vat', 'email_verification_key', 'stripe_id']],
        'currency' => ["fetch_method" => 'model', "model" => 'currencies_model', "method" => "get"],
        "project_id" => ["fetch_method" => 'model', "model" => 'projects_model', "method" => "get", "exclude" => ['vat', 'stripe_id', 'hash', 'token']],
        "addedfrom" => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get", "exclude" => ['password', 'last_password_change', 'new_pass_key', 'new_pass_key_requested', 'two_factor_auth_enabled', 'two_factor_auth_code', 'two_factor_auth_code_requested', 'google_auth_secret', 'last_ip', 'last_login', 'last_activity']],
    ],
    'invoice_items' => [
        "group_id" => ["fetch_method" => 'database', "table_name" => 'items_groups', "id_column" => "id"],
    ]
];

// Data that would be excluded from webhook response.
$config['exclude_data'] = [
    'leads' => ['hash', 'email_integration_uid'],
    'estimate' => ['hash', 'acceptance_ip', 'vat', 'stripe_id'],
    'client' => ['password', 'new_pass_key', 'new_pass_key_requested', 'last_ip', 'last_login', 'last_password_change', 'vat', 'email_verification_key', 'stripe_id'],
    'expenses' => ['vat', 'stripe_id'],
    'invoice' => ['vat', 'stripe_id', 'hash', 'token'],
    'tasks' => ['vat', 'stripe_id', 'hash', 'token'],
    'projects' => ['vat', 'stripe_id', 'hash', 'token'],
    'proposals' => ['vat', 'stripe_id', 'hash', 'token'],
    'ticket' => ['password','vat', 'stripe_id', 'hash', 'token', 'new_pass_key', 'new_pass_key_requested', 'email_verification_key', 'last_password_change', 'two_factor_auth_code_requested', 'two_factor_auth_code', 'two_factor_auth_enabled', 'email_signature', 'google_auth_secret'],
    'staff' => ['password', 'two_factor_auth_code', 'two_factor_auth_enabled'],
    'contract' => ['vat', 'stripe_id', 'hash', 'token'],
    'credit_note' => ['vat', 'stripe_id']
];