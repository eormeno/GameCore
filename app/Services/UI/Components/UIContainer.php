<?php

namespace App\Services\UI\Components;

use App\Services\UI\Contracts\UIElement;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\Support\UIIdGenerator;

/**
 * Composite UI Container that can hold and manage child UI elements
 * 
 * This class implements the Composite pattern, allowing UI elements to be
 * organized in a tree structure. It provides methods to add, remove, update,
 * and find child elements, as well as recursive JSON serialization.
 */
class UIContainer implements UIElement
{
    protected int $id;
    protected string $type = 'container';
    protected ?string $name = null;
    protected array $config = [];

    /** @var array<string, UIElement> Map of element ID to UIElement instance */
    protected array $children = [];

    /** @var array|null Legacy elements array for backward compatibility */
    public ?array $legacyElements = null;

    public function __construct(?string $name = null)
    {
        $this->name = $name;

        // Detectar automáticamente el contexto desde la clase que invoca
        $context = $this->detectCallingContext();

        // Usar el generador centralizado de IDs
        $this->id = UIIdGenerator::generate($context);

        $this->config = [
            'type' => $this->type,
            'visible' => true,
            'layout' => LayoutType::VERTICAL->value,
            'slot' => null,
            'title' => null,
        ];
    }

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
     * Set the name for this container
     * 
     * @param string|null $name The container name
     * @return self For method chaining
     */
    public function name(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set the title for this container
     * 
     * @param string|null $title The container title
     * @return self For method chaining
     */
    public function setName(string|null $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set the slot name for this container
     * 
     * @param string $slot The slot name (e.g., 'canvas', 'sidebar')
     * @return self For method chaining
     */
    public function slot(string $slot): self
    {
        $this->config['slot'] = $slot;
        return $this;
    }

    /**
     * Set the layout type for this container
     * 
     * @param LayoutType $layout The layout type (VERTICAL or HORIZONTAL)
     * @return self For method chaining
     */
    public function layout(LayoutType $layout): self
    {
        $this->config['layout'] = $layout->value;
        return $this;
    }

    /**
     * Set the title for this container
     * 
     * @param string $title The container title
     * @return self For method chaining
     */
    public function title(string $title): self
    {
        $this->config['title'] = $title;
        return $this;
    }

    /**
     * Add a child element to this container
     * 
     * @param UIElement $element The element to add
     * @return self For method chaining
     * @throws \InvalidArgumentException If element with same ID already exists
     */
    public function add(UIElement $element): self
    {
        $elementId = $element->getId();

        if (isset($this->children[$elementId])) {
            throw new \InvalidArgumentException(
                "Element with ID '{$elementId}' already exists in container '{$this->id}'"
            );
        }

        $this->children[$elementId] = $element;
        return $this;
    }

    /**
     * Add multiple child elements to this container
     * 
     * @param array<UIElement> $elements Array of elements to add
     * @return self For method chaining
     */
    public function addMany(array $elements): self
    {
        foreach ($elements as $element) {
            if ($element instanceof UIElement) {
                $this->add($element);
            }
        }
        return $this;
    }

    /**
     * Remove a child element from this container by ID
     * 
     * @param string $elementId The ID of the element to remove
     * @return self For method chaining
     * @throws \InvalidArgumentException If element not found
     */
    public function remove(string $elementId): self
    {
        if (!isset($this->children[$elementId])) {
            throw new \InvalidArgumentException(
                "Element with ID '{$elementId}' not found in container '{$this->id}'"
            );
        }

        unset($this->children[$elementId]);
        return $this;
    }

    /**
     * Remove a child element from this container by ID (silent version)
     * Returns true if element was removed, false if not found
     * 
     * @param string $elementId The ID of the element to remove
     * @return bool True if removed, false if not found
     */
    public function tryRemove(string $elementId): bool
    {
        if (isset($this->children[$elementId])) {
            unset($this->children[$elementId]);
            return true;
        }
        return false;
    }

    /**
     * Update a child element by replacing it with a new element
     * 
     * @param string $elementId The ID of the element to update
     * @param UIElement $newElement The new element to replace with
     * @return self For method chaining
     * @throws \InvalidArgumentException If element not found or IDs don't match
     */
    public function update(string $elementId, UIElement $newElement): self
    {
        if (!isset($this->children[$elementId])) {
            throw new \InvalidArgumentException(
                "Element with ID '{$elementId}' not found in container '{$this->id}'"
            );
        }

        if ($newElement->getId() !== $elementId) {
            throw new \InvalidArgumentException(
                "New element ID '{$newElement->getId()}' does not match target ID '{$elementId}'"
            );
        }

        $this->children[$elementId] = $newElement;
        return $this;
    }

    /**
     * Find a child element by ID (searches recursively through the tree)
     * 
     * @param string $elementId The ID of the element to find
     * @return UIElement|null The found element, or null if not found
     */
    public function find(string $elementId): ?UIElement
    {
        // Check direct children first
        if (isset($this->children[$elementId])) {
            return $this->children[$elementId];
        }

        // Search recursively in child containers
        foreach ($this->children as $child) {
            if ($child instanceof UIContainer) {
                $found = $child->find($elementId);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * Check if this container has a specific child element
     * 
     * @param string $elementId The ID of the element to check
     * @return bool True if element exists as direct child, false otherwise
     */
    public function has(string $elementId): bool
    {
        return isset($this->children[$elementId]);
    }

    /**
     * Get all direct child elements
     * 
     * @return array<UIElement> Array of child elements
     */
    public function getChildren(): array
    {
        return array_values($this->children);
    }

    /**
     * Get the number of direct children
     * 
     * @return int The number of children
     */
    public function count(): int
    {
        return count($this->children);
    }

    /**
     * Remove all child elements
     * 
     * @return self For method chaining
     */
    public function clear(): self
    {
        $this->children = [];
        return $this;
    }

    /**
     * {@inheritDoc}
     * 
     * Recursively converts this container and all its children to JSON format
     * Children are serialized in the 'elements' array within the configuration
     */
    public function toJson(): array
    {
        // Build the elements array by recursively calling toJson() on all children
        $elements = [];
        foreach ($this->children as $child) {
            $childJson = $child->toJson();
            // Use the + operator to preserve numeric keys (IDs)
            $elements = $elements + $childJson;
        }

        // Build the final configuration with children
        // Include 'name' attribute only if it's not null (for client-side referencing)
        $config = array_merge($this->config, [
            'elements' => $elements,
        ]);
        
        if ($this->name !== null) {
            $config['name'] = $this->name;
        }

        return [$this->id => $config];
    }

    /**
     * Convert to array (alias for toJson for backward compatibility)
     * 
     * @return array
     */
    public function build(): array
    {
        return $this->toJson();
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
}
