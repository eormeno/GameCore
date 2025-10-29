<?php

namespace Tests\Feature;

use App\Services\Screens\TableDemoService;
use Tests\TestCase;

/**
 * Integration test for the complete table system with consistent heights
 */
class TableSystemIntegrationTest extends TestCase
{
    private TableDemoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TableDemoService();
    }

    /**
     * @test
     */
    public function it_creates_complete_table_system_with_consistent_heights()
    {
        // Act - get the full UI
        $ui = $this->service->getUI();

        // Assert - should have proper structure
        $this->assertNotEmpty($ui, 'UI should not be empty');
        $this->assertGreaterThan(30, count($ui), 'Should have many UI components');

        // Find all table cells and verify they have consistent heights
        $cellHeights = [];
        $cellCount = 0;
        
        foreach ($ui as $id => $component) {
            if ($component['type'] === 'tablecell') {
                $cellCount++;
                
                $this->assertArrayHasKey('min_height', $component, 
                    "Cell {$id} should have min_height property");
                
                $height = $component['min_height'];
                $this->assertIsInt($height, "Height should be integer for cell {$id}");
                $this->assertGreaterThan(0, $height, "Height should be positive for cell {$id}");
                
                $cellHeights[] = $height;
            }
        }

        $this->assertGreaterThan(0, $cellCount, 'Should have table cells');
        
        // All cells should have the same height (24px for users table - compact)
        $uniqueHeights = array_unique($cellHeights);
        $this->assertCount(1, $uniqueHeights, 
            'All cells should have the same height. Found heights: ' . implode(', ', $uniqueHeights));
        $this->assertEquals(24, $uniqueHeights[0], 'All cells should have 24px height');
    }

    /**
     * @test
     */
    public function it_maintains_height_consistency_through_all_operations()
    {
        // Get initial UI to establish baseline
        $initialUI = $this->service->getUI();
        
        // Test edit operation maintains height
        $editResult = $this->service->onEditRow([
            'table_name' => 'users_table',
            'id' => 1,
            'row' => 0,
            'data' => ['name' => 'Height Test User']
        ]);
        
        foreach ($editResult as $update) {
            if (isset($update['min_height'])) {
                $this->assertEquals(24, $update['min_height'], 
                    'Edit operation should maintain 24px height');
            }
        }

        // Test remove operation maintains height
        $removeResult = $this->service->onRemoveRow([
            'table_name' => 'users_table',
            'id' => 2,
            'row' => 1
        ]);
        
        foreach ($removeResult as $update) {
            if (isset($update['min_height'])) {
                $this->assertEquals(24, $update['min_height'], 
                    'Remove operation should maintain 24px height');
            }
        }

        // Test pagination maintains height
        $pageResult = $this->service->onChangeTablePage([
            'table_name' => 'users_table',
            'page' => 2
        ]);
        
        foreach ($pageResult as $update) {
            if (isset($update['min_height'])) {
                $this->assertEquals(24, $update['min_height'], 
                    'Pagination should maintain 24px height');
            }
        }
    }

    /**
     * @test
     */
    public function it_generates_expected_number_of_components()
    {
        // Act
        $ui = $this->service->getUI();

        // Assert - should generate the expected number of components
        $this->assertEquals(39, count($ui), 
            'Should generate exactly 39 UI components as established in baseline');
    }

    /**
     * @test
     */
    public function it_has_proper_table_structure_with_heights()
    {
        // Act
        $ui = $this->service->getUI();

        // Find table container
        $tableFound = false;
        $headerRowFound = false;
        $dataRowsFound = 0;
        $cellsWithHeight = 0;

        foreach ($ui as $id => $component) {
            switch ($component['type']) {
                case 'table':
                    $tableFound = true;
                    break;
                    
                case 'tableheaderrow':
                    $headerRowFound = true;
                    break;
                    
                case 'tablerow':
                    $dataRowsFound++;
                    break;
                    
                case 'tablecell':
                    if (isset($component['min_height']) && $component['min_height'] === 24) {
                        $cellsWithHeight++;
                    }
                    break;
            }
        }

        $this->assertTrue($tableFound, 'Should have a table component');
        $this->assertTrue($headerRowFound, 'Should have a header row');
        $this->assertGreaterThan(0, $dataRowsFound, 'Should have data rows');
        $this->assertGreaterThan(0, $cellsWithHeight, 'Should have cells with consistent height');
    }

    /**
     * @test
     */
    public function it_preserves_all_existing_functionality()
    {
        // This test ensures that adding height consistency didn't break anything

        // Test that service still has proper methods
        $this->assertTrue(method_exists($this->service, 'onEditUser'), 
            'Legacy onEditUser method should still exist');
        $this->assertTrue(method_exists($this->service, 'onRemoveUser'), 
            'Legacy onRemoveUser method should still exist');
        $this->assertTrue(method_exists($this->service, 'onChangePage'), 
            'Legacy onChangePage method should still exist');

        // Test that new generic methods exist
        $this->assertTrue(method_exists($this->service, 'onEditRow'), 
            'Generic onEditRow method should exist');
        $this->assertTrue(method_exists($this->service, 'onRemoveRow'), 
            'Generic onRemoveRow method should exist');
        $this->assertTrue(method_exists($this->service, 'onChangeTablePage'), 
            'Generic onChangeTablePage method should exist');

        // Test that service can still generate UI without errors
        $ui = $this->service->getUI();
        $this->assertIsArray($ui, 'Should return array');
        $this->assertNotEmpty($ui, 'Should not be empty');

        // Test that legacy methods still work (backward compatibility)
        $legacyEditResult = $this->service->onEditUser([
            'id' => 1,
            'row' => 0,
            'data' => ['name' => 'Legacy Test']
        ]);
        $this->assertIsArray($legacyEditResult, 'Legacy edit should return array');

        $legacyRemoveResult = $this->service->onRemoveUser([
            'id' => 2,
            'row' => 1
        ]);
        $this->assertIsArray($legacyRemoveResult, 'Legacy remove should return array');

        $legacyPageResult = $this->service->onChangePage(['page' => 2]);
        $this->assertIsArray($legacyPageResult, 'Legacy pagination should return array');
    }
}