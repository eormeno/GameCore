<?php

namespace App\Services\Screens;

use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\UIBuilder;

/**
 * Table Demo Service
 * 
 * Simple table demonstration with:
 * - Header row with 4 columns (Name, Country, Actions buttons)
 * - 5 data rows with Edit and Remove buttons
 */
class TableDemoService extends AbstractUIService
{
    /**
     * Build the table demo UI
     */
    protected function buildBaseUI(...$params): UIContainer
    {
        $container = UIBuilder::container('main')
            ->parent('main')
            ->layout(LayoutType::VERTICAL)
            ->title('Table Component Demo');

        // Create table with dimensions: 5 rows × 4 columns
        $table = UIBuilder::table('users_table', 5, 4)
            ->title('Users Table');

        // Fill header row
        $table->fillHeaderRow(['Name', 'Country', 'Actions', '']);

        // Fill data rows
        $table->fillRow(0, [
            'Alice Johnson',
            'United States',
            ['button' => ['label' => 'Edit', 'action' => 'edit_user', 'style' => 'primary', 'parameters' => ['user_id' => 1, 'row' => 0, 'name' => 'Alice Johnson']]],
            ['button' => ['label' => 'Remove', 'action' => 'remove_user', 'style' => 'danger', 'parameters' => ['user_id' => 1, 'row' => 0]]]
        ]);

        $table->fillRow(1, [
            'Bob Smith',
            'Canada',
            ['button' => ['label' => 'Edit', 'action' => 'edit_user', 'style' => 'primary', 'parameters' => ['user_id' => 2, 'row' => 1, 'name' => 'Bob Smith']]],
            ['button' => ['label' => 'Remove', 'action' => 'remove_user', 'style' => 'danger', 'parameters' => ['user_id' => 2, 'row' => 1]]]
        ]);

        $table->fillRow(2, [
            'Charlie Brown',
            'United Kingdom',
            ['button' => ['label' => 'Edit', 'action' => 'edit_user', 'style' => 'primary', 'parameters' => ['user_id' => 3, 'row' => 2, 'name' => 'Charlie Brown']]],
            ['button' => ['label' => 'Remove', 'action' => 'remove_user', 'style' => 'danger', 'parameters' => ['user_id' => 3, 'row' => 2]]]
        ]);

        $table->fillRow(3, [
            'Diana Prince',
            'Germany',
            ['button' => ['label' => 'Edit', 'action' => 'edit_user', 'style' => 'primary', 'parameters' => ['user_id' => 4, 'row' => 3, 'name' => 'Diana Prince']]],
            ['button' => ['label' => 'Remove', 'action' => 'remove_user', 'style' => 'danger', 'parameters' => ['user_id' => 4, 'row' => 3]]]
        ]);

        $table->fillRow(4, [
            'Ethan Hunt',
            'Australia',
            ['button' => ['label' => 'Edit', 'action' => 'edit_user', 'style' => 'primary', 'parameters' => ['user_id' => 5, 'row' => 4, 'name' => 'Ethan Hunt']]],
            ['button' => ['label' => 'Remove', 'action' => 'remove_user', 'style' => 'danger', 'parameters' => ['user_id' => 5, 'row' => 4]]]
        ]);

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
