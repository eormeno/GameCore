<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class UIDemoController extends Controller
{
    /**
     * Show UI for the specified demo service
     *
     * @param string $demo The demo name from the route (e.g., 'demo-ui', 'input-demo')
     * @param bool $reset Whether to reset the stored UI state
     * @return JsonResponse
     */
    public function show(string $demo, bool $reset = false): JsonResponse
    {
        // Convert kebab-case to PascalCase and append 'Service'
        // Example: 'demo-ui' -> 'DemoUi' -> 'DemoUiService'
        $serviceName = Str::studly($demo) . 'Service';
        
        // Build fully qualified class name
        $serviceClass = "App\\Services\\Screens\\{$serviceName}";
        
        // Check if service class exists
        if (!class_exists($serviceClass)) {
            return response()->json([
                'error' => 'Demo service not found',
                'service' => $serviceName
            ], 404);
        }
        
        // Instantiate service using Laravel's service container
        // This allows dependency injection to work
        $service = app($serviceClass);

        // If the 'reset' url parameter is present, clear any cached data
        if ($reset) {
            $service->clearStoredUI();
        }
        
        // Return UI JSON
        return response()->json($service->getUI());
    }
}
