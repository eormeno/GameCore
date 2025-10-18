<?php

namespace App\Services\UI\Components;

/**
 * Builder for Table Row UI components
 * 
 * Represents a row in a table. This component must be associated with a Table
 * and will be automatically added to the table's rows container.
 */
class TableRowBuilder extends UIComponent
{
    /** @var TableBuilder The parent table */
    private TableBuilder $table;

    /**
     * Create a new table row
     * 
     * @param TableBuilder $table The parent table this row belongs to
     * @param string|null $name Optional name for the row
     */
    public function __construct(TableBuilder $table, ?string $name = null)
    {
        $this->table = $table;
        parent::__construct($name);
    }

    protected function getDefaultConfig(): array
    {
        return [
            'cells' => [],
            'selected' => false,
            'style' => 'default',
        ];
    }

    /**
     * Set the cells data for this row
     * 
     * @param array $cells Array of cell values
     * @return self For method chaining
     */
    public function cells(array $cells): self
    {
        return $this->setConfig('cells', $cells);
    }

    /**
     * Set a specific cell value by index
     * 
     * @param int $index The cell index (0-based)
     * @param mixed $value The cell value
     * @return self For method chaining
     */
    public function setCell(int $index, mixed $value): self
    {
        $this->config['cells'][$index] = $value;
        return $this;
    }

    /**
     * Mark this row as selected
     * 
     * @param bool $selected True to select, false otherwise
     * @return self For method chaining
     */
    public function selected(bool $selected = true): self
    {
        return $this->setConfig('selected', $selected);
    }

    /**
     * Set the row style
     * 
     * @param string $style The style name (default, primary, success, warning, danger, etc.)
     * @return self For method chaining
     */
    public function style(string $style): self
    {
        return $this->setConfig('style', $style);
    }

    /**
     * Get the parent table
     * 
     * @return TableBuilder
     */
    public function getTable(): TableBuilder
    {
        return $this->table;
    }
}
