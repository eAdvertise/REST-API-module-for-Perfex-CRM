<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Comprehensive API Samples for Sandbox
 * This file contains all available API endpoints with sample requests
 */

return [
    // Leads
    'get_leads' => [
        'method' => 'GET',
        'endpoint' => 'leads',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Retrieve all leads'
    ],
    'get_lead_by_id' => [
        'method' => 'GET',
        'endpoint' => 'leads/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get specific lead by ID'
    ],
    'create_lead' => [
        'method' => 'POST',
        'endpoint' => 'leads',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "+1234567890",
            "company": "Example Corp",
            "source": "Website",
            "status": "New"
        }',
        'description' => 'Create a new lead with sample data'
    ],
    'update_lead' => [
        'method' => 'PUT',
        'endpoint' => 'leads/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "name": "John Smith",
            "email": "johnsmith@example.com",
            "status": "Qualified"
        }',
        'description' => 'Update lead information'
    ],
    'search_leads' => [
        'method' => 'GET',
        'endpoint' => 'leads/search/example',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Search leads by keyword'
    ],
    'delete_lead' => [
        'method' => 'DELETE',
        'endpoint' => 'leads/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Delete a lead'
    ],

    // Projects
    'get_projects' => [
        'method' => 'GET',
        'endpoint' => 'projects',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get all projects'
    ],
    'get_project_by_id' => [
        'method' => 'GET',
        'endpoint' => 'projects/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get specific project by ID'
    ],
    'create_project' => [
        'method' => 'POST',
        'endpoint' => 'projects',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "name": "API Test Project",
            "description": "A test project created via API",
            "client_id": 1,
            "start_date": "2024-01-01",
            "deadline": "2024-12-31",
            "status": "In Progress"
        }',
        'description' => 'Create a new project'
    ],
    'update_project' => [
        'method' => 'PUT',
        'endpoint' => 'projects/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "name": "Updated Project Name",
            "status": "Completed",
            "description": "Updated project description"
        }',
        'description' => 'Update project information'
    ],
    'delete_project' => [
        'method' => 'DELETE',
        'endpoint' => 'projects/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Delete a project'
    ],

    // Tasks
    'get_tasks' => [
        'method' => 'GET',
        'endpoint' => 'tasks',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get all tasks'
    ],
    'get_task_by_id' => [
        'method' => 'GET',
        'endpoint' => 'tasks/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get specific task by ID'
    ],
    'create_task' => [
        'method' => 'POST',
        'endpoint' => 'tasks',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "name": "API Test Task",
            "description": "A test task created via API",
            "project_id": 1,
            "priority": "Medium",
            "status": "To Do",
            "start_date": "2024-01-01",
            "due_date": "2024-01-31"
        }',
        'description' => 'Create a new task'
    ],
    'update_task' => [
        'method' => 'PUT',
        'endpoint' => 'tasks/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "name": "Updated Task Name",
            "status": "In Progress",
            "priority": "High"
        }',
        'description' => 'Update task information'
    ],
    'delete_task' => [
        'method' => 'DELETE',
        'endpoint' => 'tasks/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Delete a task'
    ],

    // Tickets
    'get_tickets' => [
        'method' => 'GET',
        'endpoint' => 'tickets',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Retrieve all support tickets'
    ],
    'get_ticket_by_id' => [
        'method' => 'GET',
        'endpoint' => 'tickets/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get specific ticket by ID'
    ],
    'create_ticket' => [
        'method' => 'POST',
        'endpoint' => 'tickets',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "subject": "API Test Ticket",
            "message": "This is a test ticket created via API",
            "department": "Support",
            "priority": "Medium",
            "status": "Open"
        }',
        'description' => 'Create a new support ticket'
    ],
    'update_ticket' => [
        'method' => 'PUT',
        'endpoint' => 'tickets/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "status": "In Progress",
            "priority": "High",
            "message": "Updated ticket message"
        }',
        'description' => 'Update ticket information'
    ],
    'delete_ticket' => [
        'method' => 'DELETE',
        'endpoint' => 'tickets/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Delete a ticket'
    ],

    // Invoices
    'get_invoices' => [
        'method' => 'GET',
        'endpoint' => 'invoices',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Retrieve all invoices'
    ],
    'get_invoice_by_id' => [
        'method' => 'GET',
        'endpoint' => 'invoices/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get specific invoice by ID'
    ],
    'create_invoice' => [
        'method' => 'POST',
        'endpoint' => 'invoices',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "client_id": 1,
            "date": "2024-01-15",
            "due_date": "2024-02-15",
            "currency": "USD",
            "subtotal": 1000.00,
            "total": 1000.00,
            "status": "Draft"
        }',
        'description' => 'Create a new invoice'
    ],
    'update_invoice' => [
        'method' => 'PUT',
        'endpoint' => 'invoices/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "status": "Sent",
            "total": 1200.00,
            "notes": "Updated invoice notes"
        }',
        'description' => 'Update invoice information'
    ],
    'search_invoices' => [
        'method' => 'GET',
        'endpoint' => 'invoices/search/example',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Search invoices by keyword'
    ],
    'delete_invoice' => [
        'method' => 'DELETE',
        'endpoint' => 'invoices/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Delete an invoice'
    ],

    // Estimates
    'get_estimates' => [
        'method' => 'GET',
        'endpoint' => 'estimates',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Retrieve all estimates'
    ],
    'get_estimate_by_id' => [
        'method' => 'GET',
        'endpoint' => 'estimates/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get specific estimate by ID'
    ],
    'create_estimate' => [
        'method' => 'POST',
        'endpoint' => 'estimates',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "client_id": 1,
            "date": "2024-01-15",
            "expirydate": "2024-02-15",
            "currency": "USD",
            "subtotal": 800.00,
            "total": 800.00,
            "status": "Draft"
        }',
        'description' => 'Create a new estimate'
    ],
    'update_estimate' => [
        'method' => 'PUT',
        'endpoint' => 'estimates/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "status": "Sent",
            "total": 900.00,
            "notes": "Updated estimate notes"
        }',
        'description' => 'Update estimate information'
    ],
    'search_estimates' => [
        'method' => 'GET',
        'endpoint' => 'estimates/search/example',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Search estimates by keyword'
    ],
    'delete_estimate' => [
        'method' => 'DELETE',
        'endpoint' => 'estimates/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Delete an estimate'
    ],

    // Contracts
    'get_contracts' => [
        'method' => 'GET',
        'endpoint' => 'contracts',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Retrieve all contracts'
    ],
    'get_contract_by_id' => [
        'method' => 'GET',
        'endpoint' => 'contracts/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get specific contract by ID'
    ],
    'create_contract' => [
        'method' => 'POST',
        'endpoint' => 'contracts',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "subject": "API Test Contract",
            "client": 1,
            "contract_type": "Service Agreement",
            "start_date": "2024-01-01",
            "end_date": "2024-12-31",
            "contract_value": 50000.00,
            "status": "Draft"
        }',
        'description' => 'Create a new contract'
    ],
    'update_contract' => [
        'method' => 'PUT',
        'endpoint' => 'contracts/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "status": "Active",
            "contract_value": 55000.00,
            "notes": "Updated contract terms"
        }',
        'description' => 'Update contract information'
    ],
    'delete_contract' => [
        'method' => 'DELETE',
        'endpoint' => 'contracts/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Delete a contract'
    ],

    // Credit Notes
    'get_credit_notes' => [
        'method' => 'GET',
        'endpoint' => 'credit_notes',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Retrieve all credit notes'
    ],
    'get_credit_note_by_id' => [
        'method' => 'GET',
        'endpoint' => 'credit_notes/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get specific credit note by ID'
    ],
    'create_credit_note' => [
        'method' => 'POST',
        'endpoint' => 'credit_notes',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "client_id": 1,
            "date": "2024-01-15",
            "currency": "USD",
            "subtotal": 100.00,
            "total": 100.00,
            "status": "Draft"
        }',
        'description' => 'Create a new credit note'
    ],
    'update_credit_note' => [
        'method' => 'PUT',
        'endpoint' => 'credit_notes/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "status": "Sent",
            "total": 120.00,
            "notes": "Updated credit note"
        }',
        'description' => 'Update credit note information'
    ],
    'search_credit_notes' => [
        'method' => 'GET',
        'endpoint' => 'credit_notes/search/example',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Search credit notes by keyword'
    ],
    'delete_credit_note' => [
        'method' => 'DELETE',
        'endpoint' => 'credit_notes/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Delete a credit note'
    ],

    // Expenses
    'get_expenses' => [
        'method' => 'GET',
        'endpoint' => 'expenses',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Retrieve all expenses'
    ],
    'get_expense_by_id' => [
        'method' => 'GET',
        'endpoint' => 'expenses/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get specific expense by ID'
    ],
    'create_expense' => [
        'method' => 'POST',
        'endpoint' => 'expenses',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "category": "Travel",
            "amount": 150.00,
            "date": "2024-01-15",
            "description": "Business trip expenses",
            "client_id": 1,
            "currency": "USD"
        }',
        'description' => 'Create a new expense'
    ],
    'update_expense' => [
        'method' => 'PUT',
        'endpoint' => 'expenses/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "amount": 175.00,
            "description": "Updated expense description"
        }',
        'description' => 'Update expense information'
    ],
    'search_expenses' => [
        'method' => 'GET',
        'endpoint' => 'expenses/search/travel',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Search expenses by keyword'
    ],
    'delete_expense' => [
        'method' => 'DELETE',
        'endpoint' => 'expenses/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Delete an expense'
    ],

    // Items
    'get_items' => [
        'method' => 'GET',
        'endpoint' => 'items',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Retrieve all items'
    ],
    'get_item_by_id' => [
        'method' => 'GET',
        'endpoint' => 'items/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get specific item by ID'
    ],
    'search_items' => [
        'method' => 'GET',
        'endpoint' => 'items/search/example',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Search items by keyword'
    ],

    // Contacts
    'get_contacts' => [
        'method' => 'GET',
        'endpoint' => 'contacts',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Retrieve all contacts'
    ],
    'get_contact_by_id' => [
        'method' => 'GET',
        'endpoint' => 'contacts/1/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get specific contact by ID'
    ],
    'create_contact' => [
        'method' => 'POST',
        'endpoint' => 'contacts',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "firstname": "John",
            "lastname": "Doe",
            "email": "john.doe@example.com",
            "phonenumber": "+1234567890",
            "title": "Manager",
            "customer_id": 1
        }',
        'description' => 'Create a new contact'
    ],
    'update_contact' => [
        'method' => 'PUT',
        'endpoint' => 'contacts/1/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "firstname": "John",
            "lastname": "Smith",
            "email": "john.smith@example.com",
            "title": "Senior Manager"
        }',
        'description' => 'Update contact information'
    ],
    'search_contacts' => [
        'method' => 'GET',
        'endpoint' => 'contacts/search/example',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Search contacts by keyword'
    ],
    'delete_contact' => [
        'method' => 'DELETE',
        'endpoint' => 'contacts/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Delete a contact'
    ],

    // Staff
    'get_staff' => [
        'method' => 'GET',
        'endpoint' => 'staff',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Retrieve all staff members'
    ],
    'get_staff_by_id' => [
        'method' => 'GET',
        'endpoint' => 'staff/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get specific staff member by ID'
    ],

    // Payments
    'get_payments' => [
        'method' => 'GET',
        'endpoint' => 'payments',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Retrieve all payments'
    ],
    'get_payment_by_id' => [
        'method' => 'GET',
        'endpoint' => 'payments/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get specific payment by ID'
    ],
    'create_payment' => [
        'method' => 'POST',
        'endpoint' => 'payments',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "invoice_id": 1,
            "amount": 1000.00,
            "paymentmode": "Bank Transfer",
            "date": "2024-01-15",
            "note": "Payment via API"
        }',
        'description' => 'Create a new payment'
    ],
    'update_payment' => [
        'method' => 'PUT',
        'endpoint' => 'payments/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "amount": 1200.00,
            "note": "Updated payment note"
        }',
        'description' => 'Update payment information'
    ],
    'delete_payment' => [
        'method' => 'DELETE',
        'endpoint' => 'payments/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Delete a payment'
    ],

    // Proposals
    'get_proposals' => [
        'method' => 'GET',
        'endpoint' => 'proposals',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Retrieve all proposals'
    ],
    'get_proposal_by_id' => [
        'method' => 'GET',
        'endpoint' => 'proposals/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get specific proposal by ID'
    ],
    'create_proposal' => [
        'method' => 'POST',
        'endpoint' => 'proposals',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "subject": "API Test Proposal",
            "client_id": 1,
            "date": "2024-01-15",
            "open_till": "2024-02-15",
            "currency": "USD",
            "subtotal": 2000.00,
            "total": 2000.00,
            "status": "Draft"
        }',
        'description' => 'Create a new proposal'
    ],
    'update_proposal' => [
        'method' => 'PUT',
        'endpoint' => 'proposals/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "status": "Sent",
            "total": 2200.00,
            "notes": "Updated proposal"
        }',
        'description' => 'Update proposal information'
    ],
    'delete_proposal' => [
        'method' => 'DELETE',
        'endpoint' => 'proposals/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Delete a proposal'
    ],

    // Subscriptions
    'get_subscriptions' => [
        'method' => 'GET',
        'endpoint' => 'subscriptions',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Retrieve all subscriptions'
    ],
    'get_subscription_by_id' => [
        'method' => 'GET',
        'endpoint' => 'subscriptions/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get specific subscription by ID'
    ],
    'create_subscription' => [
        'method' => 'POST',
        'endpoint' => 'subscriptions',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "name": "API Test Subscription",
            "client_id": 1,
            "description": "Test subscription created via API",
            "date": "2024-01-15",
            "next_billing_cycle": "2024-02-15",
            "status": "Active"
        }',
        'description' => 'Create a new subscription'
    ],
    'update_subscription' => [
        'method' => 'PUT',
        'endpoint' => 'subscriptions/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "status": "Inactive",
            "description": "Updated subscription"
        }',
        'description' => 'Update subscription information'
    ],
    'delete_subscription' => [
        'method' => 'DELETE',
        'endpoint' => 'subscriptions/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Delete a subscription'
    ],

    // Milestones
    'get_milestones' => [
        'method' => 'GET',
        'endpoint' => 'milestones',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Retrieve all milestones'
    ],
    'get_milestone_by_id' => [
        'method' => 'GET',
        'endpoint' => 'milestones/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get specific milestone by ID'
    ],
    'create_milestone' => [
        'method' => 'POST',
        'endpoint' => 'milestones',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "name": "API Test Milestone",
            "description": "Test milestone created via API",
            "project_id": 1,
            "due_date": "2024-02-15",
            "status": "Not Started"
        }',
        'description' => 'Create a new milestone'
    ],
    'update_milestone' => [
        'method' => 'PUT',
        'endpoint' => 'milestones/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "status": "In Progress",
            "description": "Updated milestone"
        }',
        'description' => 'Update milestone information'
    ],
    'search_milestones' => [
        'method' => 'GET',
        'endpoint' => 'milestones/search/example',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Search milestones by keyword'
    ],
    'delete_milestone' => [
        'method' => 'DELETE',
        'endpoint' => 'milestones/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Delete a milestone'
    ],

    // Timesheets
    'get_timesheets' => [
        'method' => 'GET',
        'endpoint' => 'timesheets',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Retrieve all timesheets'
    ],
    'get_timesheet_by_id' => [
        'method' => 'GET',
        'endpoint' => 'timesheets/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get specific timesheet by ID'
    ],
    'create_timesheet' => [
        'method' => 'POST',
        'endpoint' => 'timesheets',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "project_id": 1,
            "task_id": 1,
            "staff_id": 1,
            "date": "2024-01-15",
            "hours": 8.0,
            "note": "API test timesheet entry"
        }',
        'description' => 'Create a new timesheet entry'
    ],
    'update_timesheet' => [
        'method' => 'PUT',
        'endpoint' => 'timesheets/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "hours": 7.5,
            "note": "Updated timesheet entry"
        }',
        'description' => 'Update timesheet information'
    ],
    'delete_timesheet' => [
        'method' => 'DELETE',
        'endpoint' => 'timesheets/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Delete a timesheet entry'
    ],

    // Calendar
    'get_calendar' => [
        'method' => 'GET',
        'endpoint' => 'calendar',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Retrieve all calendar events'
    ],
    'get_calendar_event_by_id' => [
        'method' => 'GET',
        'endpoint' => 'calendar/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get specific calendar event by ID'
    ],
    'create_calendar_event' => [
        'method' => 'POST',
        'endpoint' => 'calendar',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "title": "API Test Event",
            "description": "Test event created via API",
            "start": "2024-01-15 09:00:00",
            "end": "2024-01-15 17:00:00",
            "color": "#3498db"
        }',
        'description' => 'Create a new calendar event'
    ],
    'update_calendar_event' => [
        'method' => 'PUT',
        'endpoint' => 'calendar/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '{
            "title": "Updated Event Title",
            "description": "Updated event description"
        }',
        'description' => 'Update calendar event information'
    ],
    'delete_calendar_event' => [
        'method' => 'DELETE',
        'endpoint' => 'calendar/1',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Delete a calendar event'
    ],

    // Common Data
    'get_expense_categories' => [
        'method' => 'GET',
        'endpoint' => 'common/expense_category',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get expense categories'
    ],
    'get_payment_modes' => [
        'method' => 'GET',
        'endpoint' => 'common/payment_mode',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get payment modes'
    ],
    'get_tax_data' => [
        'method' => 'GET',
        'endpoint' => 'common/tax_data',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get tax data'
    ],

    // Custom Fields
    'get_custom_fields' => [
        'method' => 'GET',
        'endpoint' => 'custom_fields/company',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get custom fields for company'
    ],
    'get_custom_fields_leads' => [
        'method' => 'GET',
        'endpoint' => 'custom_fields/leads',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get custom fields for leads'
    ],
    'get_custom_fields_customers' => [
        'method' => 'GET',
        'endpoint' => 'custom_fields/customers',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get custom fields for customers'
    ],

    // Authentication
    'login' => [
        'method' => 'POST',
        'endpoint' => 'login/auth',
        'headers' => 'Content-Type: application/json',
        'data' => '{
            "email": "admin@example.com",
            "password": "your_password"
        }',
        'description' => 'Authenticate user and get API key'
    ],
    'get_api_key' => [
        'method' => 'GET',
        'endpoint' => 'login/key',
        'headers' => 'authtoken: YOUR_API_KEY',
        'data' => '',
        'description' => 'Get API key information'
    ]
];
