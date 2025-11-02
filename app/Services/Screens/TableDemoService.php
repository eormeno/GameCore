<?php

namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use Illuminate\Support\Facades\Log;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Components\TableBuilder;
use App\Services\UI\DataTable\UsersTableModel;
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
    protected TableBuilder $users_table;

    /**
     * Get the data model instance
     * 
     * Always creates a fresh instance with current_page from cache.
     * This ensures pagination state is correctly restored.
     * 
     * @return UsersDataTableModel
     */
    private function getDataModel(): UsersDataTableModel
    {
        return new UsersDataTableModel();
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

        $table = UIBuilder::table('users_table')
            ->title('Users Table')
            ->pagination(5)
            ->dataModel(new UsersTableModel())
            ->align('center')
            ->rowMinHeight(40);

        $container->add($table);

        return $container;
    }

    public function onEditUser(array $params): void
    {
        $rowId = $params['user_id'] ?? null;
        $row = $params['row'] ?? null;
        //$this->users_table->editCell($row, 1, 'EDITADO');
        $this->users_table->getModel()->updateRow($rowId, ['name' => 'EDITADO']);
    }

    public function onRemoveUser(array $params): void
    {
        $userId = $params['user_id'] ?? null;
        $row = $params['row'] ?? null;
    }

    public function onChangePage(array $params): void
    {
        $page = $params['page'] ?? 1;
        $this->users_table->page($page);
    }

}
