<?php

namespace App\Services\UI\Components;

/**
 * Table Builder
 * 
 * A table with fixed dimensions (rows × columns) where:
 * - Structure is defined upfront
 * - All cells are initially empty
 * - Cells are identified by (row, col) coordinates
 * - Cell names follow pattern: "{row}_{col}"
 */
class TableBuilder extends UIComponent
{
    /** @var UIContainer The rows container */
    private UIContainer $rowsContainer;

    /** @var TableHeaderRowBuilder|null The header row (optional) */
    private ?TableHeaderRowBuilder $headerRow = null;

    /** @var int Number of data rows (excluding header) */
    private int $rows;

    /** @var int Number of columns */
    private int $cols;

    /** @var array Matrix of cell builders [row][col] */
    private array $cells = [];

    /** @var array Array of row builders */
    private array $rowBuilders = [];

    /**
     * Create a new table
     * 
     * @param string|null $name Table name
     * @param int $rows Number of data rows (0 for dynamic)
     * @param int $cols Number of columns (0 for dynamic)
     */
    public function __construct(?string $name = null, int $rows = 0, int $cols = 0)
    {
        parent::__construct($name);
        
        // Create the rows container
        $this->rowsContainer = new UIContainer('rows');
        $this->rowsContainer->setParent($this->id);
        $this->config['rows_container'] = $this->rowsContainer->getId();
        
        $this->rows = $rows;
        $this->cols = $cols;
        
        $this->setConfig('rows', $rows);
        $this->setConfig('cols', $cols);
        
        // Initialize empty cells if dimensions are provided
        if ($rows > 0 && $cols > 0) {
            $this->initializeEmptyCells();
        }
    }

    protected function getDefaultConfig(): array
    {
        return [
            'title' => '',
            'header_row' => null,
            'pagination' => false,
            'rows' => 0,
            'cols' => 0,
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
     * Get the rows container
     * 
     * @return UIContainer
     */
    public function getRowsContainer(): UIContainer
    {
        return $this->rowsContainer;
    }

    /**
     * Set table dimensions and initialize empty cells
     * 
     * @param int $rows Number of data rows
     * @param int $cols Number of columns
     * @return self
     */
    public function dimensions(int $rows, int $cols): self
    {
        $this->rows = $rows;
        $this->cols = $cols;
        
        $this->setConfig('rows', $rows);
        $this->setConfig('cols', $cols);
        
        $this->initializeEmptyCells();
        
        return $this;
    }

    /**
     * Initialize all cells as empty
     */
    private function initializeEmptyCells(): void
    {
        // Create header row
        $headerRow = $this->createHeaderRow('header');
        
        // Create empty header cells with column index
        for ($col = 0; $col < $this->cols; $col++) {
            $headerRow->createCell("header_$col")->text('')->column($col);
        }
        
        // Create data rows with empty cells
        for ($row = 0; $row < $this->rows; $row++) {
            $rowBuilder = $this->createRow("row_$row");
            $rowBuilder->row($row); // Set row index for ordering
            $this->rowBuilders[$row] = $rowBuilder;
            
            // Create empty cells for this row with column index
            $this->cells[$row] = [];
            for ($col = 0; $col < $this->cols; $col++) {
                $cellName = "{$row}_{$col}";
                $cell = $rowBuilder->createCell($cellName);
                $cell->text('')->column($col); // Empty by default with column index
                $this->cells[$row][$col] = $cell;
            }
        }
    }

    /**
     * Fill the header row with data
     * 
     * @param array $data Array of header values (strings)
     * @return self
     */
    public function fillHeaderRow(array $data): self
    {
        $headerRow = $this->getHeaderRow();
        
        if (!$headerRow) {
            throw new \LogicException("Table dimensions must be set before filling header row");
        }
        
        $cells = $headerRow->getCells();
        
        for ($col = 0; $col < min(count($data), $this->cols); $col++) {
            if (isset($cells[$col])) {
                $cells[$col]->text($data[$col]);
            }
        }
        
        return $this;
    }

    /**
     * Clear all data rows (set all cells to empty strings)
     * This is useful for pagination or when reloading data
     * 
     * @return self
     */
    public function clearRows(): self
    {
        for ($row = 0; $row < $this->rows; $row++) {
            for ($col = 0; $col < $this->cols; $col++) {
                $this->cells[$row][$col]->text('');
            }
        }
        
        return $this;
    }

    /**
     * Fill a data row with values
     * 
     * @param int $row Row index (0-based)
     * @param array $data Array of cell data
     *                    - string: text content
     *                    - array with 'text': text content
     *                    - array with 'button': button config
     *                    - array with 'url_image': image config
     * @return self
     */
    public function fillRow(int $row, array $data): self
    {
        if ($row < 0 || $row >= $this->rows) {
            throw new \OutOfBoundsException("Row index $row is out of bounds (0-" . ($this->rows - 1) . ")");
        }
        
        for ($col = 0; $col < min(count($data), $this->cols); $col++) {
            $value = $data[$col];
            $cell = $this->cells[$row][$col];
            
            if (is_string($value)) {
                // Simple text
                $cell->text($value);
            } elseif (is_array($value)) {
                if (isset($value['text'])) {
                    $cell->text($value['text']);
                } elseif (isset($value['button'])) {
                    $cell->button($value['button']);
                } elseif (isset($value['url_image'])) {
                    $cell->urlImage(
                        $value['url_image'],
                        $value['alt'] ?? null,
                        $value['width'] ?? null,
                        $value['height'] ?? null
                    );
                }
            }
        }
        
        return $this;
    }

    /**
     * Get the cell ID for a specific row and column
     * 
     * Format: tableId_row_col
     * Example: 88001_0_1 (table 88001, row 0, col 1)
     * 
     * @param int $row Row index (0-based)
     * @param int $col Column index (0-based)
     * @return int Cell ID
     */
    public function getCellId(int $row, int $col): int
    {
        if ($row < 0 || $row >= $this->rows) {
            throw new \OutOfBoundsException("Row index $row is out of bounds");
        }
        
        if ($col < 0 || $col >= $this->cols) {
            throw new \OutOfBoundsException("Column index $col is out of bounds");
        }
        
        return $this->cells[$row][$col]->getId();
    }

    /**
     * Get a specific cell builder
     * 
     * @param int $row Row index (0-based)
     * @param int $col Column index (0-based)
     * @return TableCellBuilder
     */
    public function getCell(int $row, int $col): TableCellBuilder
    {
        if ($row < 0 || $row >= $this->rows) {
            throw new \OutOfBoundsException("Row index $row is out of bounds");
        }
        
        if ($col < 0 || $col >= $this->cols) {
            throw new \OutOfBoundsException("Column index $col is out of bounds");
        }
        
        return $this->cells[$row][$col];
    }

    /**
     * Set the table title
     * 
     * @param string $title The table title
     * @return self
     */
    public function title(string $title): self
    {
        return $this->setConfig('title', $title);
    }

    /**
     * Set minimum height for all rows
     * 
     * @param int $height Minimum height in pixels
     * @return self
     */
    public function rowMinHeight(int $height): self
    {
        // Apply min height to all existing rows
        foreach ($this->rowBuilders as $row) {
            $row->minHeight($height);
        }
        
        return $this;
    }

    /**
     * Enable or disable pagination
     * 
     * @param bool $pagination True to enable pagination
     * @return self
     */
    public function pagination(bool $pagination = true): self
    {
        return $this->setConfig('pagination', $pagination);
    }

    /**
     * Get table dimensions
     * 
     * @return array ['rows' => int, 'cols' => int]
     */
    public function getDimensions(): array
    {
        return [
            'rows' => $this->rows,
            'cols' => $this->cols,
        ];
    }

    /**
     * Override toJson to include the rows container and header row in flat structure
     */
    public function toJson(?int $order = null): array
    {
        // Get the table's JSON
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

    protected function getExcludedJsonKeys(): array
    {
        // Don't exclude 'name' - we need it for cell identification
        return parent::getExcludedJsonKeys();
    }
}
