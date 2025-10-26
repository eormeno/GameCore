<?php

namespace App\Services\Screens;

use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\UIBuilder;

/**
 * Table Demo Service
 * 
 * Demonstrates table functionality with:
 * - Dynamic data loading from file
 * - Header row with columns (Name, Country, Actions)
 * - Edit and Remove action buttons
 * - Column width constraints
 * 
 * Version: 1.1 (with column widths)
 */
class TableDemoService extends AbstractUIService
{
    /**
     * Load users data from file
     * 
     * @return array
     */
    private function getUsersData(): array
    {
        return require app_path('Data/users_data.php');
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

        // Load users data
        $users = $this->getUsersData();
        $userCount = count($users);

        // Pagination settings
        $perPage = 10;
        $currentPage = 1;

        // Define fixed table dimensions (acts as min and max)
        $tableRows = $perPage; // Fixed size - like a matrix
        $tableCols = 5;  // Id, Name, Country, Edit, Remove

        // Instruction label
        $container->add(
            UIBuilder::label('lbl_instruction')
                ->text("📊 Table with {$userCount} users total (showing {$perPage} per page):")
                ->style('info')
        );

        // Create table with FIXED dimensions and pagination
        $table = UIBuilder::table('users_table', $tableRows, $tableCols)
            ->title('Users Table')
            ->align('center') // Align table in container: left, center, right
            ->pagination(true, $perPage) // Enable pagination, 10 per page
            ->currentPage($currentPage)
            ->totalItems($userCount)
            ->rowMinHeight(50) // Set minimum height for all rows (50px)
            ->columnWidth(0, 50, 80)      // Id column: min 50px, max 80px
            ->columnWidth(1, 200, 250)    // Name column: min 200px, max 250px
            ->columnWidth(2, 200, 250)    // Country column: min 200px, max 250px
            ->columnWidth(3, 80, 120)     // Actions column: min 80px, max 120px
            ->columnWidth(4, 80, 120);    // Remove column: min 80px, max 120px

        // Fill header row
        $table->fillHeaderRow(['Id','Name', 'Country', 'Actions', '']);

        // Clear all rows explicitly (ensures all start empty)
        $table->clearRows();

        // Fill with paginated data
        $this->fillTableWithPage($table, $users, $currentPage, $perPage);

        $container->add($table);

        return $container;
    }

    /**
     * Fill table with data for a specific page
     * 
     * @param \App\Services\UI\Components\TableBuilder $table
     * @param array $users All users data
     * @param int $page Current page (1-based)
     * @param int $perPage Items per page
     */
    private function fillTableWithPage($table, array $users, int $page, int $perPage): void
    {
        // Calculate offset
        $offset = ($page - 1) * $perPage;
        $pagedUsers = array_slice($users, $offset, $perPage);

        $row = 0;
        foreach ($pagedUsers as $user) {
            if ($row >= $perPage) {
                break;
            }

            $table->fillRow($row++, [
                $user['id'],
                $user['name'],
                $user['country'],
                ['button' => [
                    'label' => "Edit #{$user['id']}",
                    'action' => 'edit_user',
                    'style' => 'primary',
                    'parameters' => [
                        'user_id' => $user['id'],
                        'row' => $row - 1,
                        'name' => $user['name']
                    ]
                ]],
                ['button' => [
                    'label' => "Remove #{$user['id']}",
                    'action' => 'remove_user',
                    'style' => 'danger',
                    'parameters' => [
                        'user_id' => $user['id'],
                        'row' => $row - 1
                    ]
                ]]
            ]);
        }
    }

    /**
     * Handle edit user action
     * 
     * @param array $params Action parameters
     * @return array UI updates
     */
    public function onEditUser(array $params): array
    {
        $row = $params['row'] ?? null;
        $userName = $params['name'] ?? 'Unknown';

        if ($row === null) {
            return [];
        }

        // Update the user name in the table (simulate editing)
        $newName = $userName . ' [EDITED]';

        // Get the stored UI to find the cell
        $storedUI = $this->getStoredUI();

        // Find the cell by name pattern: "{row}_{col}"
        $cellName = "{$row}_0"; // Column 0 = name
        $nameCellId = null;

        foreach ($storedUI as $id => $component) {
            if ($component['type'] === 'tablecell' && isset($component['name']) && $component['name'] === $cellName) {
                $nameCellId = $id;
                break;
            }
        }

        if (!$nameCellId) {
            return [];
        }

        // Return only the updated cell
        return [
            $nameCellId => [
                'type' => 'tablecell',
                'text' => $newName,
                '_id' => $nameCellId,
            ]
        ];
    }

    /**
     * Handle remove user action
     * 
     * @param array $params Action parameters
     * @return array UI updates
     */
    public function onRemoveUser(array $params): array
    {
        $row = $params['row'] ?? null;

        if ($row === null) {
            return [];
        }

        // Get the stored UI
        $storedUI = $this->getStoredUI();

        // Find name cell (column 0) and country cell (column 1)
        $nameCellName = "{$row}_0";
        $countryCellName = "{$row}_1";

        $nameCellId = null;
        $countryCellId = null;

        foreach ($storedUI as $id => $component) {
            if ($component['type'] === 'tablecell') {
                if (isset($component['name']) && $component['name'] === $nameCellName) {
                    $nameCellId = $id;
                } elseif (isset($component['name']) && $component['name'] === $countryCellName) {
                    $countryCellId = $id;
                }

                if ($nameCellId && $countryCellId) {
                    break;
                }
            }
        }

        $result = [];

        if ($nameCellId) {
            $result[$nameCellId] = [
                'type' => 'tablecell',
                'text' => '[REMOVED]',
                '_id' => $nameCellId,
            ];
        }

        if ($countryCellId) {
            $result[$countryCellId] = [
                'type' => 'tablecell',
                'text' => '-',
                '_id' => $countryCellId,
            ];
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
        $perPage = 10;

        // Get all users
        $users = $this->getUsersData();

        // Get table from stored UI
        $storedUI = $this->getStoredUI();
        $tableId = null;
        
        // Find the table component
        foreach ($storedUI as $id => $component) {
            if ($component['type'] === 'table' && isset($component['name']) && $component['name'] === 'users_table') {
                $tableId = $id;
                break;
            }
        }

        if (!$tableId) {
            return [];
        }

        // Calculate offset
        $offset = ($page - 1) * $perPage;
        $pagedUsers = array_slice($users, $offset, $perPage);

        // Build updates for all cells
        $result = [];
        
        $row = 0;
        foreach ($pagedUsers as $user) {
            if ($row >= $perPage) {
                break;
            }

            // Update each cell in the row
            $cellNames = [
                "{$row}_0" => $user['id'],
                "{$row}_1" => $user['name'],
                "{$row}_2" => $user['country'],
            ];

            foreach ($cellNames as $cellName => $value) {
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

            // Update button parameters for Edit and Remove buttons
            // Column 3: Edit button
            $editCellName = "{$row}_3";
            foreach ($storedUI as $id => $component) {
                if ($component['type'] === 'tablecell' && 
                    isset($component['name']) && 
                    $component['name'] === $editCellName) {
                    $result[$id] = [
                        'type' => 'tablecell',
                        'button' => [
                            'label' => "Edit #{$user['id']}",
                            'action' => 'edit_user',
                            'style' => 'primary',
                            'parameters' => [
                                'user_id' => $user['id'],
                                'row' => $row,
                                'name' => $user['name']
                            ]
                        ],
                        '_id' => $id,
                    ];
                    break;
                }
            }

            // Column 4: Remove button
            $removeCellName = "{$row}_4";
            foreach ($storedUI as $id => $component) {
                if ($component['type'] === 'tablecell' && 
                    isset($component['name']) && 
                    $component['name'] === $removeCellName) {
                    $result[$id] = [
                        'type' => 'tablecell',
                        'button' => [
                            'label' => "Remove #{$user['id']}",
                            'action' => 'remove_user',
                            'style' => 'danger',
                            'parameters' => [
                                'user_id' => $user['id'],
                                'row' => $row
                            ]
                        ],
                        '_id' => $id,
                    ];
                    break;
                }
            }

            $row++;
        }

        // Clear remaining rows if less than perPage
        for ($i = $row; $i < $perPage; $i++) {
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

        return $result;
    }
}
