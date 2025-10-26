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

        // Define fixed table dimensions (acts as min and max)
        $tableRows = 10; // Fixed size - like a matrix
        $tableCols = 5;  // Id, Name, Country, Edit, Remove

        // Instruction label
        $container->add(
            UIBuilder::label('lbl_instruction')
                ->text("📊 Table with {$userCount} users (table size: {$tableRows}×{$tableCols}):")
                ->style('info')
        );

        // Create table with FIXED dimensions (matrix-style)
        // All rows are created initially empty
        $table = UIBuilder::table('users_table', $tableRows, $tableCols)
            ->title('Users Table')
            ->align('center') // Align table in container: left, center, right
            ->rowMinHeight(50) // Set minimum height for all rows (50px)
            ->columnWidth(0, 50, 80)      // Id column: min 50px, max 80px
            ->columnWidth(1, 200, 250)    // Name column: min 200px, max 250px
            ->columnWidth(2, 200, 250)    // Country column: min 200px, max 250px
            ->columnWidth(3, 80, 120)     // Actions column: min 80px, max 120px
            ->columnWidth(4, 80, 120);    // Remove column: min 80px, max 120px

        // Fill header row
        $table->fillHeaderRow(['Id','Name', 'Country', 'Actions', '']);

        // Clear all rows explicitly (ensures all start empty)
        // This is important for pagination scenarios
        $table->clearRows();

        $row = 0;
        // Fill only the rows with actual data
        foreach ($users as $index => $user) {
            // Stop if we exceed table capacity
            if ($row >= $tableRows) {
                break;
            }

            $table->fillRow($row++, [
                $user['id'],
                $user['name'],
                $user['country'],
                ['button' => [
                    'label' => 'Edit',
                    'action' => 'edit_user',
                    'style' => 'primary',
                    'parameters' => [
                        'user_id' => $user['id'],
                        'row' => $index,
                        'name' => $user['name']
                    ]
                ]],
                ['button' => [
                    'label' => 'Remove',
                    'action' => 'remove_user',
                    'style' => 'danger',
                    'parameters' => [
                        'user_id' => $user['id'],
                        'row' => $index
                    ]
                ]]
            ]);
        }

        // Remaining rows (if any) stay empty - no need to fill explicitly

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
}
