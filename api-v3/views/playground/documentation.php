<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/themes/prism.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .documentation-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
            border-radius: 10px;
        }
        .content-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .section-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
            border-radius: 10px 10px 0 0;
            font-weight: 600;
        }
        .section-body {
            padding: 20px;
        }
        .endpoint-item {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .method-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .method-get { background: #28a745; color: white; }
        .method-post { background: #007bff; color: white; }
        .method-put { background: #ffc107; color: black; }
        .method-delete { background: #dc3545; color: white; }
        .code-block {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            overflow-x: auto;
        }
        .nav-link {
            color: #495057;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            background-color: #e9ecef;
            color: #212529;
        }
        .nav-link.active {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <div class="documentation-container">
        <!-- Header Section -->
        <div class="header-section text-center">
            <h1><i class="fas fa-book"></i> Perfex CRM API Documentation</h1>
            <p class="lead">Complete guide to using the Perfex CRM REST API</p>
        </div>

        <!-- Navigation -->
        <div class="content-section">
            <div class="section-header">
                <h4><i class="fas fa-list"></i> Quick Navigation</h4>
            </div>
            <div class="section-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="#authentication" class="nav-link">Authentication</a>
                    </div>
                    <div class="col-md-3">
                        <a href="#endpoints" class="nav-link">API Endpoints</a>
                    </div>
                    <div class="col-md-3">
                        <a href="#examples" class="nav-link">Examples</a>
                    </div>
                    <div class="col-md-3">
                        <a href="#playground" class="nav-link">API Playground</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Authentication Section -->
        <div class="content-section" id="authentication">
            <div class="section-header">
                <h4><i class="fas fa-key"></i> Authentication</h4>
            </div>
            <div class="section-body">
                <p>The Perfex CRM API uses JWT (JSON Web Token) authentication. You need to include your API key in the Authorization header of each request.</p>
                
                <h5>Getting Your API Key</h5>
                <ol>
                    <li>Log in to your Perfex CRM admin panel</li>
                    <li>Go to <strong>API</strong> → <strong>API Keys</strong></li>
                    <li>Create a new API key or use an existing one</li>
                    <li>Copy the API key for use in your requests</li>
                </ol>

                <h5>Using Your API Key</h5>
                <p>Include your API key in the Authorization header:</p>
                <div class="code-block">
Authorization: Bearer YOUR_API_KEY_HERE
                </div>

                <h5>Example Request</h5>
                <div class="code-block">
curl -X GET "https://yourdomain.com/api/clients" \
  -H "Authorization: Bearer YOUR_API_KEY_HERE" \
  -H "Content-Type: application/json"
                </div>
            </div>
        </div>

        <!-- API Endpoints Section -->
        <div class="content-section" id="endpoints">
            <div class="section-header">
                <h4><i class="fas fa-code"></i> API Endpoints</h4>
            </div>
            <div class="section-body">
                <p>All API endpoints follow the pattern: <code>https://yourdomain.com/api/{resource}</code></p>

                <h5>Available Resources</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="endpoint-item">
                            <span class="method-badge method-get">GET</span>
                            <strong>/api/clients</strong>
                            <p class="mb-0">Retrieve all clients</p>
                        </div>
                        <div class="endpoint-item">
                            <span class="method-badge method-post">POST</span>
                            <strong>/api/clients</strong>
                            <p class="mb-0">Create a new client</p>
                        </div>
                        <div class="endpoint-item">
                            <span class="method-badge method-get">GET</span>
                            <strong>/api/projects</strong>
                            <p class="mb-0">Retrieve all projects</p>
                        </div>
                        <div class="endpoint-item">
                            <span class="method-badge method-post">POST</span>
                            <strong>/api/projects</strong>
                            <p class="mb-0">Create a new project</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="endpoint-item">
                            <span class="method-badge method-get">GET</span>
                            <strong>/api/leads</strong>
                            <p class="mb-0">Retrieve all leads</p>
                        </div>
                        <div class="endpoint-item">
                            <span class="method-badge method-post">POST</span>
                            <strong>/api/leads</strong>
                            <p class="mb-0">Create a new lead</p>
                        </div>
                        <div class="endpoint-item">
                            <span class="method-badge method-get">GET</span>
                            <strong>/api/tickets</strong>
                            <p class="mb-0">Retrieve all tickets</p>
                        </div>
                        <div class="endpoint-item">
                            <span class="method-badge method-post">POST</span>
                            <strong>/api/tickets</strong>
                            <p class="mb-0">Create a new ticket</p>
                        </div>
                    </div>
                </div>

                <h5>Response Format</h5>
                <p>All API responses are returned in JSON format with the following structure:</p>
                <div class="code-block">
{
    "status": true,
    "message": "Success",
    "data": {
        // Response data here
    }
}
                </div>
            </div>
        </div>

        <!-- Examples Section -->
        <div class="content-section" id="examples">
            <div class="section-header">
                <h4><i class="fas fa-lightbulb"></i> Examples</h4>
            </div>
            <div class="section-body">
                <h5>Create a New Lead</h5>
                <div class="code-block">
curl -X POST "https://yourdomain.com/api/leads" \
  -H "Authorization: Bearer YOUR_API_KEY_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "company": "Example Corp",
    "source": "Website",
    "status": "New"
}'
                </div>

                <h5>Get All Clients</h5>
                <div class="code-block">
curl -X GET "https://yourdomain.com/api/clients" \
  -H "Authorization: Bearer YOUR_API_KEY_HERE" \
  -H "Content-Type: application/json"
                </div>

                <h5>Create a New Ticket</h5>
                <div class="code-block">
curl -X POST "https://yourdomain.com/api/tickets" \
  -H "Authorization: Bearer YOUR_API_KEY_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "subject": "API Test Ticket",
    "message": "This is a test ticket created via API",
    "department": "Support",
    "priority": "Medium",
    "status": "Open"
}'
                </div>
            </div>
        </div>

        <!-- Playground Section -->
        <div class="content-section" id="playground">
            <div class="section-header">
                <h4><i class="fas fa-flask"></i> API Playground</h4>
            </div>
            <div class="section-body">
                <p>Test the API directly in your browser using our interactive playground:</p>
                <a href="<?php echo base_url('api/playground'); ?>" class="btn btn-primary">
                    <i class="fas fa-play"></i> Open API Playground
                </a>
                <a href="<?php echo base_url('api/playground/swagger'); ?>" class="btn btn-outline-primary ms-2">
                    <i class="fas fa-code"></i> View Swagger UI
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-4">
            <p class="text-muted">
                <a href="https://perfexcrm.themesic.com/apiguide/" target="_blank" class="btn btn-outline-primary">
                    <i class="fas fa-external-link-alt"></i> View Full API Documentation
                </a>
            </p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
