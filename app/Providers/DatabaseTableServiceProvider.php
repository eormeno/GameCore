<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\DatabaseTable\DatabaseTableService;
use App\Services\DatabaseTable\Contracts\DatabaseTableServiceInterface;

class DatabaseTableServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(DatabaseTableServiceInterface::class, function () {
            return new DatabaseTableService();
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
