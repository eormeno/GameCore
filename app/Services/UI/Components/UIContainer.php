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
    protected int|string|null $slot = null;
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
     * Set the slot name for this container
     * 
     * @param int|string|null $slot The slot (int = parent ID, string = parent name, null = delete)
     * @return self For method chaining
     */
    public function slot(int|string|null $slot): self
    {
        return $this->setSlot($slot);
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

        // Automatically set the child's slot to this container's ID
        $element->setSlot($this->id);

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
     * @param int|string $elementId The ID of the element to remove
     * @return self For method chaining
     * @throws \InvalidArgumentException If element not found
     */
    public function remove(int|string $elementId): self
    {
        $elementId = (string)$elementId;
        if (!isset($this->children[$elementId])) {
            throw new \InvalidArgumentException(
                "Element with ID '{$elementId}' not found in container '{$this->id}'"
            );
        }

        // Mark the element for deletion by setting slot to null
        $this->children[$elementId]->setSlot(null);

        unset($this->children[$elementId]);
        return $this;
    }

    /**
     * Remove a child element from this container by ID (silent version)
     * Returns true if element was removed, false if not found
     * 
     * @param int|string $elementId The ID of the element to remove
     * @return bool True if removed, false if not found
     */
    public function tryRemove(int|string $elementId): bool
    {
        $elementId = (string)$elementId;
        if (isset($this->children[$elementId])) {
            // Mark the element for deletion by setting slot to null
            $this->children[$elementId]->setSlot(null);
            
            unset($this->children[$elementId]);
            return true;
        }
        return false;
    }

    /**
     * Update a child element by replacing it with a new element
     * 
     * @param int|string $elementId The ID of the element to update
     * @param UIElement $newElement The new element to replace with
     * @return self For method chaining
     * @throws \InvalidArgumentException If element not found or IDs don't match
     */
    public function update(int|string $elementId, UIElement $newElement): self
    {
        $elementId = (string)$elementId;
        if (!isset($this->children[$elementId])) {
            throw new \InvalidArgumentException(
                "Element with ID '{$elementId}' not found in container '{$this->id}'"
            );
        }

        if ((string)$newElement->getId() !== $elementId) {
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
     * @param int|string $elementId The ID of the element to find
     * @return UIElement|null The found element, or null if not found
     */
    public function find(int|string $elementId): ?UIElement
    {
        $elementId = (string)$elementId;
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
     * @param int|string $elementId The ID of the element to check
     * @return bool True if element exists as direct child, false otherwise
     */
    public function has(int|string $elementId): bool
    {
        $elementId = (string)$elementId;
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
     * Converts this container and all its children to a flat JSON structure
     * All components are returned at the same level, with 'slot' indicating parent-child relationships
     */
    public function toJson(): array
    {
        // Start with this container's configuration
        $config = $this->config;
        
        // Include 'name' attribute only if it's not null (for client-side referencing)
        if ($this->name !== null) {
            $config['name'] = $this->name;
        }

        // Start with this container
        $result = [$this->id => $config];

        // Add all children at the same level (flat structure)
        foreach ($this->children as $child) {
            $childJson = $child->toJson();
            // Use the + operator to preserve numeric keys (IDs)
            $result = $result + $childJson;
        }

        return $result;
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
