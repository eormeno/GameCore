<?php

namespace App\Services\UI\Components;

use App\Services\UI\Contracts\UIElement;
use App\Services\UI\Support\UIIdGenerator;

/**
 * Abstract base class for all leaf UI components (Button, Label, Table, etc.)
 * 
 * This class implements the common functionality for leaf nodes in the UI tree.
 * Leaf components cannot have children and represent atomic UI elements.
 */
abstract class UIComponent implements UIElement
{
    protected int $id;
    protected string $type;
    protected ?string $name = null;
    protected int|string|null $slot = null;
    protected array $config = [];

    public function __construct(?string $name = null)
    {
        $this->name = $name;

        // Detectar automáticamente el contexto desde la clase que invoca
        $context = $this->detectCallingContext();

        // Usar el generador centralizado de IDs
        $this->id = UIIdGenerator::generate($context);

        $this->type = $this->getTypeFromClassName();
        $this->config = array_merge([
            'type' => $this->type,
            'visible' => true,
            'slot' => null,
        ], $this->getDefaultConfig());
        
        // Only include 'name' if it's not null
        if ($this->name !== null) {
            $this->config['name'] = $this->name;
        }
    }

    /**
     * Detecta automáticamente la clase que está invocando el builder
     * Busca en el stack trace la primera clase fuera del namespace UI
     * 
     * @return string El nombre base de la clase invocante
     */
    private function detectCallingContext(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);

        // Buscar en el stack trace la primera clase que NO sea del namespace UI
        foreach ($trace as $frame) {
            if (
                isset($frame['class']) &&
                !str_starts_with($frame['class'], 'App\\Services\\UI\\')
            ) {
                return class_basename($frame['class']);
            }
        }

        return 'default';
    }

    /**
     * Extract the component type from the class name
     * Example: "ButtonBuilder" -> "button"
     */
    private function getTypeFromClassName(): string
    {
        $className = (new \ReflectionClass($this))->getShortName();
        return strtolower(str_replace('Builder', '', $className));
    }

    /**
     * Get the default configuration for this component type
     * Must be implemented by each concrete component
     * 
     * @return array Default configuration values
     */
    abstract protected function getDefaultConfig(): array;

    /**
     * {@inheritDoc}
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function isVisible(): bool
    {
        return $this->config['visible'] ?? true;
    }

    /**
     * {@inheritDoc}
     */
    public function setVisible(bool $visible): self
    {
        $this->config['visible'] = $visible;
        return $this;
    }

    /**
     * Fluent API for setting visibility
     */
    public function visible(bool $visible = true): self
    {
        return $this->setVisible($visible);
    }

    /**
     * {@inheritDoc}
     * @return $this
     */
    public function name(?string $name): self
    {
        $this->name = $name;
        if ($name !== null) {
            $this->config['name'] = $name;
        } else {
            unset($this->config['name']);
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setName(?string $name): self
    {
        $this->name = $name;
        if ($name !== null) {
            $this->config['name'] = $name;
        } else {
            unset($this->config['name']);
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSlot(): int|string|null
    {
        return $this->slot;
    }

    /**
     * {@inheritDoc}
     */
    public function setSlot(int|string|null $slot): self
    {
        $this->slot = $slot;
        $this->config['slot'] = $slot;
        return $this;
    }

    /**
     * Fluent API for setting slot
     * 
     * @param int|string|null $slot The slot (int = parent ID, string = parent name, null = delete)
     * @return self For method chaining
     */
    public function slot(int|string|null $slot): self
    {
        return $this->setSlot($slot);
    }

    /**
     * {@inheritDoc}
     * 
     * For leaf components, returns the configuration wrapped in the component ID
     * Null values are filtered out from the configuration
     */
    public function toJson(): array
    {
        // Filter out null values from config
        $config = array_filter($this->config, fn($value) => $value !== null);
        
        // Remove 'visible' if it's true (default value)
        if (isset($config['visible']) && $config['visible'] === true) {
            unset($config['visible']);
        }
        
        // Allow subclasses to exclude additional keys
        $excludeKeys = $this->getExcludedJsonKeys();
        if (!empty($excludeKeys)) {
            $config = array_diff_key($config, array_flip($excludeKeys));
        }
        
        return [$this->id => $config];
    }

    /**
     * Get list of config keys to exclude from JSON output
     * Override in subclasses to customize
     * 
     * @return array List of keys to exclude
     */
    protected function getExcludedJsonKeys(): array
    {
        return [];
    }

    /**
     * Get the component configuration (without ID wrapper)
     * Useful for internal operations
     * 
     * @return array The configuration array
     */
    protected function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set a configuration value
     * 
     * @param string $key The configuration key
     * @param mixed $value The configuration value
     * @return self For method chaining
     */
    protected function setConfig(string $key, mixed $value): self
    {
        $this->config[$key] = $value;
        return $this;
    }

    /**
     * Método de utilidad para debugging - obtiene información del contexto
     * 
     * @param string $context Nombre del contexto
     * @return array Información del contexto (offset, contador, etc)
     */
    public static function getContextInfo(string $context): array
    {
        return UIIdGenerator::getContextInfo($context);
    }
}
