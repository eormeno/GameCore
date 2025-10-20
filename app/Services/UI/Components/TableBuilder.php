<?php

namespace App\Services\UI\Components;

use App\Services\UI\Support\UIIdGenerator;

/**
 * Builder for Table UI components
 * 
 * Tables are structured data display elements with headers and rows.
 * They support sorting, pagination, and custom styling.
 */
class TableBuilder extends UIComponent
{
    /** @var UIContainer The rows container */
    private UIContainer $rowsContainer;

    /** @var TableHeaderRowBuilder|null The header row (optional) */
    private ?TableHeaderRowBuilder $headerRow = null;

    /** @var bool Flag to prevent multiple auto-fill calls */
    private bool $autoFillCompleted = false;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        
        // Create the rows container
        $this->rowsContainer = new UIContainer('rows');
        
        // Set the rows container's parent to this table's ID (parent-child relationship)
        $this->rowsContainer->setParent($this->id);
        
        // Set the rows_container attribute to reference the container's ID
        $this->config['rows_container'] = $this->rowsContainer->getId();
    }

    protected function getDefaultConfig(): array
    {
        return [
            'title' => '',
            'header_row' => null,
            'pagination' => false,
            'min_rows' => null,
        ];
    }

    /**
     * Create and return a header row for this table
     * Only one header row is allowed per table
     * 
     * @param string|null $name Optional name for the header row
     * @return TableHeaderRowBuilder The header row builder
     */
    public function createHeaderRow(?string $name = null): TableHeaderRowBuilder
    {
        if ($this->headerRow !== null) {
            throw new \LogicException("Table already has a header row. Only one header row is allowed per table.");
        }

        $this->headerRow = new TableHeaderRowBuilder($this, $name ?? 'header');
        $this->headerRow->setParent($this->id);
        $this->config['header_row'] = $this->headerRow->getId();
        
        return $this->headerRow;
    }

    /**
     * Get the header row if it exists
     * 
     * @return TableHeaderRowBuilder|null
     */
    public function getHeaderRow(): ?TableHeaderRowBuilder
    {
        return $this->headerRow;
    }

    /**
     * Set the table title
     * 
     * @param string $title The table title
     * @return self For method chaining
     */
    public function title(string $title): self
    {
        return $this->setConfig('title', $title);
    }

    /**
     * Enable or disable pagination
     * 
     * @param bool $pagination True to enable pagination
     * @return self For method chaining
     */
    public function pagination(bool $pagination = true): self
    {
        return $this->setConfig('pagination', $pagination);
    }

    /**
     * Set minimum number of rows to display (fills with empty rows if needed)
     * 
     * @param int $minRows Minimum number of rows
     * @return self For method chaining
     */
    public function minRows(int $minRows): self
    {
        return $this->setConfig('min_rows', $minRows);
    }

    /**
     * Create a new table row associated with this table
     * Automatically adds the row to the table
     * 
     * @param string|null $name Optional name for the row
     * @return TableRowBuilder The new row builder
     */
    public function createRow(?string $name = null): TableRowBuilder
    {
        $row = new TableRowBuilder($this, $name);
        $this->addRow($row);
        return $row;
    }

    /**
     * Add a row component to this table
     * 
     * @param TableRowBuilder $row The row to add
     * @return self For method chaining
     */
    public function addRow(TableRowBuilder $row): self
    {
        // Add the row to the rows container
        $this->rowsContainer->add($row);
        return $this;
    }

    /**
     * Add multiple row components to this table
     * 
     * @param array<TableRowBuilder> $rows Array of rows to add
     * @return self For method chaining
     */
    public function addRows(array $rows): self
    {
        foreach ($rows as $row) {
            if ($row instanceof TableRowBuilder) {
                $this->addRow($row);
            }
        }
        return $this;
    }

    /**
     * Get the rows container
     * 
     * @return UIContainer
     */
    public function getRowsContainer(): UIContainer
    {
        return $this->rowsContainer;
    }

    /**
     * {@inheritDoc}
     * 
     * Override toJson to include the rows container and header row in flat structure
     * and automatically fill with empty rows if minRows is set
     */
    public function toJson(): array
    {
        // Auto-fill empty rows if minRows is set
        $this->autoFillEmptyRows();
        
        // Get the table's JSON (without the rows container and header row)
        $tableJson = parent::toJson();
        
        // Get the rows container's JSON
        $rowsContainerJson = $this->rowsContainer->toJson();
        
        // Start with table + rows container
        $result = $tableJson + $rowsContainerJson;
        
        // Add header row if it exists
        if ($this->headerRow !== null) {
            $headerRowJson = $this->headerRow->toJson();
            $result = $result + $headerRowJson;
        }
        
        return $result;
    }

    /**
     * Automatically fill the table with empty rows if current row count is less than minRows
     * 
     * @return void
     */
    private function autoFillEmptyRows(): void
    {
        // Prevent multiple calls
        if ($this->autoFillCompleted) {
            return;
        }
        
        // Check if minRows is set
        $minRows = $this->config['min_rows'] ?? null;
        
        if ($minRows === null || $minRows <= 0) {
            $this->autoFillCompleted = true;
            return; // No minRows set, nothing to do
        }
        
        // Count current rows in the rows container
        $currentRowCount = count($this->rowsContainer->getChildren());
        
        // If we already have enough rows, do nothing
        if ($currentRowCount >= $minRows) {
            $this->autoFillCompleted = true;
            return;
        }
        
        // Calculate how many empty rows we need to add
        $emptyRowsNeeded = $minRows - $currentRowCount;
        
        // Count header cells to determine how many cells per row
        $headerCount = 0;
        if ($this->headerRow !== null) {
            $headerCount = count($this->headerRow->getCells());
        }
        
        // Add empty rows with empty cells
        for ($i = 0; $i < $emptyRowsNeeded; $i++) {
            $rowNumber = $currentRowCount + $i + 1;
            $emptyRow = new TableRowBuilder($this, "empty_row_$rowNumber");
            $emptyRow->empty(true);
            
            // Add empty cells to the row
            for ($j = 0; $j < $headerCount; $j++) {
                $emptyRow->createCell()->text('');
            }
            
            $this->addRow($emptyRow);
        }
        
        $this->autoFillCompleted = true;
    }

}
