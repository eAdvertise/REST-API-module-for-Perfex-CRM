<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/themes/prism.min.css" rel="stylesheet">
    <link href="<?php echo base_url('modules/api/assets/sandbox.css'); ?>" rel="stylesheet">
    <style>
        .sandbox-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        .sandbox-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        .endpoint-category {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .category-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
            color: #2c3e50;
        }
        .category-body {
            padding: 20px;
        }
        .endpoint-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .endpoint-item:hover {
            background: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .endpoint-item.active {
            background: #e3f2fd;
            border-color: #2196f3;
        }
        .method-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            margin-right: 10px;
        }
        .method-get { background: #28a745; color: white; }
        .method-post { background: #007bff; color: white; }
        .method-put { background: #ffc107; color: #212529; }
        .method-delete { background: #dc3545; color: white; }
        .method-patch { background: #6f42c1; color: white; }
        .request-form {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 20px;
        }
        .response-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 20px;
        }
        .sample-request {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .sample-request:hover {
            background: #e9ecef;
            transform: translateY(-1px);
        }
        .code-block {
            background: #2d3748;
            color: #e2e8f0;
            border-radius: 6px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            margin: 10px 0;
        }
        .environment-switcher {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .history-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .history-item:hover {
            background: #e9ecef;
        }
        .copy-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .response-container:hover .copy-btn {
            opacity: 1;
        }
        .tabs-container {
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
        }
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
        }
        .nav-tabs .nav-link.active {
            color: #495057;
            background-color: transparent;
            border-bottom: 2px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="sandbox-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-flask"></i> API Sandbox Playground</h1>
                    <p class="lead mb-0">Test API requests safely without affecting production data</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="<?php echo admin_url('api/api_management'); ?>" class="btn btn-outline-light me-2" target="_blank">
                        <i class="fas fa-key"></i> Get API Token
                    </a>
                    <a href="<?php echo base_url('api/playground/documentation'); ?>" class="btn btn-outline-light me-2">
                        <i class="fas fa-book"></i> Documentation
                    </a>
                    <a href="<?php echo base_url('api/playground/swagger'); ?>" class="btn btn-outline-light">
                        <i class="fas fa-code"></i> Swagger UI
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="sandbox-container">
        <!-- Environment Switcher -->
        <div class="environment-switcher">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="mb-0"><i class="fas fa-shield-alt"></i> Environment: <span class="badge bg-success">Sandbox</span></h6>
                    <small class="text-muted">All requests are made to test environment - no production data affected</small>
                </div>
                <div class="col-md-6 text-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="environmentSwitch">
                        <label class="form-check-label" for="environmentSwitch">
                            Enable Production Mode (Use with caution!)
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Authentication Token -->
        <div class="endpoint-category">
            <div class="category-header">
                <i class="fas fa-key"></i> Authentication Token
            </div>
            <div class="category-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="auth-token" class="form-label">API Token</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="auth-token" placeholder="Enter your API token here">
                                <button class="btn btn-outline-secondary" type="button" id="toggle-token-visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-primary" type="button" id="test-token">
                                    <i class="fas fa-check"></i> Test
                                </button>
                            </div>
                            <small class="form-text text-muted">
                                Get your API token from <a href="<?php echo admin_url('api/api_management'); ?>" target="_blank">API Management</a>. 
                                The token will be used in the <code>authtoken</code> header.
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Token Status</label>
                            <div id="token-status" class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle"></i> No token provided
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-success" id="save-token">
                                <i class="fas fa-save"></i> Save Token
                            </button>
                            <button class="btn btn-sm btn-outline-danger" id="clear-token">
                                <i class="fas fa-trash"></i> Clear Token
                            </button>
                            <button class="btn btn-sm btn-outline-info" id="load-saved-token">
                                <i class="fas fa-download"></i> Load Saved Token
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Sidebar - Endpoints & Samples -->
            <div class="col-md-4">
                <!-- Quick Samples -->
                <div class="endpoint-category">
                    <div class="category-header">
                        <i class="fas fa-rocket"></i> Quick Start Samples
                    </div>
                    <div class="category-body">
                        <div class="sample-request" data-sample="create_lead">
                            <h6><span class="method-badge method-post">POST</span> Create Lead</h6>
                            <p class="mb-0 text-muted">Create a new lead with sample data</p>
                            <button class="btn btn-sm btn-outline-primary load-sample-btn mt-2" data-sample="create_lead">
                                <i class="fas fa-play"></i> Load Sample
                            </button>
                        </div>
                        
                        <div class="sample-request" data-sample="get_projects">
                            <h6><span class="method-badge method-get">GET</span> Get Projects</h6>
                            <p class="mb-0 text-muted">Retrieve all projects</p>
                            <button class="btn btn-sm btn-outline-primary load-sample-btn mt-2" data-sample="get_projects">
                                <i class="fas fa-play"></i> Load Sample
                            </button>
                        </div>
                    </div>
                </div>

                <!-- API Endpoints -->
                <div class="endpoint-category">
                    <div class="category-header">
                        <i class="fas fa-list"></i> API Endpoints
                    </div>
                    <div class="category-body">
                        <!-- Leads -->
                        <h6 class="text-muted mb-2 mt-3">Leads</h6>
                        <div class="endpoint-item endpoint-link" data-endpoint="GET /leads">
                            <span class="method-badge method-get">GET</span>
                            <code>/leads</code>
                            <small class="d-block text-muted">Get all leads</small>
                        </div>
                        <div class="endpoint-item endpoint-link" data-endpoint="POST /leads">
                            <span class="method-badge method-post">POST</span>
                            <code>/leads</code>
                            <small class="d-block text-muted">Create new lead</small>
                        </div>

                        <!-- Projects -->
                        <h6 class="text-muted mb-2 mt-3">Projects</h6>
                        <div class="endpoint-item endpoint-link" data-endpoint="GET /projects">
                            <span class="method-badge method-get">GET</span>
                            <code>/projects</code>
                            <small class="d-block text-muted">Get all projects</small>
                        </div>
                        <div class="endpoint-item endpoint-link" data-endpoint="POST /projects">
                            <span class="method-badge method-post">POST</span>
                            <code>/projects</code>
                            <small class="d-block text-muted">Create new project</small>
                        </div>

                        <!-- Tasks -->
                        <h6 class="text-muted mb-2 mt-3">Tasks</h6>
                        <div class="endpoint-item endpoint-link" data-endpoint="GET /tasks">
                            <span class="method-badge method-get">GET</span>
                            <code>/tasks</code>
                            <small class="d-block text-muted">Get all tasks</small>
                        </div>
                        <div class="endpoint-item endpoint-link" data-endpoint="POST /tasks">
                            <span class="method-badge method-post">POST</span>
                            <code>/tasks</code>
                            <small class="d-block text-muted">Create new task</small>
                        </div>

                        <!-- Tickets -->
                        <h6 class="text-muted mb-2 mt-3">Tickets</h6>
                        <div class="endpoint-item endpoint-link" data-endpoint="GET /tickets">
                            <span class="method-badge method-get">GET</span>
                            <code>/tickets</code>
                            <small class="d-block text-muted">Get all tickets</small>
                        </div>
                        <div class="endpoint-item endpoint-link" data-endpoint="POST /tickets">
                            <span class="method-badge method-post">POST</span>
                            <code>/tickets</code>
                            <small class="d-block text-muted">Create new ticket</small>
                        </div>

                        <!-- Invoices -->
                        <h6 class="text-muted mb-2 mt-3">Invoices</h6>
                        <div class="endpoint-item endpoint-link" data-endpoint="GET /invoices">
                            <span class="method-badge method-get">GET</span>
                            <code>/invoices</code>
                            <small class="d-block text-muted">Get all invoices</small>
                        </div>
                        <div class="endpoint-item endpoint-link" data-endpoint="POST /invoices">
                            <span class="method-badge method-post">POST</span>
                            <code>/invoices</code>
                            <small class="d-block text-muted">Create new invoice</small>
                        </div>

                        <!-- Estimates -->
                        <h6 class="text-muted mb-2 mt-3">Estimates</h6>
                        <div class="endpoint-item endpoint-link" data-endpoint="GET /estimates">
                            <span class="method-badge method-get">GET</span>
                            <code>/estimates</code>
                            <small class="d-block text-muted">Get all estimates</small>
                        </div>
                        <div class="endpoint-item endpoint-link" data-endpoint="POST /estimates">
                            <span class="method-badge method-post">POST</span>
                            <code>/estimates</code>
                            <small class="d-block text-muted">Create new estimate</small>
                        </div>

                        <!-- Contracts -->
                        <h6 class="text-muted mb-2 mt-3">Contracts</h6>
                        <div class="endpoint-item endpoint-link" data-endpoint="GET /contracts">
                            <span class="method-badge method-get">GET</span>
                            <code>/contracts</code>
                            <small class="d-block text-muted">Get all contracts</small>
                        </div>
                        <div class="endpoint-item endpoint-link" data-endpoint="POST /contracts">
                            <span class="method-badge method-post">POST</span>
                            <code>/contracts</code>
                            <small class="d-block text-muted">Create new contract</small>
                        </div>

                        <!-- Credit Notes -->
                        <h6 class="text-muted mb-2 mt-3">Credit Notes</h6>
                        <div class="endpoint-item endpoint-link" data-endpoint="GET /credit_notes">
                            <span class="method-badge method-get">GET</span>
                            <code>/credit_notes</code>
                            <small class="d-block text-muted">Get all credit notes</small>
                        </div>
                        <div class="endpoint-item endpoint-link" data-endpoint="POST /credit_notes">
                            <span class="method-badge method-post">POST</span>
                            <code>/credit_notes</code>
                            <small class="d-block text-muted">Create new credit note</small>
                        </div>

                        <!-- Expenses -->
                        <h6 class="text-muted mb-2 mt-3">Expenses</h6>
                        <div class="endpoint-item endpoint-link" data-endpoint="GET /expenses">
                            <span class="method-badge method-get">GET</span>
                            <code>/expenses</code>
                            <small class="d-block text-muted">Get all expenses</small>
                        </div>
                        <div class="endpoint-item endpoint-link" data-endpoint="POST /expenses">
                            <span class="method-badge method-post">POST</span>
                            <code>/expenses</code>
                            <small class="d-block text-muted">Create new expense</small>
                        </div>

                        <!-- Items -->
                        <h6 class="text-muted mb-2 mt-3">Items</h6>
                        <div class="endpoint-item endpoint-link" data-endpoint="GET /items">
                            <span class="method-badge method-get">GET</span>
                            <code>/items</code>
                            <small class="d-block text-muted">Get all items</small>
                        </div>

                        <!-- Contacts -->
                        <h6 class="text-muted mb-2 mt-3">Contacts</h6>
                        <div class="endpoint-item endpoint-link" data-endpoint="GET /contacts">
                            <span class="method-badge method-get">GET</span>
                            <code>/contacts</code>
                            <small class="d-block text-muted">Get all contacts</small>
                        </div>
                        <div class="endpoint-item endpoint-link" data-endpoint="POST /contacts">
                            <span class="method-badge method-post">POST</span>
                            <code>/contacts</code>
                            <small class="d-block text-muted">Create new contact</small>
                        </div>

                        <!-- Staff -->
                        <h6 class="text-muted mb-2 mt-3">Staff</h6>
                        <div class="endpoint-item endpoint-link" data-endpoint="GET /staff">
                            <span class="method-badge method-get">GET</span>
                            <code>/staff</code>
                            <small class="d-block text-muted">Get all staff members</small>
                        </div>

                        <!-- Payments -->
                        <h6 class="text-muted mb-2 mt-3">Payments</h6>
                        <div class="endpoint-item endpoint-link" data-endpoint="GET /payments">
                            <span class="method-badge method-get">GET</span>
                            <code>/payments</code>
                            <small class="d-block text-muted">Get all payments</small>
                        </div>
                        <div class="endpoint-item endpoint-link" data-endpoint="POST /payments">
                            <span class="method-badge method-post">POST</span>
                            <code>/payments</code>
                            <small class="d-block text-muted">Create new payment</small>
                        </div>

                        <!-- Proposals -->
                        <h6 class="text-muted mb-2 mt-3">Proposals</h6>
                        <div class="endpoint-item endpoint-link" data-endpoint="GET /proposals">
                            <span class="method-badge method-get">GET</span>
                            <code>/proposals</code>
                            <small class="d-block text-muted">Get all proposals</small>
                        </div>
                        <div class="endpoint-item endpoint-link" data-endpoint="POST /proposals">
                            <span class="method-badge method-post">POST</span>
                            <code>/proposals</code>
                            <small class="d-block text-muted">Create new proposal</small>
                        </div>

                        <!-- Subscriptions -->
                        <h6 class="text-muted mb-2 mt-3">Subscriptions</h6>
                        <div class="endpoint-item endpoint-link" data-endpoint="GET /subscriptions">
                            <span class="method-badge method-get">GET</span>
                            <code>/subscriptions</code>
                            <small class="d-block text-muted">Get all subscriptions</small>
                        </div>
                        <div class="endpoint-item endpoint-link" data-endpoint="POST /subscriptions">
                            <span class="method-badge method-post">POST</span>
                            <code>/subscriptions</code>
                            <small class="d-block text-muted">Create new subscription</small>
                        </div>

                        <!-- Milestones -->
                        <h6 class="text-muted mb-2 mt-3">Milestones</h6>
                        <div class="endpoint-item endpoint-link" data-endpoint="GET /milestones">
                            <span class="method-badge method-get">GET</span>
                            <code>/milestones</code>
                            <small class="d-block text-muted">Get all milestones</small>
                        </div>
                        <div class="endpoint-item endpoint-link" data-endpoint="POST /milestones">
                            <span class="method-badge method-post">POST</span>
                            <code>/milestones</code>
                            <small class="d-block text-muted">Create new milestone</small>
                        </div>

                        <!-- Timesheets -->
                        <h6 class="text-muted mb-2 mt-3">Timesheets</h6>
                        <div class="endpoint-item endpoint-link" data-endpoint="GET /timesheets">
                            <span class="method-badge method-get">GET</span>
                            <code>/timesheets</code>
                            <small class="d-block text-muted">Get all timesheets</small>
                        </div>
                        <div class="endpoint-item endpoint-link" data-endpoint="POST /timesheets">
                            <span class="method-badge method-post">POST</span>
                            <code>/timesheets</code>
                            <small class="d-block text-muted">Create new timesheet entry</small>
                        </div>

                        <!-- Calendar -->
                        <h6 class="text-muted mb-2 mt-3">Calendar</h6>
                        <div class="endpoint-item endpoint-link" data-endpoint="GET /calendar">
                            <span class="method-badge method-get">GET</span>
                            <code>/calendar</code>
                            <small class="d-block text-muted">Get all calendar events</small>
                        </div>
                        <div class="endpoint-item endpoint-link" data-endpoint="POST /calendar">
                            <span class="method-badge method-post">POST</span>
                            <code>/calendar</code>
                            <small class="d-block text-muted">Create new calendar event</small>
                        </div>

                        <!-- Common Data -->
                        <h6 class="text-muted mb-2 mt-3">Common Data</h6>
                        <div class="endpoint-item endpoint-link" data-endpoint="GET /common/expense_category">
                            <span class="method-badge method-get">GET</span>
                            <code>/common/expense_category</code>
                            <small class="d-block text-muted">Get expense categories</small>
                        </div>
                        <div class="endpoint-item endpoint-link" data-endpoint="GET /common/payment_mode">
                            <span class="method-badge method-get">GET</span>
                            <code>/common/payment_mode</code>
                            <small class="d-block text-muted">Get payment modes</small>
                        </div>

                        <!-- Custom Fields -->
                        <h6 class="text-muted mb-2 mt-3">Custom Fields</h6>
                        <div class="endpoint-item endpoint-link" data-endpoint="GET /custom_fields/company">
                            <span class="method-badge method-get">GET</span>
                            <code>/custom_fields/company</code>
                            <small class="d-block text-muted">Get company custom fields</small>
                        </div>

                        <!-- Authentication -->
                        <h6 class="text-muted mb-2 mt-3">Authentication</h6>
                        <div class="endpoint-item endpoint-link" data-endpoint="POST /login/auth">
                            <span class="method-badge method-post">POST</span>
                            <code>/login/auth</code>
                            <small class="d-block text-muted">Authenticate user</small>
                        </div>
                        <div class="endpoint-item endpoint-link" data-endpoint="GET /login/key">
                            <span class="method-badge method-get">GET</span>
                            <code>/login/key</code>
                            <small class="d-block text-muted">Get API key info</small>
                        </div>
                    </div>
                </div>

                <!-- Request History -->
                <div class="endpoint-category">
                    <div class="category-header">
                        <i class="fas fa-history"></i> Request History
                        <button class="btn btn-sm btn-outline-secondary float-end" id="clear-history">
                            <i class="fas fa-trash"></i> Clear
                        </button>
                    </div>
                    <div class="category-body">
                        <div id="request-history">
                            <p class="text-muted text-center">No requests yet</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-md-8">
                <!-- Request Form -->
                <div class="request-form">
                    <div class="tabs-container">
                        <ul class="nav nav-tabs" id="requestTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="form-tab" data-bs-toggle="tab" data-bs-target="#form-pane" type="button" role="tab">
                                    <i class="fas fa-edit"></i> Request Form
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="curl-tab" data-bs-toggle="tab" data-bs-target="#curl-pane" type="button" role="tab">
                                    <i class="fas fa-terminal"></i> cURL Command
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content" id="requestTabContent">
                        <!-- Form Tab -->
                        <div class="tab-pane fade show active" id="form-pane" role="tabpanel">
                            <form id="api-request-form">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="request-method" class="form-label">Method</label>
                                            <select class="form-select" id="request-method" required>
                                                <option value="GET">GET</option>
                                                <option value="POST">POST</option>
                                                <option value="PUT">PUT</option>
                                                <option value="PATCH">PATCH</option>
                                                <option value="DELETE">DELETE</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="mb-3">
                                            <label for="request-endpoint" class="form-label">Endpoint</label>
                                            <input type="text" class="form-control" id="request-endpoint" placeholder="/leads" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="request-headers" class="form-label">Headers</label>
                                    <textarea class="form-control" id="request-headers" rows="3" placeholder="authtoken: YOUR_API_KEY&#10;Content-Type: application/json">authtoken: YOUR_API_KEY
Content-Type: application/json</textarea>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> The authtoken header will be automatically updated when you enter your API token above.
                                    </small>
                                </div>

                                <div class="mb-3" id="data-group">
                                    <label for="request-data" class="form-label">Request Body (JSON)</label>
                                    <textarea class="form-control" id="request-data" rows="8" placeholder='{"key": "value"}'></textarea>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-play"></i> Execute Request
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="clear-form">
                                        <i class="fas fa-eraser"></i> Clear
                                    </button>
                                    <button type="button" class="btn btn-outline-info" id="format-json">
                                        <i class="fas fa-code"></i> Format JSON
                                    </button>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="fas fa-download"></i> Export
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" id="export-json">Export as JSON</a></li>
                                            <li><a class="dropdown-item" href="#" id="export-curl">Export as cURL</a></li>
                                        </ul>
                                    </div>
                                    <input type="file" class="d-none" id="import-file" accept=".json">
                                    <button type="button" class="btn btn-outline-warning" id="import-request">
                                        <i class="fas fa-upload"></i> Import
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- cURL Tab -->
                        <div class="tab-pane fade" id="curl-pane" role="tabpanel">
                            <div class="mb-3">
                                <label for="curl-command" class="form-label">Generated cURL Command</label>
                                <textarea class="form-control" id="curl-command" rows="10" readonly></textarea>
                            </div>
                            <button type="button" class="btn btn-outline-primary" id="copy-curl">
                                <i class="fas fa-copy"></i> Copy cURL
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Response Area -->
                <div class="response-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5><i class="fas fa-reply"></i> Response</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary" id="copy-response">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                            <button class="btn btn-sm btn-outline-info" id="format-response">
                                <i class="fas fa-code"></i> Format
                            </button>
                        </div>
                    </div>
                    <div id="response-content">
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-arrow-up fa-2x mb-3"></i>
                            <p>Execute a request to see the response here</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/plugins/autoloader/prism-autoloader.min.js"></script>
    <script src="<?php echo base_url('modules/api/assets/sandbox.js'); ?>"></script>
    <script>
        // Configuration
        const config = {
            baseUrl: '<?php echo base_url(); ?>',
            apiBaseUrl: '<?php echo base_url('api/'); ?>',
            playgroundUrl: '<?php echo base_url('api/playground/'); ?>',
            adminUrl: '<?php echo base_url(); ?>'
        };

        // Make config available globally
        window.sandboxConfig = config;

        // Load sample requests from server
        function loadSampleRequests() {
            $.get(config.playgroundUrl + 'get_samples')
                .done(function(samples) {
                    window.sampleRequests = samples;
                })
                .fail(function() {
                    console.error('Failed to load sample requests');
                });
        }

        // Initialize sandbox when document is ready
        $(document).ready(function() {
            initSandbox();
            loadSampleRequests();
            initTokenManagement();
        });

        // Enhanced form submission
        $('#api-request-form').on('submit', function(e) {
            e.preventDefault();
            executeRequest();
        });

        // Execute API request
        function executeRequest() {
            const formData = collectFormData();
            
            if (!validateFormData(formData)) {
                return;
            }
            
            showLoading();
            updateCurlCommand(formData);
            
            // Add to history
            addToHistory(formData);
            
            $.ajax({
                url: config.playgroundUrl + 'execute_request',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    showResponse(response, true);
                },
                error: function(xhr) {
                    showResponse(xhr.responseJSON || {error: xhr.responseText, status_code: xhr.status}, false);
                }
            });
        }

        // Collect form data
        function collectFormData() {
            return {
                method: $('#request-method').val(),
                endpoint: $('#request-endpoint').val(),
                headers: $('#request-headers').val(),
                data: $('#request-data').val()
            };
        }

        // Validate form data
        function validateFormData(formData) {
            try {
                if (formData.headers && formData.headers.trim()) {
                    // Validate header format (key: value pairs)
                    const headerLines = formData.headers.split('\n');
                    for (let line of headerLines) {
                        line = line.trim();
                        if (line && !line.includes(':')) {
                            showAlert('Invalid header format. Use "Key: Value" format.', 'error');
                            return false;
                        }
                    }
                }
                if (formData.data && formData.data.trim()) {
                    JSON.parse(formData.data);
                }
            } catch (error) {
                showAlert('Invalid JSON format in request body', 'error');
                return false;
            }
            
            if (!formData.endpoint) {
                showAlert('Endpoint is required', 'error');
                return false;
            }
            
            return true;
        }

        // Show loading state
        function showLoading() {
            const loadingHtml = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Executing request...</p>
                </div>
            `;
            $('#response-content').html(loadingHtml);
        }

        // Show response
        function showResponse(response, isSuccess) {
            let html = '';
            
            if (isSuccess) {
                html += `
                    <div class="alert alert-success">
                        <h6><i class="fas fa-check-circle"></i> Success <span class="badge bg-success">${response.http_code || 200}</span></h6>
                    </div>
                `;
                
                if (response.response) {
                    try {
                        const jsonResponse = JSON.parse(response.response);
                        html += `<pre><code class="language-json">${JSON.stringify(jsonResponse, null, 2)}</code></pre>`;
                    } catch (e) {
                        html += `<pre><code>${response.response}</code></pre>`;
                    }
                }
            } else {
                html += `
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-exclamation-circle"></i> Error <span class="badge bg-danger">${response.http_code || 500}</span></h6>
                    </div>
                `;
                html += `<pre><code>${response.error || response.message || 'Unknown error occurred'}</code></pre>`;
            }
            
            $('#response-content').html(html);
            
            // Highlight syntax
            Prism.highlightAll();
            
            // Scroll to response
            $('html, body').animate({
                scrollTop: $('#response-content').offset().top - 100
            }, 500);
        }

        // Update cURL command
        function updateCurlCommand(formData) {
            let curlCommand = `curl -X ${formData.method} "${config.apiBaseUrl}${formData.endpoint}"`;
            
            // Add headers
            if (formData.headers) {
                const headerLines = formData.headers.split('\n');
                headerLines.forEach(line => {
                    line = line.trim();
                    if (line && line.includes(':')) {
                        curlCommand += ` \\\n  -H "${line}"`;
                    }
                });
            }
            
            // Add data
            if (formData.data && ['POST', 'PUT', 'PATCH'].includes(formData.method)) {
                curlCommand += ` \\\n  -d '${formData.data}'`;
            }
            
            $('#curl-command').val(curlCommand);
        }

        // Add to history
        function addToHistory(formData) {
            const historyItem = {
                method: formData.method,
                endpoint: formData.endpoint,
                timestamp: new Date().toLocaleString()
            };
            
            let history = JSON.parse(localStorage.getItem('apiHistory') || '[]');
            history.unshift(historyItem);
            history = history.slice(0, 10); // Keep only last 10 requests
            localStorage.setItem('apiHistory', JSON.stringify(history));
            
            updateHistoryDisplay();
        }

        // Update history display
        function updateHistoryDisplay() {
            const history = JSON.parse(localStorage.getItem('apiHistory') || '[]');
            const historyContainer = $('#request-history');
            
            if (history.length === 0) {
                historyContainer.html('<p class="text-muted text-center">No requests yet</p>');
                return;
            }
            
            let html = '';
            history.forEach((item, index) => {
                html += `
                    <div class="history-item" data-index="${index}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="method-badge method-${item.method.toLowerCase()}">${item.method}</span>
                                <code>${item.endpoint}</code>
                            </div>
                            <small class="text-muted">${item.timestamp}</small>
                        </div>
                    </div>
                `;
            });
            
            historyContainer.html(html);
            
            // Add click handlers
            $('.history-item').on('click', function() {
                const index = $(this).data('index');
                const item = history[index];
                $('#request-method').val(item.method);
                $('#request-endpoint').val(item.endpoint);
                toggleDataGroup(item.method);
            });
        }

        // Clear history
        $('#clear-history').on('click', function() {
            localStorage.removeItem('apiHistory');
            updateHistoryDisplay();
        });

        // Copy cURL command
        $('#copy-curl').on('click', function() {
            const curlCommand = $('#curl-command').val();
            navigator.clipboard.writeText(curlCommand).then(function() {
                showAlert('cURL command copied to clipboard!', 'success');
            });
        });

        // Copy response
        $('#copy-response').on('click', function() {
            const responseText = $('#response-content pre code').text();
            navigator.clipboard.writeText(responseText).then(function() {
                showAlert('Response copied to clipboard!', 'success');
            });
        });

        // Format JSON
        $('#format-json').on('click', function() {
            const data = $('#request-data').val();
            try {
                const formatted = JSON.stringify(JSON.parse(data), null, 2);
                $('#request-data').val(formatted);
            } catch (e) {
                showAlert('Invalid JSON format', 'error');
            }
        });

        // Format response
        $('#format-response').on('click', function() {
            const responseText = $('#response-content pre code').text();
            try {
                const formatted = JSON.stringify(JSON.parse(responseText), null, 2);
                $('#response-content pre code').text(formatted);
                Prism.highlightAll();
            } catch (e) {
                showAlert('Response is not valid JSON', 'error');
            }
        });

        // Export functionality
        $('#export-json').on('click', function() {
            const formData = collectFormData();
            const exportData = {
                method: formData.method,
                endpoint: formData.endpoint,
                headers: formData.headers ? parseHeaders(formData.headers) : {},
                data: formData.data ? JSON.parse(formData.data) : null,
                timestamp: new Date().toISOString()
            };
            
            const dataStr = JSON.stringify(exportData, null, 2);
            const dataBlob = new Blob([dataStr], {type: 'application/json'});
            const url = URL.createObjectURL(dataBlob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `api-request-${Date.now()}.json`;
            link.click();
            URL.revokeObjectURL(url);
        });

        $('#export-curl').on('click', function() {
            const curlCommand = $('#curl-command').val();
            const dataBlob = new Blob([curlCommand], {type: 'text/plain'});
            const url = URL.createObjectURL(dataBlob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `curl-command-${Date.now()}.txt`;
            link.click();
            URL.revokeObjectURL(url);
        });

        // Import functionality
        $('#import-request').on('click', function() {
            $('#import-file').click();
        });

        $('#import-file').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        const data = JSON.parse(e.target.result);
                        $('#request-method').val(data.method);
                        $('#request-endpoint').val(data.endpoint);
                        $('#request-headers').val(formatHeaders(data.headers));
                        if (data.data) {
                            $('#request-data').val(JSON.stringify(data.data, null, 2));
                        }
                        toggleDataGroup(data.method);
                        showAlert('Request imported successfully!', 'success');
                    } catch (error) {
                        showAlert('Invalid JSON file', 'error');
                    }
                };
                reader.readAsText(file);
            }
        });

        // Helper functions
        function parseHeaders(headersString) {
            const headers = {};
            const lines = headersString.split('\n');
            lines.forEach(line => {
                line = line.trim();
                if (line && line.includes(':')) {
                    const [key, value] = line.split(':', 2);
                    headers[key.trim()] = value.trim();
                }
            });
            return headers;
        }

        function formatHeaders(headers) {
            let result = '';
            for (const [key, value] of Object.entries(headers)) {
                result += `${key}: ${value}\n`;
            }
            return result.trim();
        }

        // Initialize history display
        updateHistoryDisplay();

        // Show alert function
        function showAlert(message, type) {
            const alertClass = type === 'error' ? 'alert-danger' : 
                              type === 'success' ? 'alert-success' : 
                              type === 'warning' ? 'alert-warning' : 'alert-info';
            
            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Remove existing alerts
            $('.alert').remove();
            
            // Add new alert at the top of the sandbox container
            $('.sandbox-container').prepend(alertHtml);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
        }

        // Token Management Functions
        function initTokenManagement() {
            // Load saved token on page load
            loadSavedToken();
            
            // Update headers when token changes
            $('#auth-token').on('input', function() {
                updateHeadersWithToken();
                updateTokenStatus();
            });
            
            // Toggle token visibility
            $('#toggle-token-visibility').on('click', function() {
                const tokenInput = $('#auth-token');
                const icon = $(this).find('i');
                
                if (tokenInput.attr('type') === 'password') {
                    tokenInput.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    tokenInput.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
            
            // Test token
            $('#test-token').on('click', function() {
                testToken();
            });
            
            // Save token
            $('#save-token').on('click', function() {
                saveToken();
            });
            
            // Clear token
            $('#clear-token').on('click', function() {
                clearToken();
            });
            
            // Load saved token
            $('#load-saved-token').on('click', function() {
                loadSavedToken();
            });
        }

        function updateHeadersWithToken() {
            const token = $('#auth-token').val();
            let headers = $('#request-headers').val();
            
            if (token) {
                // Update or add authtoken header
                const lines = headers.split('\n');
                let updated = false;
                
                for (let i = 0; i < lines.length; i++) {
                    if (lines[i].toLowerCase().startsWith('authtoken:')) {
                        lines[i] = `authtoken: ${token}`;
                        updated = true;
                        break;
                    }
                }
                
                if (!updated) {
                    lines.unshift(`authtoken: ${token}`);
                }
                
                $('#request-headers').val(lines.join('\n'));
            }
        }

        function updateTokenStatus() {
            const token = $('#auth-token').val();
            const statusDiv = $('#token-status');
            
            if (token) {
                if (token.length < 10) {
                    statusDiv.removeClass('alert-success alert-warning').addClass('alert-warning');
                    statusDiv.html('<i class="fas fa-exclamation-triangle"></i> Token seems too short');
                } else {
                    statusDiv.removeClass('alert-warning alert-danger').addClass('alert-success');
                    statusDiv.html('<i class="fas fa-check-circle"></i> Token ready');
                }
            } else {
                statusDiv.removeClass('alert-success alert-danger').addClass('alert-warning');
                statusDiv.html('<i class="fas fa-exclamation-triangle"></i> No token provided');
            }
        }

        function testToken() {
            const token = $('#auth-token').val();
            
            if (!token) {
                showAlert('Please enter a token first', 'error');
                return;
            }
            
            // Test with a simple API call
            $.ajax({
                url: config.apiBaseUrl + 'login/key',
                type: 'GET',
                headers: {
                    'authtoken': token,
                    'Content-Type': 'application/json'
                },
                success: function(response) {
                    showAlert('Token is valid!', 'success');
                    updateTokenStatus();
                },
                error: function(xhr) {
                    if (xhr.status === 401) {
                        showAlert('Invalid token - please check your API key', 'error');
                    } else {
                        showAlert('Token test failed: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
                    }
                }
            });
        }

        function saveToken() {
            const token = $('#auth-token').val();
            
            if (!token) {
                showAlert('Please enter a token first', 'error');
                return;
            }
            
            localStorage.setItem('api_sandbox_token', token);
            showAlert('Token saved successfully!', 'success');
        }

        function loadSavedToken() {
            const savedToken = localStorage.getItem('api_sandbox_token');
            
            if (savedToken) {
                $('#auth-token').val(savedToken);
                updateHeadersWithToken();
                updateTokenStatus();
                showAlert('Saved token loaded', 'success');
            }
        }

        function clearToken() {
            $('#auth-token').val('');
            $('#request-headers').val('authtoken: YOUR_API_KEY\nContent-Type: application/json');
            updateTokenStatus();
            localStorage.removeItem('api_sandbox_token');
            showAlert('Token cleared', 'info');
        }

        // Update sample requests to use actual token
        function loadSampleRequests() {
            $.get(config.playgroundUrl + 'get_samples')
                .done(function(samples) {
                    window.sampleRequests = samples;
                    updateSampleRequestsWithToken();
                })
                .fail(function() {
                    console.error('Failed to load sample requests');
                });
        }

        function updateSampleRequestsWithToken() {
            const token = $('#auth-token').val();
            
            if (token && window.sampleRequests) {
                // Update all sample requests to use the actual token
                for (let key in window.sampleRequests) {
                    if (window.sampleRequests[key].headers) {
                        window.sampleRequests[key].headers = window.sampleRequests[key].headers.replace('YOUR_API_KEY', token);
                    }
                }
            }
        }

        // Override the load sample function to use actual token
        $(document).on('click', '.load-sample-btn', function() {
            const sampleKey = $(this).data('sample');
            const token = $('#auth-token').val();
            
            if (!token) {
                showAlert('Please enter your API token first', 'warning');
                return;
            }
            
            if (window.sampleRequests && window.sampleRequests[sampleKey]) {
                const sample = window.sampleRequests[sampleKey];
                
                // Update sample with actual token
                const updatedSample = { ...sample };
                if (updatedSample.headers) {
                    updatedSample.headers = updatedSample.headers.replace('YOUR_API_KEY', token);
                }
                
                $('#request-method').val(updatedSample.method);
                $('#request-endpoint').val(updatedSample.endpoint);
                $('#request-headers').val(updatedSample.headers);
                $('#request-data').val(updatedSample.data);
                
                toggleDataGroup(updatedSample.method);
                showAlert('Sample loaded with your token', 'success');
            }
        });
    </script>
</body>
</html>
