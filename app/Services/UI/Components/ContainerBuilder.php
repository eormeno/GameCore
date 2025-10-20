<?php

namespace App\Services\UI\Components;

use App\Services\UI\Enums\LayoutType;
use App\Services\UI\Contracts\UIElement;

/**
 * Builder for Container UI components
 * 
 * This builder creates UIContainer instances that can hold and manage
 * child UI elements in a tree structure. It provides a fluent API
 * for configuring and building containers.
 * 
 * @deprecated This builder class is maintained for backward compatibility.
 *             Consider using UIContainer directly for new code.
 */
class ContainerBuilder
{
    private UIContainer $container;

    public function __construct(?string $name = null)
    {
        $this->container = new UIContainer($name);
    }

    /**
     * Set the parent for this container
     * 
     * @param int|string|null $parent The parent (int = parent ID, string = parent name, null = delete)
     * @return self For method chaining
     */
    public function parent(int|string|null $parent): self
    {
        $this->container->parent($parent);
        return $this;
    }

    /**
     * Set the layout type for this container
     * 
     * @param LayoutType $layout The layout type
     * @return self For method chaining
     */
    public function layout(LayoutType $layout): self
    {
        $this->container->layout($layout);
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
        $this->container->title($title);
        return $this;
    }

    /**
     * Set visibility of the container
     * 
     * @param bool $visible True if visible, false otherwise
     * @return self For method chaining
     */
    public function visible(bool $visible = true): self
    {
        $this->container->visible($visible);
        return $this;
    }

    /**
     * Add a child element to this container
     * 
     * @param UIElement|UIContainer|ContainerBuilder $element The element to add
     * @return self For method chaining
     */
    public function add($element): self
    {
        // If it's a ContainerBuilder, unwrap it to get the UIContainer
        if ($element instanceof ContainerBuilder) {
            $element = $element->getContainer();
        }
        
        $this->container->add($element);
        return $this;
    }

    /**
     * Add multiple child elements to this container
     * 
     * @param array $elements Array of elements to add
     * @return self For method chaining
     */
    public function addMany(array $elements): self
    {
        foreach ($elements as $element) {
            // If it's a ContainerBuilder, unwrap it
            if ($element instanceof ContainerBuilder) {
                $element = $element->getContainer();
            }
            $this->container->addMany([$element]);
        }
        return $this;
    }

    /**
     * Legacy method: Set elements from array (for backward compatibility)
     * 
     * This method supports the old API where elements were passed as arrays.
     * It will merge all array elements into the container's elements.
     * 
     * @param array $elements Array of element configurations
     * @return self For method chaining
     * @deprecated Use add() or addMany() instead
     */
    public function elements(array $elements): self
    {
        // For backward compatibility with code that passes merged arrays
        // This is used during the migration period
        // The elements array is stored temporarily and will be used in build()
        $this->container->legacyElements = $elements;
        return $this;
    }

    /**
     * Build the container and return it as a UIContainer instance
     * This is the preferred method for new code.
     * 
     * @return UIContainer The built container
     */
    public function buildContainer(): UIContainer
    {
        return $this->container;
    }

    /**
     * Build the container and return its JSON representation
     * This method is for backward compatibility.
     * 
     * @return array The JSON representation
     */
    public function build(): array
    {
        // Handle legacy elements if they were set
        if (isset($this->container->legacyElements)) {
            // Merge legacy elements directly into the config
            // This maintains backward compatibility during migration
            $json = $this->container->toJson();
            $containerId = $this->container->getId();
            $json[$containerId]['elements'] = array_merge(
                $json[$containerId]['elements'],
                $this->container->legacyElements
            );
            unset($this->container->legacyElements);
            return $json;
        }
        
        return $this->container->toJson();
    }

    /**
     * Get the underlying UIContainer instance
     * Useful for advanced operations
     * 
     * @return UIContainer
     */
    public function getContainer(): UIContainer
    {
        return $this->container;
    }

    /**
     * Get the container's unique ID
     * 
     * @return int The container ID
     */
    public function getId(): int
    {
        return $this->container->getId();
    }

    /**
     * Remove a child element from this container by ID
     * 
     * @param int $elementId The ID of the element to remove
     * @return self For method chaining
     */
    public function remove(int $elementId): self
    {
        $this->container->remove($elementId);
        return $this;
    }

    /**
     * Remove a child element (silent version)
     * 
     * @param int $elementId The ID of the element to remove
     * @return bool True if removed, false if not found
     */
    public function tryRemove(int $elementId): bool
    {
        return $this->container->tryRemove($elementId);
    }

    /**
     * Convert to JSON representation
     * 
     * @return array The JSON representation
     */
    public function toJson(): array
    {
        return $this->container->toJson();
    }

    /**
     * Find a child element by ID
     * 
     * @param int $elementId The ID to search for
     * @return UIElement|null The found element or null
     */
    public function find(int $elementId): ?UIElement
    {
        return $this->container->find($elementId);
    }

    /**
     * Get all direct child elements
     * 
     * @return array<UIElement> Array of child elements
     */
    public function getChildren(): array
    {
        return $this->container->getChildren();
    }

    /**
     * Get the number of direct children
     * 
     * @return int The number of children
     */
    public function count(): int
    {
        return $this->container->count();
    }

    /**
     * Clear all children
     * 
     * @return self For method chaining
     */
    public function clear(): self
    {
        $this->container->clear();
        return $this;
    }
}