<?php

namespace App\Services\UI;

use App\Services\UI\Components\UIContainer;
use App\Services\UI\Support\UIDiffer;
use App\Services\UI\Enums\LayoutType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

/**
 * Abstract UI Service
 * 
 * Base class for all UI services that handles:
 * - UI state storage and retrieval
 * - Automatic diff calculation
 * - Event lifecycle management
 * - Response formatting
 * 
 * Child classes only need to:
 * 1. Implement buildBaseUI() to define UI structure
 * 2. Implement event handlers that modify components (no return needed)
 * 
 * The lifecycle is managed by UIEventController:
 * - initializeEventContext() - Called before event handler
 * - onEventHandler($params) - Your event handler
 * - finalizeEventContext() - Called after event handler, returns formatted response
 */
abstract class AbstractUIService
{
    /**
     * Current UI container instance
     */
    protected UIContainer $container;
    
    /**
     * UI state before modifications (for diff calculation)
     */
    protected ?array $oldUI = null;
    
    /**
     * UI state after modifications (for diff calculation)
     */
    protected ?array $newUI = null;
    
    /**
     * Whether the UI has been modified during event handling
     */
    protected bool $modified = false;

    /**
     * Build base UI structure
     * 
     * Override this method in your service to define the base UI.
     * This will be called automatically if the cache expires.
     * 
     * @param mixed ...$params Optional parameters for UI construction
     * @return UIContainer Base UI structure
     */
    abstract protected function buildBaseUI(...$params): UIContainer;

    /**
     * Initialize event context
     * 
     * Called by UIEventController before invoking event handler.
     * Loads UI container and captures state for diff calculation.
     * Also injects component references into protected properties.
     * 
     * @return void
     */
    public function initializeEventContext(): void
    {
        $this->container = $this->getUIContainer();
        $this->oldUI = $this->container->toJson();
        $this->modified = false;
        
        // Inject component references into protected properties
        $this->injectComponentReferences();
    }
    
    /**
     * Inject component references into protected properties
     * 
     * Uses reflection to find protected properties with UI component type hints.
     * If a property name matches a component name in the container, 
     * the component is injected into that property.
     * 
     * Convention: Property name must match component name
     * Example: protected LabelBuilder $lbl_result; matches component 'lbl_result'
     * 
     * @return void
     */
    private function injectComponentReferences(): void
    {
        $reflection = new \ReflectionClass($this);
        
        foreach ($reflection->getProperties(\ReflectionProperty::IS_PROTECTED) as $property) {
            // Skip properties declared in AbstractUIService itself
            if ($property->getDeclaringClass()->getName() === self::class) {
                continue;
            }
            
            $propertyType = $property->getType();
            
            // Skip if no type hint or is a built-in type
            if (!$propertyType || $propertyType->isBuiltin()) {
                continue;
            }
            
            $typeName = $propertyType->getName();
            
            // Only process UI component types
            if (str_starts_with($typeName, 'App\\Services\\UI\\Components\\')) {
                $componentName = $property->getName();
                $component = $this->container->findByName($componentName);
                
                if ($component) {
                    $property->setValue($this, $component);
                } elseif (!$propertyType->allowsNull()) {
                    // Component not found and property is not nullable
                    throw new \RuntimeException(
                        "Component '{$componentName}' not found in UI container. " .
                        "Make sure the component exists or make the property nullable: protected ?{$typeName} \${$componentName};"
                    );
                }
            }
        }
    }

    /**
     * Finalize event context
     * 
     * Called by UIEventController after event handler completes.
     * Automatically detects changes by comparing UI state, stores updated UI,
     * and returns formatted response.
     * 
     * @return array Indexed diff response
     */
    public function finalizeEventContext(): array
    {
        // Get current UI state
        $this->newUI = $this->container->toJson();

        // Auto-detect if UI was modified by comparing states
        if ($this->oldUI === $this->newUI) {
            // No changes detected, return empty response
            return [];
        }

        // Store updated UI
        $this->storeUI($this->container);

        // Calculate and return diff in indexed format
        return $this->buildDiffResponse();
    }

    /**
     * Build diff response in indexed format
     * 
     * @return array Indexed diff response
     */
    protected function buildDiffResponse(): array
    {
        if (!$this->oldUI || !$this->newUI) {
            return [];
        }

        $diff = UIDiffer::compare($this->oldUI, $this->newUI);
        
        $result = [];
        foreach ($diff as $componentId => $changes) {
            $changes['_id'] = $componentId;
            $result[$componentId] = $changes;
        }
        
        return $result;
    }

    /**
     * Get the UI structure
     * 
     * Returns the UI from cache or regenerates if not exists.
     * This is the standard public method to retrieve UI for all services.
     * 
     * @param mixed ...$params Optional parameters that can be used by child classes
     * @return array UI structure in JSON format
     */
    public function getUI(...$params): array
    {
        return $this->getStoredUI(...$params);
    }
    
    /**
     * Get stored UI state, regenerate if missing
     * 
     * @param mixed ...$params Optional parameters passed to buildBaseUI
     * @return array UI structure in JSON format
     */
    protected function getStoredUI(...$params): array
    {
        $key = $this->getUIStorageKey();
        
        // Check if UI exists in cache
        $cachedUI = Cache::get($key);
        
        if ($cachedUI !== null) {
            return $cachedUI;
        }
        
        // Generate and cache new UI
        $ui = $this->buildBaseUI(...$params)->toJson();
        Cache::put($key, $ui, 1800); // 30 minutes in seconds
        
        return $ui;
    }
    
    /**
     * Get UI container instance from cache, regenerate if missing
     * 
     * @return UIContainer UI container instance
     */
    protected function getUIContainer(): UIContainer
    {
        // Always get JSON from cache and reconstruct container
        // This ensures we get the latest state after events modify it
        $jsonUI = $this->getStoredUI();
        
        // Reconstruct container from JSON
        return $this->reconstructContainerFromJson($jsonUI);
    }
    
    /**
     * Reconstruct UI container from JSON array
     * 
     * @param array $jsonUI JSON representation of UI
     * @return UIContainer Reconstructed container
     */
    protected function reconstructContainerFromJson(array $jsonUI): UIContainer
    {
        // Find container component
        $containerData = null;
        foreach ($jsonUI as $component) {
            if ($component['type'] === 'container') {
                $containerData = $component;
                break;
            }
        }
        
        if (!$containerData) {
            // No cached container, build fresh
            return $this->buildBaseUI();
        }
        
        // Build container with cached properties
        $container = UIBuilder::container($containerData['name'] ?? 'main')
            ->parent($containerData['parent'] ?? 'main')
            ->layout(LayoutType::from($containerData['layout']))
            ->title($containerData['title'] ?? '');
        
        // Restore container ID
        if (isset($containerData['_id'])) {
            $reflection = new \ReflectionProperty(UIContainer::class, 'id');
            $reflection->setValue($container, $containerData['_id']);
        }
        
        // Add all child components from JSON
        foreach ($jsonUI as $componentId => $componentData) {
            if ($componentData['type'] === 'container') {
                continue; // Skip container itself
            }
            
            // Recreate component based on type
            $component = $this->recreateComponentFromJson($componentData);
            if ($component) {
                $container->add($component);
            }
        }
        
        return $container;
    }
    
    /**
     * Recreate a component from JSON data
     * 
     * @param array $data Component JSON data
     * @return mixed Component builder instance or null
     */
    protected function recreateComponentFromJson(array $data)
    {
        $type = $data['type'];
        $name = $data['name'] ?? null;  // Changed from '_name' to 'name'
        $originalId = $data['_id'] ?? null;
        
        if (!$name || !$originalId) {
            return null;
        }
        
        // Create component using UIBuilder
        $component = match($type) {
            'label' => UIBuilder::label($name),
            'button' => UIBuilder::button($name),
            'input' => UIBuilder::input($name),
            'select' => UIBuilder::select($name),
            'checkbox' => UIBuilder::checkbox($name),
            default => null
        };
        
        if (!$component) {
            return null;
        }
        
        // Restore original ID using reflection
        $reflection = new \ReflectionProperty(get_class($component), 'id');
        $reflection->setValue($component, $originalId);
        
        // Restore properties from JSON
        foreach ($data as $key => $value) {
            // Skip internal properties
            if (str_starts_with($key, '_')) {
                continue;
            }
            
            // Set property if method exists
            if (method_exists($component, $key)) {
                $component->$key($value);
            }
        }
        
        return $component;
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
        
        // Only store JSON, container will be reconstructed when needed
        Cache::put($key, $ui->toJson(), 1800); // 30 minutes in seconds
    }
    
    /**
     * Clear stored UI state
     * 
     * @return void
     */
    protected function clearStoredUI(): void
    {
        Cache::forget($this->getUIStorageKey());
    }
    
    /**
     * Generate unique storage key per service + user
     * 
     * @return string Cache key
     */
    private function getUIStorageKey(): string
    {
        $serviceClass = class_basename(static::class);
        $userId = Auth::check() ? Auth::id() : session()->getId();
        
        return "ui_state:{$serviceClass}:{$userId}";
    }
    
    /**
     * Generate unique storage key for UI container object
     * 
     * @return string Cache key for container
     */
    private function getUIContainerStorageKey(): string
    {
        return $this->getUIStorageKey() . ':container';
    }
}
