<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clean Interactive API Docs - Ananya</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5.10.5/swagger-ui.css" />
    <style>
        html {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }
        *,
        *:before,
        *:after {
            box-sizing: inherit;
        }
        body {
            margin: 0;
            background: #fafafa;
        }
        .swagger-ui .topbar {
            background: linear-gradient(45deg, #2563eb, #7c3aed);
            border-bottom: 2px solid #fbbf24;
        }
        .swagger-ui .topbar .download-url-wrapper {
            display: none;
        }
        .swagger-ui .info .title {
            color: #2563eb;
        }
        .swagger-ui .scheme-container {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .swagger-ui .opblock.opblock-get .opblock-summary {
            background: rgba(37, 99, 235, 0.1);
            border-color: #2563eb;
        }
        .swagger-ui .opblock.opblock-post .opblock-summary {
            background: rgba(124, 58, 237, 0.1);
            border-color: #7c3aed;
        }
        .main-content {
            padding-top: 20px;
        }
        .page-header {
            background: white;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
        }
        .clean-api-badge {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 1000;
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.8rem;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <?php 
    $root_path = '../';
    $css_path = '../css/';
    include '../includes/header.php'; 
    ?>
    
    <div class="main-content">
        <div class="container-fluid">
            <div class="row page-header">
                <div class="col-12">
                    <h2 class="text-center mb-4">Interactive API Documentation</h2>
                    <p class="text-center text-muted">Test and explore the Ananya API endpoints directly in your browser</p>
                </div>
            </div>
        </div>
        
        <!-- Clean API Badge -->
        <div class="clean-api-badge">
            <i class="fas fa-rocket me-1"></i>Clean URLs
        </div>
        
        <div id="swagger-ui"></div>
    </div>
    
    <script src="https://unpkg.com/swagger-ui-dist@5.10.5/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5.10.5/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            // Begin Swagger UI call region
            const ui = SwaggerUIBundle({
                url: './openapi.yaml',
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
                validatorUrl: null,
                tryItOutEnabled: true,
                supportedSubmitMethods: ['get', 'post', 'put', 'delete'],
                requestInterceptor: function(request) {
                    // Ensure all requests use clean URLs
                    console.log('Making request to:', request.url);
                    return request;
                },
                onComplete: function() {
                    console.log("Clean API Swagger UI loaded successfully");
                },
                onFailure: function(data) {
                    console.log("Failed to load Clean OpenAPI spec:", data);
                }
            });
            // End Swagger UI call region

            window.ui = ui;
        };
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>