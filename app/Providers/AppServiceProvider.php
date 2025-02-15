<?php

namespace App\Providers;

use App\Contracts\IRenderer;
use App\Services\RendererService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use App\Models\Prefab\Managers\ComponentManager;
use App\Models\Prefab\Parsers\GameObjectNameParser;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(IRenderer::class, RendererService::class);
        $this->app->bind(GameObjectNameParser::class, function ($app) {
            return new GameObjectNameParser();
        });
        $this->app->bind(ComponentManager::class, function ($app) {
            return new ComponentManager();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configura las rutas API
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));
    }
}
