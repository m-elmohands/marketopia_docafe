<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Do Cafe API Documentation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.17.14/swagger-ui.css">
    <style>
        html { box-sizing: border-box; overflow-y: scroll; }
        *, *::before, *::after { box-sizing: inherit; }
        body { margin: 0; background: #fafafa; }
        .topbar { display: none; }
        .documentation-links { max-width: 1460px; margin: 0 auto; padding: 16px 24px 0; font-family: sans-serif; }
        .documentation-links a { color: #7c2d12; font-weight: 600; }
    </style>
</head>
<body>
    <nav class="documentation-links" aria-label="API documentation downloads">
        <a href="{{ route('api.postman') }}">Open Postman collection URL</a>
    </nav>
    <div id="swagger-ui"></div>
    <noscript>JavaScript is required to display the interactive API documentation.</noscript>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.17.14/swagger-ui-bundle.js"></script>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            SwaggerUIBundle({
                url: @json(route('api.openapi')),
                dom_id: '#swagger-ui',
                deepLinking: true,
                displayRequestDuration: true,
                persistAuthorization: true,
                tryItOutEnabled: true,
            });
        });
    </script>
</body>
</html>
