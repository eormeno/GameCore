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

    public function __construct(string $id)
    {
        $this->container = new UIContainer($id);
    }

    /**
     * Set the slot name for this container
     * 
     * @param string $slot The slot name
     * @return self For method chaining
     */
    public function slot(string $slot): self
    {
        $this->container->slot($slot);
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
     * @param UIElement|UIContainer $element The element to add
     * @return self For method chaining
     */
    public function add($element): self
    {
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
        $this->container->addMany($elements);
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
}