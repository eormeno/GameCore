<?php

namespace App\Services\UI\Contracts;

/**
 * Interface for all UI elements in the component tree
 * 
 * This interface defines the contract that all UI elements must implement,
 * enabling the Composite pattern for building hierarchical UI structures.
 */
interface UIElement
{
    /**
     * Get the unique identifier for this UI element
     * 
     * @return string The element ID (format: "id:type")
     */
    public function getId(): int;

    /**
     * Get the type of UI element (button, label, container, table, etc.)
     * 
     * @return string The element type
     */
    public function getType(): string;

    /**
     * Convert the UI element to its JSON representation
     * 
     * For leaf elements (Button, Label), this returns their configuration.
     * For composite elements (Container), this recursively calls toJson() on children.
     * 
     * @return array The JSON-serializable array representation
     */
    public function toJson(): array;

    /**
     * Get the visibility state of the element
     * 
     * @return bool True if visible, false otherwise
     */
    public function isVisible(): bool;

    /**
     * Set the visibility state of the element
     * 
     * @param bool $visible The visibility state
     * @return self For method chaining
     */
    public function setVisible(bool $visible): self;

    public function setName(?string $name): self;

    /**
     * Get the slot where this element should be rendered
     * 
     * @return int|string|null The slot (int = parent ID, string = parent name, null = delete)
     */
    public function getSlot(): int|string|null;

    /**
     * Set the slot where this element should be rendered
     * 
     * @param int|string|null $slot The slot (int = parent ID, string = parent name, null = delete)
     * @return self For method chaining
     */
    public function setSlot(int|string|null $slot): self;
}
