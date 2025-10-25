<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Demo - {{ ucfirst(str_replace('-', ' ', $demo)) }} - GameCore</title>
    <link rel="stylesheet" href="{{ asset('css/ui-components.css') }}">
</head>
<body>
    <div id="main"></div>
    
    <script>
        // Pass demo name from Laravel to JavaScript
        window.DEMO_NAME = '{{ $demo }}';
    </script>
    <script src="{{ asset('js/ui-renderer.js') }}"></script>
</body>
</html>
