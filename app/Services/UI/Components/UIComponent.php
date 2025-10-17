<?php

namespace App\Services\UI\Components;

use App\Services\UI\Contracts\UIElement;

/**
 * Abstract base class for all leaf UI components (Button, Label, Table, etc.)
 * 
 * This class implements the common functionality for leaf nodes in the UI tree.
 * Leaf components cannot have children and represent atomic UI elements.
 */
abstract class UIComponent implements UIElement
{
    protected string $id;
    protected string $type;
    protected array $config = [];

    public function __construct(string $id)
    {
        $this->type = $this->getTypeFromClassName();
        $this->id = $id;
        $this->config = array_merge([
            'type' => $this->type,
            'visible' => true,
        ], $this->getDefaultConfig());
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
    public function getId(): string
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
     * 
     * For leaf components, returns the configuration wrapped in the component ID
     */
    public function toJson(): array
    {
        return [$this->id => $this->config];
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
}
