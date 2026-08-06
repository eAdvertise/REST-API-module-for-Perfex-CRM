<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@4.15.5/swagger-ui.css" />
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .swagger-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            text-align: center;
        }
        .swagger-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .swagger-ui {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .back-button:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="swagger-header">
        <h1><i class="fas fa-code"></i> eAD-CRM API - Swagger UI</h1>
        <p class="lead">Interactive API documentation and testing</p>
    </div>

    <!-- Swagger UI Container -->
    <div class="swagger-container">
        <div class="swagger-ui">
            <div id="swagger-ui"></div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/swagger-ui-dist@4.15.5/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@4.15.5/swagger-ui-standalone-preset.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
    <style>
        .swagger-ui > div:nth-child(2) > .wrapper:nth-child(5) {
            display: none;
        }
    </style>
    <script>
        // Swagger UI configuration
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: '<?php echo base_url('api/openapi.json'); ?>',
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                tryItOutEnabled: true,
                requestInterceptor: function(request) {
                    // Add base URL to requests
                    if (request.url.startsWith('/api/')) {
                        request.url = '<?php echo base_url(); ?>' + request.url;
                    }
                    return request;
                },
                onComplete: function() {
                    // Add authentication button
                    const authButton = document.createElement('button');
                    authButton.innerHTML = '<i class="fas fa-key"></i> Set API Key';
                    authButton.className = 'btn btn-primary';
                    authButton.style.margin = '10px';
                    authButton.onclick = function() {
                        const apiKey = prompt('Enter your API key:');
                        if (apiKey) {
                            // Set the API key in Swagger UI
                            ui.preauthorizeApiKey('Bearer', apiKey);
                            alert('API key set successfully!');
                        }
                    };
                    
                    const topbar = document.querySelector('.swagger-ui .topbar');
                    if (topbar) {
                        topbar.appendChild(authButton);
                    }
                }
            });
        };
    </script>
</body>
</html>
