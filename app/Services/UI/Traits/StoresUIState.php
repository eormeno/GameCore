<?php

namespace App\Services\UI\Traits;

use App\Services\UI\Components\UIContainer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

/**
 * Trait para almacenar y recuperar estado de UI en cache
 * 
 * Permite mantener una copia de la UI en file cache para:
 * - Comparar cambios (diffing)
 * - Auto-regenerar si el cache expira
 * - Aislar estado por usuario
 * 
 * Performance con file cache: ~3ms read, ~5ms write
 */
trait StoresUIState
{
    /**
     * Get stored UI state, regenerate if missing
     * 
     * @return array UI structure in JSON format
     */
    protected function getStoredUI(): array
    {
        $key = $this->getUIStorageKey();
        
        // Cache::remember auto-regenera si no existe o expiró
        return Cache::remember($key, now()->addMinutes(30), function() {
            // Auto-regenerar UI base si no existe en cache
            return $this->buildBaseUI()->toJson();
        });
    }
    
    /**
     * Store UI state in cache
     * 
     * @param UIContainer $ui UI container to store
     * @return void
     */
    protected function storeUI(UIContainer $ui): void
    {
        $key = $this->getUIStorageKey();
        
        // Guardar por 30 minutos (suficiente para sesión activa)
        Cache::put($key, $ui->toJson(), now()->addMinutes(30));
    }
    
    /**
     * Clear stored UI state
     * 
     * @return void
     */
    protected function clearStoredUI(): void
    {
        $key = $this->getUIStorageKey();
        Cache::forget($key);
    }
    
    /**
     * Build base UI structure
     * 
     * Override this method in your service to define the base UI.
     * This will be called automatically if the cache expires.
     * 
     * @return UIContainer Base UI structure
     */
    abstract protected function buildBaseUI(): UIContainer;
    
    /**
     * Generate unique storage key per service + user
     * 
     * @return string Cache key
     */
    private function getUIStorageKey(): string
    {
        $serviceClass = class_basename(static::class);
        
        // Usar user ID si está autenticado, sino usar session ID
        $userId = Auth::check() ? Auth::id() : session()->getId();
        
        return "ui_state:{$serviceClass}:{$userId}";
    }
}
