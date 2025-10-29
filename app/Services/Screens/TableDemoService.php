<?php

namespace App\Services\Screens;

use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\DataTable\UsersDataTableModel;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\UIBuilder;

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
    private UsersDataTableModel $dataModel;

    /**
     * Get the data model instance
     * 
     * @return UsersDataTableModel
     */
    private function getDataModel(): UsersDataTableModel
    {
        if (!isset($this->dataModel)) {
            $this->dataModel = new UsersDataTableModel(5, 1); // 5 per page, start at page 1
        }
        return $this->dataModel;
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
            ->rowMinHeight(20);

        $container->add($table);

        return $container;
    }



    /**
     * Handle edit user action
     * 
     * @param array $params Action parameters
     * @return array UI updates
     */
    public function onEditUser(array $params): array
    {
        $userId = $params['user_id'] ?? null;
        $row = $params['row'] ?? null;
        $userName = $params['name'] ?? 'Unknown';

        if ($userId === null || $row === null) {
            return [];
        }

        // Use the data model to update the user (simulation)
        $dataModel = $this->getDataModel();
        $dataModel->updateUser($userId, ['name' => $userName . ' [EDITED]']);

        // Find the name cell and update it
        $storedUI = $this->getStoredUI();
        $currentPage = $dataModel->getCurrentPage();
        $perPage = $dataModel->getPerPage();
        
        // Calculate the row position on current page
        $pageRow = $row % $perPage;
        $cellName = "{$pageRow}_1"; // Column 1 = name
        
        foreach ($storedUI as $id => $component) {
            if ($component['type'] === 'tablecell' && 
                isset($component['name']) && 
                $component['name'] === $cellName) {
                return [
                    $id => [
                        'type' => 'tablecell',
                        'text' => $userName . ' [EDITED]',
                        '_id' => $id,
                    ]
                ];
            }
        }

        return [];
    }

    /**
     * Handle remove user action
     * 
     * @param array $params Action parameters
     * @return array UI updates
     */
    public function onRemoveUser(array $params): array
    {
        $userId = $params['user_id'] ?? null;
        $row = $params['row'] ?? null;

        if ($userId === null || $row === null) {
            return [];
        }

        // Use the data model to remove the user (simulation)
        $dataModel = $this->getDataModel();
        $dataModel->removeUser($userId);

        // Calculate the row position on current page
        $perPage = $dataModel->getPerPage();
        $pageRow = $row % $perPage;

        // Get the stored UI and find cells for this row
        $storedUI = $this->getStoredUI();
        $result = [];

        // Define cell names for this row
        $cellNames = [
            "{$pageRow}_0" => '-',           // ID
            "{$pageRow}_1" => '[REMOVED]',   // Name
            "{$pageRow}_2" => '-',           // Country
            "{$pageRow}_3" => '-',           // Edit button
            "{$pageRow}_4" => '-'            // Remove button
        ];

        // Update all cells in the row
        foreach ($cellNames as $cellName => $newValue) {
            foreach ($storedUI as $id => $component) {
                if ($component['type'] === 'tablecell' && 
                    isset($component['name']) && 
                    $component['name'] === $cellName) {
                    $result[$id] = [
                        'type' => 'tablecell',
                        'text' => $newValue,
                        '_id' => $id,
                    ];
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Handle page change action
     * 
     * @param array $params Action parameters with 'page' key
     * @return array UI updates
     */
    public function onChangePage(array $params): array
    {
        $page = $params['page'] ?? 1;
        
        // Update data model with new page
        $dataModel = $this->getDataModel();
        $dataModel->setCurrentPage($page);
        $formattedData = $dataModel->getFormattedPageData();
        
        // Get stored UI to find cells
        $storedUI = $this->getStoredUI();
        $result = [];
        
        // Update data cells
        $row = 0;
        foreach ($formattedData as $rowData) {
            if ($row >= $dataModel->getPerPage()) {
                break;
            }

            // Update each cell in the row
            $cellData = [
                "{$row}_0" => $rowData['id'],
                "{$row}_1" => $rowData['name'],
                "{$row}_2" => $rowData['country'],
            ];

            foreach ($cellData as $cellName => $value) {
                foreach ($storedUI as $id => $component) {
                    if ($component['type'] === 'tablecell' && 
                        isset($component['name']) && 
                        $component['name'] === $cellName) {
                        $result[$id] = [
                            'type' => 'tablecell',
                            'text' => (string)$value,
                            '_id' => $id,
                        ];
                        break;
                    }
                }
            }

            // Update button cells
            $this->updateButtonCell($storedUI, $result, "{$row}_3", $rowData['actions']);
            $this->updateButtonCell($storedUI, $result, "{$row}_4", $rowData['remove']);

            $row++;
        }

        // Clear remaining rows if less than perPage
        $this->clearRemainingRows($storedUI, $result, $row, $dataModel->getPerPage());

        return $result;
    }

    /**
     * Update a button cell in the result array
     * 
     * @param array $storedUI
     * @param array &$result
     * @param string $cellName
     * @param array $buttonData
     */
    private function updateButtonCell(array $storedUI, array &$result, string $cellName, array $buttonData): void
    {
        foreach ($storedUI as $id => $component) {
            if ($component['type'] === 'tablecell' && 
                isset($component['name']) && 
                $component['name'] === $cellName) {
                $result[$id] = [
                    'type' => 'tablecell',
                    'button' => $buttonData['button'],
                    '_id' => $id,
                ];
                break;
            }
        }
    }

    /**
     * Clear remaining empty rows
     * 
     * @param array $storedUI
     * @param array &$result
     * @param int $startRow
     * @param int $totalRows
     */
    private function clearRemainingRows(array $storedUI, array &$result, int $startRow, int $totalRows): void
    {
        for ($i = $startRow; $i < $totalRows; $i++) {
            for ($col = 0; $col < 5; $col++) {
                $cellName = "{$i}_{$col}";
                foreach ($storedUI as $id => $component) {
                    if ($component['type'] === 'tablecell' && 
                        isset($component['name']) && 
                        $component['name'] === $cellName) {
                        $result[$id] = [
                            'type' => 'tablecell',
                            'text' => '',
                            '_id' => $id,
                        ];
                        break;
                    }
                }
            }
        }
    }
}
