<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TranslationService;

class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(TranslationService::class, function ($app) {
            $service = new TranslationService();
            
            // Pre-cargar módulos comunes al inicio si el caché está habilitado
            if (config('translation.cache_enabled', true)) {
                $service->preloadModules(['common', 'errors']);
            }
            
            return $service;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}