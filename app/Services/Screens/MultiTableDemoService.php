<?php

namespace App\Services\Screens;

use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\DataTable\UsersDataTableModel;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\UIBuilder;
use App\Traits\DataTableEventsTrait;

/**
 * Multi-Table Demo Service
 * 
 * Demonstrates how to use DataTableEventsTrait with multiple tables
 * in the same service/page. Shows the power of the table_name parameter
 * for distinguishing between different tables.
 * 
 * This service manages:
 * - users_table: List of users
 * - products_table: List of products (simulated)
 */
class MultiTableDemoService extends AbstractUIService
{
    use DataTableEventsTrait;
    
    private ?UsersDataTableModel $usersModel = null;
    private ?UsersDataTableModel $productsModel = null; // Using same model for demo

    /**
     * Build the multi-table demo UI
     */
    protected function buildBaseUI(...$params): UIContainer
    {
        $container = UIBuilder::container('main')
            ->parent('main')
            ->layout(LayoutType::VERTICAL)
            ->title('Multi-Table Demo');

        // First table: Users
        $usersModel = $this->getUsersModel();
        $usersTable = UIBuilder::tableWithModel('users_table', $usersModel)
            ->title('Users Management')
            ->align('center')
            ->rowMinHeight(20);

        $container->add($usersTable);

        // Second table: Products (using same model for demo purposes)
        $productsModel = $this->getProductsModel();
        $productsTable = UIBuilder::tableWithModel('products_table', $productsModel)
            ->title('Products Management')
            ->align('center')
            ->rowMinHeight(20);

        $container->add($productsTable);

        return $container;
    }

    /**
     * Get the users data model
     * 
     * @return UsersDataTableModel
     */
    private function getUsersModel(): UsersDataTableModel
    {
        if (!$this->usersModel) {
            $this->usersModel = new UsersDataTableModel(3, 1); // 3 users per page
        }
        return $this->usersModel;
    }

    /**
     * Get the products data model (simulated)
     * 
     * @return UsersDataTableModel
     */
    private function getProductsModel(): UsersDataTableModel
    {
        if (!$this->productsModel) {
            $this->productsModel = new UsersDataTableModel(4, 1); // 4 products per page
        }
        return $this->productsModel;
    }

    /**
     * Get the data model for a specific table (required by DataTableEventsTrait)
     * 
     * This method enables the trait to handle multiple tables by routing
     * to the appropriate data model based on table_name.
     * 
     * @param string $tableName The table identifier
     * @return mixed|null The data model instance or null if not found
     */
    protected function getDataModelForTable(string $tableName)
    {
        switch ($tableName) {
            case 'users_table':
                return $this->getUsersModel();
                
            case 'products_table':
                return $this->getProductsModel();
                
            default:
                return null; // Unknown table
        }
    }

    /**
     * Example of generic row editing for any table
     * 
     * This method can handle edits for both users_table and products_table
     * thanks to the DataTableEventsTrait.
     * 
     * @param array $params Parameters with table_name, id, and data
     * @return array UI updates
     */
    public function editAnyRow(array $params): array
    {
        // The trait handles everything automatically based on table_name
        return $this->onEditRow($params);
    }

    /**
     * Example of generic row removal for any table
     * 
     * @param array $params Parameters with table_name, id, and description
     * @return array UI updates
     */
    public function removeAnyRow(array $params): array
    {
        // The trait handles everything automatically based on table_name
        return $this->onRemoveRow($params);
    }

    /**
     * Example: Edit a user specifically
     * 
     * @param array $params
     * @return array
     */
    public function editUser(array $params): array
    {
        return $this->onEditRow([
            'table_name' => 'users_table',
            'id' => $params['user_id'] ?? null,
            'row' => $params['row'] ?? null,
            'data' => $params['data'] ?? []
        ]);
    }

    /**
     * Example: Edit a product specifically
     * 
     * @param array $params
     * @return array
     */
    public function editProduct(array $params): array
    {
        return $this->onEditRow([
            'table_name' => 'products_table',
            'id' => $params['product_id'] ?? null,
            'row' => $params['row'] ?? null,
            'data' => $params['data'] ?? []
        ]);
    }

    /**
     * Example: Remove a user specifically
     * 
     * @param array $params
     * @return array
     */
    public function removeUser(array $params): array
    {
        return $this->onRemoveRow([
            'table_name' => 'users_table',
            'id' => $params['user_id'] ?? null,
            'row' => $params['row'] ?? null,
            'description' => '[USER REMOVED]'
        ]);
    }

    /**
     * Example: Remove a product specifically
     * 
     * @param array $params
     * @return array
     */
    public function removeProduct(array $params): array
    {
        return $this->onRemoveRow([
            'table_name' => 'products_table',
            'id' => $params['product_id'] ?? null,
            'row' => $params['row'] ?? null,
            'description' => '[PRODUCT REMOVED]'
        ]);
    }

    /**
     * Override to customize field mapping for different tables
     * 
     * @param int $pageRow
     * @param string $dataKey
     * @param mixed $dataModel
     * @return string|null
     */
    protected function getCellNameForData(int $pageRow, string $dataKey, $dataModel): ?string
    {
        // You can customize field mapping per table if needed
        $fieldMapping = [
            'name' => 1,        // Column 1 for both tables
            'title' => 1,       // Product title maps to column 1
            'country' => 2,     // User country
            'category' => 2,    // Product category
            'price' => 3,       // Product price
            'status' => 3,      // User status
        ];

        $columnIndex = $fieldMapping[$dataKey] ?? null;
        
        return $columnIndex !== null ? "{$pageRow}_{$columnIndex}" : null;
    }

    /**
     * Override to customize removal values per table
     * 
     * @param int $columnCount
     * @param string $description
     * @return array
     */
    protected function getRemovalValuesForRow(int $columnCount, string $description): array
    {
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
}