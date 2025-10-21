<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Demo - GameCore</title>
</head>
<body>
    <div id="main"></div>

    <script>
        // Fetch UI components from backend
        async function loadDemoUI() {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                const response = await fetch('/api/demo-ui', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const uiData = await response.json();
                console.log('UI Data received:', uiData);
                
                // Here you would render the UI components
                // For now, just display the JSON structure
                renderUI(uiData);
                
            } catch (error) {
                console.error('Error loading demo UI:', error);
                document.getElementById('main').innerHTML = `
                    <div style="padding: 20px; color: red;">
                        <h2>Error loading UI components</h2>
                        <p>${error.message}</p>
                    </div>
                `;
            }
        }

        // Simple renderer for demonstration
        function renderUI(data) {
            const mainContainer = document.getElementById('main');
            mainContainer.innerHTML = `
                <div style="padding: 20px; font-family: Arial, sans-serif;">
                    <h1>Demo UI Components</h1>
                    <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow: auto;">
${JSON.stringify(data, null, 2)}
                    </pre>
                </div>
            `;
        }

        // Load UI on page load
        document.addEventListener('DOMContentLoaded', loadDemoUI);
    </script>
</body>
</html>
