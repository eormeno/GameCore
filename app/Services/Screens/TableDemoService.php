<?php

namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Traits\DataTableEventsTrait;
use App\Services\UI\DataTable\UsersDataTableModel;

/**
 * Table Demo Service
 * 
 * Demonstrates table functionality with:
 * - AbstractDataTableModel for data management
 * - Pagination handled by the model
 * - Edit and Remove action buttons
 * - Column width constraints
 * 
 * Version: 2.0 (with DataTableModel abstraction)
 */
class TableDemoService extends AbstractUIService
{
    use DataTableEventsTrait;
    
    private UsersDataTableModel $dataModel;

    /**
     * Get the data model instance
     * 
     * @return UsersDataTableModel
     */
    private function getDataModel(): UsersDataTableModel
    {
        if (!isset($this->dataModel)) {
            $this->dataModel = new UsersDataTableModel(2, 1); // 7 per page, start at page 1
        }
        return $this->dataModel;
    }

    /**
     * Get the data model for a specific table (required by DataTableEventsTrait)
     * 
     * @param string $tableName The table identifier
     * @return mixed|null The data model instance or null if not found
     */
    protected function getDataModelForTable(string $tableName)
    {
        // For this demo service, we only have one table: 'users_table'
        if ($tableName === 'users_table') {
            return $this->getDataModel();
        }
        
        return null;
    }

    /**
     * Build the table demo UI
     */
    protected function buildBaseUI(...$params): UIContainer
    {
        $container = UIBuilder::container('main')
            ->parent('main')
            ->layout(LayoutType::VERTICAL)
            ->title('Table Component Demo');

        // Create table with data model - everything is configured automatically
        $dataModel = $this->getDataModel();
        $table = UIBuilder::tableWithModel('users_table', $dataModel)
            ->title('Users Table')
            ->align('center')
            ->rowMinHeight(40); // Further reduced for more compact rows

        $container->add($table);

        return $container;
    }

    /**
     * Handle edit user action (legacy compatibility)
     * 
     * Maps to the generic onEditRow method with proper parameters.
     * 
     * @param array $params Action parameters
     * @return array UI updates
     */
    public function onEditUser(array $params): array
    {
        // Map legacy parameters to generic format
        $genericParams = [
            'table_name' => 'users_table',
            'id' => $params['user_id'] ?? null,
            'row' => $params['row'] ?? null,
            'data' => [
                'name' => ($params['name'] ?? 'Unknown') . ' [EDITED]'
            ]
        ];

        return $this->onEditRow($genericParams);
    }

    /**
     * Handle remove user action (legacy compatibility)
     * 
     * Maps to the generic onRemoveRow method with proper parameters.
     * 
     * @param array $params Action parameters
     * @return array UI updates
     */
    public function onRemoveUser(array $params): array
    {
        // Map legacy parameters to generic format
        $genericParams = [
            'table_name' => 'users_table',
            'id' => $params['user_id'] ?? null,
            'row' => $params['row'] ?? null,
            'description' => '[REMOVED]'
        ];

        return $this->onRemoveRow($genericParams);
    }

    /**
     * Handle page change action (legacy compatibility)
     * 
     * Maps to the generic onChangePage method with proper parameters.
     * 
     * @param array $params Action parameters with 'page' key
     * @return array UI updates
     */
    public function onChangePage(array $params): array
    {
        // Map legacy parameters to generic format
        $genericParams = [
            'table_name' => 'users_table',
            'page' => $params['page'] ?? 1
        ];

        return $this->onChangeTablePage($genericParams);
    }

    /**
     * Get the default row height for the users table
     * 
     * Override to ensure consistent row heights across all states
     * (data rows, removed rows, and empty rows).
     * 
     * @param string|null $tableName The table name
     * @return int The default minimum height in pixels
     */
    // protected function getDefaultRowHeight(?string $tableName = null): int
    // {
    //     if ($tableName === 'users_table') {
    //         return 40; // Further reduced height for more compact rows
    //     }
        
    //     // Call the trait's default implementation
    //     return 40; // Default height for any other table
    // }
}
