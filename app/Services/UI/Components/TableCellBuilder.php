<?php

namespace App\Services\UI\Components;

use App\Services\UI\Enums\Align;

/**
 * Builder for Table Cell UI components
 * 
 * Represents a cell in a table row. This component must be associated with a TableRow
 * and can contain either simple text or a single child component.
 */
class TableCellBuilder extends UIComponent
{
    /** @var TableRowBuilder The parent row */
    private TableRowBuilder $row;

    /** @var UIComponent|null Optional child component */
    private ?UIComponent $child = null;

    /**
     * Create a new table cell
     * 
     * @param TableRowBuilder $row The parent row this cell belongs to
     * @param string|null $name Optional name for the cell
     */
    public function __construct(TableRowBuilder $row, ?string $name = null)
    {
        $this->row = $row;
        parent::__construct($name);
    }

    protected function getDefaultConfig(): array
    {
        return [
            'text' => null,
            'align' => null,
        ];
    }

    /**
     * Set simple text content for the cell
     * 
     * @param string|int|float|null $text The text content
     * @return self For method chaining
     */
    public function text(string|int|float|null $text): self
    {
        return $this->setConfig('text', $text);
    }

    /**
     * Set horizontal alignment for the cell content
     * 
     * @param Align $align The alignment (left, center, right)
     * @return self For method chaining
     */
    public function align(Align $align): self
    {
        return $this->setConfig('align', $align->value);
    }

    /**
     * Add a child component to this cell
     * Only one child component is allowed per cell
     * Note: Containers are not allowed as children to prevent recursion issues
     * 
     * @param UIComponent $component The component to add
     * @return self For method chaining
     */
    public function addChild(UIComponent $component): self
    {
        if ($this->child !== null) {
            throw new \LogicException("TableCell can only contain one child component. Use a container if you need multiple components.");
        }

        // Prevent adding containers to avoid recursion/loops
        if ($component instanceof UIContainer) {
            throw new \LogicException("TableCell cannot contain a UIContainer. Containers should be outside the table structure.");
        }

        $this->child = $component;
        $component->setSlot($this->id);
        return $this;
    }

    /**
     * Get the parent row
     * 
     * @return TableRowBuilder
     */
    public function getRow(): TableRowBuilder
    {
        return $this->row;
    }

    /**
     * Get the child component if any
     * 
     * @return UIComponent|null
     */
    public function getChild(): ?UIComponent
    {
        return $this->child;
    }

    /**
     * {@inheritDoc}
     * 
     * Includes the child component in the flat JSON structure
     */
    public function toJson(): array
    {
        // Get base config and filter nulls
        $config = array_filter($this->config, fn($value) => $value !== null);

        // Remove 'visible' if it's true (default value)
        if (isset($config['visible']) && $config['visible'] === true) {
            unset($config['visible']);
        }

        // Remove 'align' if it's 'left' (default value)
        if (isset($config['align']) && $config['align'] === 'left') {
            unset($config['align']);
        }

        // Exclude additional keys
        $excludeKeys = $this->getExcludedJsonKeys();
        if (!empty($excludeKeys)) {
            $config = array_diff_key($config, array_flip($excludeKeys));
        }

        // Start with this cell
        $result = [$this->id => $config];

        // Add child component if present
        if ($this->child !== null) {
            $childJson = $this->child->toJson();
            $result = $result + $childJson;
        }

        return $result;
    }

    /**
     * Exclude 'name' from JSON output
     * 
     * @return array List of keys to exclude
     */
    protected function getExcludedJsonKeys(): array
    {
        return ['name'];
    }
}
