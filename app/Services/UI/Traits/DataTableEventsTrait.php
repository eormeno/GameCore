<?php

namespace App\Services\UI\Traits;

/**
 * Data Table Events Trait
 * 
 * Provides generic event handling for data table operations:
 * - onEditRow: Handle row editing with data updates
 * - onRemoveRow: Handle row removal with visual feedback
 * 
 * This trait can be used by any service that manages data tables,
 * supporting multiple tables per page through table_name parameter.
 */
trait DataTableEventsTrait
{
    /**
     * Handle edit row action for any data table
     * 
     * @param array $params Action parameters:
     *   - table_name: string - Name/ID of the table
     *   - id: mixed - Row identifier (user_id, product_id, etc.)
     *   - data: array - New data to update the row with
     *   - row: int - Row index for UI updates (optional)
     * @return array UI updates
     */
    public function onEditRow(array $params): array
    {
        $tableName = $params['table_name'] ?? null;
        $rowId = $params['id'] ?? null;
        $newData = $params['data'] ?? [];
        $row = $params['row'] ?? null;

        if (!$tableName || $rowId === null) {
            return [];
        }

        // Get the data model for this table
        $dataModel = $this->getDataModelForTable($tableName);
        if (!$dataModel) {
            return [];
        }

        // Update the data in the model
        $this->updateRowInModel($dataModel, $rowId, $newData);

        // Generate UI updates for the modified row
        return $this->generateRowUpdateResponse($tableName, $dataModel, $rowId, $row, $newData);
    }

    /**
     * Handle remove row action for any data table
     * 
     * @param array $params Action parameters:
     *   - table_name: string - Name/ID of the table
     *   - id: mixed - Row identifier to remove
     *   - description: string - Optional description for removal feedback
     *   - row: int - Row index for UI updates (optional)
     * @return array UI updates
     */
    public function onRemoveRow(array $params): array
    {
        $tableName = $params['table_name'] ?? null;
        $rowId = $params['id'] ?? null;
        $description = $params['description'] ?? '[REMOVED]';
        $row = $params['row'] ?? null;

        if (!$tableName || $rowId === null) {
            return [];
        }

        // Get the data model for this table
        $dataModel = $this->getDataModelForTable($tableName);
        if (!$dataModel) {
            return [];
        }

        // Remove the data from the model
        $this->removeRowFromModel($dataModel, $rowId);

        // Generate UI updates for the removed row
        return $this->generateRowRemovalResponse($tableName, $dataModel, $rowId, $row, $description);
    }

    /**
     * Get the data model instance for a specific table
     * 
     * Services using this trait should implement this method to return
     * the appropriate data model for the given table name.
     * 
     * @param string $tableName The table identifier
     * @return mixed|null The data model instance or null if not found
     */
    abstract protected function getDataModelForTable(string $tableName);

    /**
     * Update a row in the data model
     * 
     * Default implementation assumes the model has an updateRow method.
     * Services can override this for custom update logic.
     * 
     * @param mixed $dataModel The data model instance
     * @param mixed $rowId The row identifier
     * @param array $newData The new data to update
     * @return void
     */
    protected function updateRowInModel($dataModel, $rowId, array $newData): void
    {
        if (method_exists($dataModel, 'updateUser')) {
            // Legacy compatibility for UsersDataTableModel
            $dataModel->updateUser($rowId, $newData);
        } elseif (method_exists($dataModel, 'updateRow')) {
            $dataModel->updateRow($rowId, $newData);
        }
    }

    /**
     * Remove a row from the data model
     * 
     * Default implementation assumes the model has a removeRow method.
     * Services can override this for custom removal logic.
     * 
     * @param mixed $dataModel The data model instance
     * @param mixed $rowId The row identifier
     * @return void
     */
    protected function removeRowFromModel($dataModel, $rowId): void
    {
        if (method_exists($dataModel, 'removeUser')) {
            // Legacy compatibility for UsersDataTableModel
            $dataModel->removeUser($rowId);
        } elseif (method_exists($dataModel, 'removeRow')) {
            $dataModel->removeRow($rowId);
        }
    }

    /**
     * Generate UI update response for a modified row
     * 
     * @param string $tableName The table identifier
     * @param mixed $dataModel The data model instance
     * @param mixed $rowId The row identifier
     * @param int|null $row The row index
     * @param array $newData The updated data
     * @return array UI updates
     */
    protected function generateRowUpdateResponse(string $tableName, $dataModel, $rowId, ?int $row, array $newData): array
    {
        if ($row === null || !method_exists($dataModel, 'getPerPage')) {
            return [];
        }

        $storedUI = $this->getStoredUI();
        $perPage = $dataModel->getPerPage();
        
        // Calculate the row position on current page
        $pageRow = $row % $perPage;
        
        $result = [];

        // Update cells based on the new data
        // This is a generic approach - services can override for specific needs
        foreach ($newData as $key => $value) {
            $cellName = $this->getCellNameForData($pageRow, $key, $dataModel);
            if ($cellName) {
                $this->updateCellInResponse($storedUI, $result, $cellName, $value);
            }
        }

        return $result;
    }

    /**
     * Generate UI update response for a removed row
     * 
     * @param string $tableName The table identifier
     * @param mixed $dataModel The data model instance
     * @param mixed $rowId The row identifier
     * @param int|null $row The row index
     * @param string $description The removal description
     * @return array UI updates
     */
    protected function generateRowRemovalResponse(string $tableName, $dataModel, $rowId, ?int $row, string $description): array
    {
        if ($row === null || !method_exists($dataModel, 'getPerPage')) {
            return [];
        }

        $storedUI = $this->getStoredUI();
        $perPage = $dataModel->getPerPage();
        
        // Calculate the row position on current page
        $pageRow = $row % $perPage;
        
        $result = [];

        // Get column count to clear all cells in the row
        $columnCount = $this->getColumnCountForTable($tableName, $dataModel);
        
        // Get removal values using model configuration
        $removalValues = $this->getRemovalValuesForRow($columnCount, $description, $dataModel);
        
        // Update all cells in the row
        for ($col = 0; $col < $columnCount; $col++) {
            $cellName = "{$pageRow}_{$col}";
            $value = $removalValues[$col] ?? '-';
            $this->updateCellInResponse($storedUI, $result, $cellName, $value, $tableName);
        }

        return $result;
    }

    /**
     * Get the cell name for a specific data field
     * 
     * Services can override this to map data fields to cell positions.
     * Default implementation maps common field names.
     * 
     * @param int $pageRow The row index on the current page
     * @param string $dataKey The data field key
     * @param mixed $dataModel The data model instance
     * @return string|null The cell name or null if not mappable
     */
    protected function getCellNameForData(int $pageRow, string $dataKey, $dataModel): ?string
    {
        // Default mapping for common fields
        $fieldMapping = [
            'name' => 1,     // Column 1 typically contains name
            'title' => 1,    // Alternative for name
            'email' => 2,    // Column 2 might be email
            'country' => 2,  // Or country
            'status' => 3,   // Column 3 might be status
        ];

        $columnIndex = $fieldMapping[$dataKey] ?? null;
        
        return $columnIndex !== null ? "{$pageRow}_{$columnIndex}" : null;
    }

    /**
     * Get the number of columns for a table
     * 
     * @param string $tableName The table identifier
     * @param mixed $dataModel The data model instance
     * @return int The column count
     */
    protected function getColumnCountForTable(string $tableName, $dataModel): int
    {
        if (method_exists($dataModel, 'getColumns')) {
            return count($dataModel->getColumns());
        }
        
        // Default fallback
        return 5;
    }

    /**
     * Get removal values for all columns in a row
     * 
     * Uses the data model's removal configuration if available,
     * otherwise falls back to the provided description.
     * 
     * @param int $columnCount The number of columns
     * @param string $description The removal description (fallback)
     * @param mixed $dataModel The data model instance
     * @return array Values for each column
     */
    protected function getRemovalValuesForRow(int $columnCount, string $description, $dataModel = null): array
    {
        // Try to get removal values from the data model
        if ($dataModel && method_exists($dataModel, 'getRemovalValues')) {
            return $dataModel->getRemovalValues($columnCount);
        }

        // Fallback to default behavior with description
        $values = [];
        for ($i = 0; $i < $columnCount; $i++) {
            if ($i === 0) {
                $values[$i] = '-'; // ID column
            } elseif ($i === 1) {
                $values[$i] = $description; // Main content column
            } else {
                $values[$i] = '-'; // Other columns
            }
        }
        return $values;
    }

    /**
     * Handle page change action for any data table
     * 
     * @param array $params Action parameters:
     *   - table_name: string - Name/ID of the table
     *   - page: int - Target page number
     * @return array UI updates
     */
    public function onChangeTablePage(array $params): array
    {
        $tableName = $params['table_name'] ?? null;
        $page = $params['page'] ?? 1;

        if (!$tableName) {
            return [];
        }

        // Get the data model for this table
        $dataModel = $this->getDataModelForTable($tableName);
        if (!$dataModel) {
            return [];
        }

        // Update data model with new page
        if (method_exists($dataModel, 'setCurrentPage')) {
            $dataModel->setCurrentPage($page);
        }

        // Get formatted data for the new page
        if (!method_exists($dataModel, 'getFormattedPageData')) {
            return [];
        }

        $formattedData = $dataModel->getFormattedPageData();
        $storedUI = $this->getStoredUI();
        $result = [];
        
        // Update data cells
        $row = 0;
        foreach ($formattedData as $rowData) {
            if ($row >= $this->getPerPageForTable($tableName, $dataModel)) {
                break;
            }

            // Update each cell in the row using column mapping
            $this->updateRowCells($storedUI, $result, $row, $rowData, $dataModel, $tableName);
            $row++;
        }

        // Clear remaining rows if less than perPage
        $totalRows = $this->getPerPageForTable($tableName, $dataModel);
        $columnCount = $this->getColumnCountForTable($tableName, $dataModel);
        $this->clearRemainingRows($storedUI, $result, $row, $totalRows, $columnCount, $tableName);

        return $result;
    }

    /**
     * Update all cells in a row with new data
     * 
     * @param array $storedUI The stored UI components
     * @param array &$result The result array to update
     * @param int $row The row index
     * @param array $rowData The new row data
     * @param mixed $dataModel The data model instance
     * @param string|null $tableName The table name for height consistency
     * @return void
     */
    protected function updateRowCells(array $storedUI, array &$result, int $row, array $rowData, $dataModel, ?string $tableName = null): void
    {
        // Get column mapping for this data model
        $columnMapping = $this->getColumnMappingForModel($dataModel);
        
        foreach ($rowData as $fieldName => $value) {
            $columnIndex = $columnMapping[$fieldName] ?? null;
            
            if ($columnIndex !== null) {
                $cellName = "{$row}_{$columnIndex}";
                
                if (is_array($value) && isset($value['button'])) {
                    // Handle button cells
                    $this->updateButtonCell($storedUI, $result, $cellName, $value, $tableName);
                } else {
                    // Handle text cells
                    $this->updateCellInResponse($storedUI, $result, $cellName, $value, $tableName);
                }
            }
        }
    }

    /**
     * Update a button cell in the result array
     * 
     * @param array $storedUI The stored UI components
     * @param array &$result The result array to update
     * @param string $cellName The cell identifier
     * @param array $buttonData The button configuration
     * @param string|null $tableName The table name for height consistency
     * @return void
     */
    protected function updateButtonCell(array $storedUI, array &$result, string $cellName, array $buttonData, ?string $tableName = null): void
    {
        foreach ($storedUI as $id => $component) {
            if ($component['type'] === 'tablecell' && 
                isset($component['name']) && 
                $component['name'] === $cellName) {
                
                $cellUpdate = [
                    'type' => 'tablecell',
                    'button' => $buttonData['button'],
                    '_id' => $id,
                ];

                // Preserve height consistency for button cells too
                $this->preserveHeightProperties($component, $cellUpdate, $tableName);
                
                $result[$id] = $cellUpdate;
                break;
            }
        }
    }

    /**
     * Clear remaining empty rows
     * 
     * @param array $storedUI The stored UI components
     * @param array &$result The result array to update
     * @param int $startRow Starting row index to clear
     * @param int $totalRows Total number of rows in the table
     * @param int $columnCount Number of columns in the table
     * @param string|null $tableName The table name for height consistency
     * @return void
     */
    protected function clearRemainingRows(array $storedUI, array &$result, int $startRow, int $totalRows, int $columnCount, ?string $tableName = null): void
    {
        for ($i = $startRow; $i < $totalRows; $i++) {
            for ($col = 0; $col < $columnCount; $col++) {
                $cellName = "{$i}_{$col}";
                $this->updateCellInResponse($storedUI, $result, $cellName, '', $tableName);
            }
        }
    }

    /**
     * Get per-page count for a table
     * 
     * @param string $tableName The table identifier
     * @param mixed $dataModel The data model instance
     * @return int The per-page count
     */
    protected function getPerPageForTable(string $tableName, $dataModel): int
    {
        if (method_exists($dataModel, 'getPerPage')) {
            return $dataModel->getPerPage();
        }
        
        // Default fallback
        return 10;
    }

    /**
     * Get column mapping for a data model
     * 
     * Maps data field names to column indices for UI updates.
     * Services can override this for custom mapping.
     * 
     * @param mixed $dataModel The data model instance
     * @return array Field name to column index mapping
     */
    protected function getColumnMappingForModel($dataModel): array
    {
        // Default mapping for common fields
        return [
            'id' => 0,
            'name' => 1,
            'title' => 1,
            'country' => 2,
            'category' => 2,
            'email' => 2,
            'actions' => 3,
            'status' => 3,
            'remove' => 4,
            'delete' => 4,
        ];
    }

    /**
     * Update a cell in the UI response
     * 
     * @param array $storedUI The stored UI components
     * @param array &$result The result array to update
     * @param string $cellName The cell identifier
     * @param mixed $value The new value
     * @param string $tableName The table name (optional, for height consistency)
     * @return void
     */
    protected function updateCellInResponse(array $storedUI, array &$result, string $cellName, $value, string $tableName = null): void
    {
        foreach ($storedUI as $id => $component) {
            if ($component['type'] === 'tablecell' && 
                isset($component['name']) && 
                $component['name'] === $cellName) {
                
                $cellUpdate = [
                    'type' => 'tablecell',
                    'text' => (string)$value,
                    '_id' => $id,
                ];

                // Preserve height consistency - maintain original cell height properties
                $this->preserveHeightProperties($component, $cellUpdate, $tableName);
                
                $result[$id] = $cellUpdate;
                break;
            }
        }
    }

    /**
     * Preserve height properties from the original cell to maintain consistent row heights
     * 
     * @param array $originalComponent The original cell component
     * @param array &$cellUpdate The cell update being prepared
     * @param string|null $tableName The table name for context
     * @return void
     */
    protected function preserveHeightProperties(array $originalComponent, array &$cellUpdate, ?string $tableName = null): void
    {
        // Preserve minimum height if it was set
        if (isset($originalComponent['min_height'])) {
            $cellUpdate['min_height'] = $originalComponent['min_height'];
        }
        
        // Preserve height if it was set
        if (isset($originalComponent['height'])) {
            $cellUpdate['height'] = $originalComponent['height'];
        }
        
        // Preserve padding for compact cells
        if (isset($originalComponent['padding'])) {
            $cellUpdate['padding'] = $originalComponent['padding'];
        }
        
        // Preserve any other height-related properties
        $heightProperties = ['max_height', 'line_height', 'padding_top', 'padding_bottom'];
        foreach ($heightProperties as $property) {
            if (isset($originalComponent[$property])) {
                $cellUpdate[$property] = $originalComponent[$property];
            }
        }

        // If no height properties exist, apply default minimum height for consistency
        if (!isset($cellUpdate['min_height']) && !isset($cellUpdate['height'])) {
            $defaultMinHeight = $this->getDefaultRowHeight($tableName);
            if ($defaultMinHeight > 0) {
                $cellUpdate['min_height'] = $defaultMinHeight;
            }
        }
    }

    /**
     * Get the default row height for a table
     * 
     * Services can override this to provide table-specific default heights.
     * 
     * @param string|null $tableName The table name
     * @return int The default minimum height in pixels
     */
    protected function getDefaultRowHeight(?string $tableName = null): int
    {
        // Default minimum height for all tables
        return 30; // pixels
    }
}