<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swagger UI</title>
    <link rel="stylesheet" href="{{ asset('swagger/swagger-ui.css') }}">
</head>

<body>
    <div id="swagger-ui"></div>

    <script src="{{ asset('swagger/swagger-ui-bundle.js') }}"></script>
    <script src="{{ asset('swagger/swagger-ui-standalone-preset.js') }}"></script>
    <script>
        window.onload = () => {
            const ui = SwaggerUIBundle({
                url: "{{ asset('swagger/swagger.yaml') }}",
                dom_id: '#swagger-ui',
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                layout: "BaseLayout"
            });
        };
    </script>
</body>

</html>