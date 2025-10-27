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
    <div id="menu"></div>
    <div id="main"></div>
    <div id="modal-overlay" class="modal-overlay hidden">
        <div id="modal" class="modal-container"></div>
    </div>
    
    <script>
        // Pass demo name from Laravel to JavaScript
        window.DEMO_NAME = '{{ $demo }}';
        window.RESET_DEMO = {{ $reset ? 'true' : 'false' }};
        window.MENU_SERVICE = 'demo-menu';
    </script>
    <script src="{{ asset('js/ui-renderer.js') }}"></script>
</body>
</html>
