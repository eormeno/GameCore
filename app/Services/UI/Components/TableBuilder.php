<?php

namespace App\Services\UI\Components;

use App\Services\UI\Enums\TextAlign;
use App\Services\UI\Enums\FontWeight;
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

    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        
        // Create the rows container
        $this->rowsContainer = new UIContainer('rows');
        
        // Set the rows container's slot to this table's ID (parent-child relationship)
        $this->rowsContainer->setSlot($this->id);
        
        // Set the rows_container attribute to reference the container's ID
        $this->config['rows_container'] = $this->rowsContainer->getId();
    }

    protected function getDefaultConfig(): array
    {
        return [
            'title' => '',
            'headers' => [],
            'rows' => [],
            'pagination' => false,
            'min_rows' => null,
        ];
    }

    /**
     * Add a header to the table with optional configuration
     * 
     * @param string $text The header text
     * @param string|null $id Optional custom ID for the header
     * @param bool $sortable Whether the column is sortable
     * @param TextAlign $align Text alignment
     * @param string|null $width Column width (e.g., '200px')
     * @param FontWeight $fontWeight Font weight
     * @param string|null $color Text color
     * @param string|null $backgroundColor Background color
     * @param string|null $tooltip Tooltip text
     * @param string|null $sortDirection Initial sort direction
     * @return self For method chaining
     */
    public function addHeader(
        string $text,
        ?string $id = null,
        bool $sortable = false,
        TextAlign $align = TextAlign::CENTER,
        ?string $width = null,
        FontWeight $fontWeight = FontWeight::BOLD,
        ?string $color = null,
        ?string $backgroundColor = null,
        ?string $tooltip = null,
        ?string $sortDirection = null
    ): self {
        // Generate automatic ID if not provided
        $headerId = $id ?? strtolower(str_replace([' ', '(', ')', '-'], ['_', '', '', '_'], $text)) . '_header';
        
        // Build header config and filter out null values
        $headerData = [
            'visible' => true,
            'text' => $text,
            'sortable' => $sortable,
            'sort_direction' => $sortDirection,
            'width' => $width,
            'align' => $align->value,
            'color' => $color,
            'background_color' => $backgroundColor,
            'font_weight' => $fontWeight->value,
            'tooltip' => $tooltip,
        ];
        
        // Filter out null values
        $headerData = array_filter($headerData, fn($value) => $value !== null);
        
        // Remove 'visible' if it's true (default value)
        if (isset($headerData['visible']) && $headerData['visible'] === true) {
            unset($headerData['visible']);
        }
        
        $headerConfig = [
            $headerId . ':tableheader' => $headerData
        ];

        $this->config['headers'] = array_merge($this->config['headers'], $headerConfig);
        return $this;
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
     * Set all headers at once
     * 
     * @param array $headers Array of header configurations
     * @return self For method chaining
     */
    public function headers(array $headers): self
    {
        return $this->setConfig('headers', $headers);
    }

    /**
     * Set the table rows
     * 
     * @param array $rows Array of row data
     * @return self For method chaining
     */
    public function rows(array $rows): self
    {
        return $this->setConfig('rows', $rows);
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
     * 
     * @param string|null $name Optional name for the row
     * @return TableRowBuilder The new row builder
     */
    public function createRow(?string $name = null): TableRowBuilder
    {
        return new TableRowBuilder($this, $name);
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
     * Override toJson to include the rows container in flat structure
     * and automatically fill with empty rows if minRows is set
     */
    public function toJson(): array
    {
        // Auto-fill empty rows if minRows is set
        $this->autoFillEmptyRows();
        
        // Get the table's JSON (without the rows container)
        $tableJson = parent::toJson();
        
        // Get the rows container's JSON
        $rowsContainerJson = $this->rowsContainer->toJson();
        
        // Merge both at the same level (flat structure)
        return $tableJson + $rowsContainerJson;
    }

    /**
     * Automatically fill the table with empty rows if current row count is less than minRows
     * 
     * @return void
     */
    private function autoFillEmptyRows(): void
    {
        // Check if minRows is set
        $minRows = $this->config['min_rows'] ?? null;
        
        if ($minRows === null || $minRows <= 0) {
            return; // No minRows set, nothing to do
        }
        
        // Count current rows in the rows container
        $currentRowCount = count($this->rowsContainer->getChildren());
        
        // If we already have enough rows, do nothing
        if ($currentRowCount >= $minRows) {
            return;
        }
        
        // Calculate how many empty rows we need to add
        $emptyRowsNeeded = $minRows - $currentRowCount;
        
        // Count headers to determine how many cells per row
        $headerCount = count($this->config['headers'] ?? []);
        
        // Create empty cells array
        $emptyCells = array_fill(0, $headerCount, '');
        
        // Add empty rows
        for ($i = 0; $i < $emptyRowsNeeded; $i++) {
            $rowNumber = $currentRowCount + $i + 1;
            $emptyRow = new TableRowBuilder($this, "empty_row_$rowNumber");
            $emptyRow->cells($emptyCells)
                ->empty(true);
            
            $this->addRow($emptyRow);
        }
    }

    /**
     * Legacy build method for backward compatibility
     * Returns array format instead of object
     * 
     * @return array
     * @deprecated Use toJson() instead
     */
    public function build(): array
    {
        return $this->toJson();
    }
}