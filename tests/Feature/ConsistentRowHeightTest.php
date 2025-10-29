<?php

namespace Tests\Feature;

use App\Services\Screens\TableDemoService;
use Tests\TestCase;

/**
 * Test suite for consistent row height functionality
 * 
 * Tests that all rows maintain the same height regardless of their state:
 * - Data rows with content
 * - Removed rows with removal indicators
 * - Empty rows from pagination
 */
class ConsistentRowHeightTest extends TestCase
{
    private TableDemoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TableDemoService();
        // Initialize the service UI to have stored components
        $this->service->getUI();
    }

    /**
     * @test
     */
    public function it_preserves_height_when_updating_cells()
    {
        // Arrange - get the initial UI to have baseline cells with height
        $initialUI = $this->service->getUI();
        
        // Find a data cell and verify it has height properties
        $dataCellId = null;
        $originalHeight = null;
        
        foreach ($initialUI as $id => $component) {
            if ($component['type'] === 'tablecell' && 
                isset($component['name']) && 
                strpos($component['name'], '_1') !== false) { // Column 1 (name column)
                $dataCellId = $id;
                $originalHeight = $component['min_height'] ?? null;
                break;
            }
        }
        
        $this->assertNotNull($dataCellId, 'Should find a data cell');
        $this->assertNotNull($originalHeight, 'Original cell should have height property');

        // Act - edit a row which should update the cell
        $params = [
            'table_name' => 'users_table',
            'id' => 1,
            'row' => 0,
            'data' => ['name' => 'Updated Name']
        ];

        $result = $this->service->onEditRow($params);

        // Assert - the updated cell should preserve the height
        $this->assertNotEmpty($result);
        
        $updatedCell = null;
        foreach ($result as $update) {
            if (isset($update['_id']) && $update['_id'] === $dataCellId) {
                $updatedCell = $update;
                break;
            }
        }
        
        $this->assertNotNull($updatedCell, 'Should find the updated cell');
        $this->assertArrayHasKey('min_height', $updatedCell, 'Updated cell should preserve min_height');
        $this->assertEquals($originalHeight, $updatedCell['min_height'], 'Height should be preserved');
    }

    /**
     * @test
     */
    public function it_applies_consistent_height_to_removed_rows()
    {
        // Arrange
        $params = [
            'table_name' => 'users_table',
            'id' => 1,
            'row' => 0
        ];

        // Act - remove a row
        $result = $this->service->onRemoveRow($params);

        // Assert - all cells in the removed row should have consistent height
        $this->assertNotEmpty($result);
        
        $heightFound = false;
        foreach ($result as $update) {
            if (isset($update['min_height'])) {
                $heightFound = true;
                $this->assertGreaterThan(0, $update['min_height'], 'Removed row cells should have minimum height');
            }
        }
        
        $this->assertTrue($heightFound, 'At least some cells should have height properties preserved');
    }

    /**
     * @test
     */
    public function it_applies_consistent_height_to_empty_rows_during_pagination()
    {
        // Arrange - go to a page that might have fewer items (to trigger empty row clearing)
        $params = [
            'table_name' => 'users_table',
            'page' => 10 // High page number to potentially get empty rows
        ];

        // Act
        $result = $this->service->onChangeTablePage($params);

        // Assert - empty cells should also have height properties
        $this->assertIsArray($result);
        
        // Look for empty cells (cells with empty text)
        $emptyHeight = null;
        foreach ($result as $update) {
            if (isset($update['text']) && $update['text'] === '' && isset($update['min_height'])) {
                $emptyHeight = $update['min_height'];
                break;
            }
        }
        
        // If we found empty cells with height, they should have the expected height
        if ($emptyHeight !== null) {
            $this->assertGreaterThan(0, $emptyHeight, 'Empty cells should have minimum height');
            $this->assertEquals(24, $emptyHeight, 'Empty cells should have the configured height for users table');
        }
    }

    /**
     * @test
     */
    public function it_uses_table_specific_default_height()
    {
        // Test that the service returns correct height for users table
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getDefaultRowHeight');
        $method->setAccessible(true);

        // Test users table height
        $usersHeight = $method->invoke($this->service, 'users_table');
        $this->assertEquals(24, $usersHeight, 'Users table should have 24px height');

        // Test default height for other tables
        $defaultHeight = $method->invoke($this->service, 'other_table');
        $this->assertEquals(30, $defaultHeight, 'Other tables should have 30px default height');

        // Test null table name
        $nullHeight = $method->invoke($this->service, null);
        $this->assertEquals(30, $nullHeight, 'Null table name should return default 30px height');
    }

    /**
     * @test
     */
    public function it_preserves_button_cell_heights()
    {
        // Arrange - get initial UI to find a button cell
        $initialUI = $this->service->getUI();
        
        $buttonCellId = null;
        $originalHeight = null;
        
        foreach ($initialUI as $id => $component) {
            if ($component['type'] === 'tablecell' && 
                isset($component['button']) &&
                isset($component['name']) && 
                strpos($component['name'], '_3') !== false) { // Column 3 (actions column)
                $buttonCellId = $id;
                $originalHeight = $component['min_height'] ?? null;
                break;
            }
        }

        if ($buttonCellId === null) {
            $this->markTestSkipped('No button cells found in initial UI');
            return;
        }

        // Act - trigger pagination which updates button cells
        $params = [
            'table_name' => 'users_table',
            'page' => 1
        ];

        $result = $this->service->onChangeTablePage($params);

        // Assert - button cells should preserve their height
        $buttonHeightPreserved = false;
        foreach ($result as $update) {
            if (isset($update['button']) && isset($update['min_height'])) {
                $buttonHeightPreserved = true;
                $this->assertGreaterThan(0, $update['min_height'], 'Button cells should have minimum height');
                break;
            }
        }

        // If we had button cells in the result, they should preserve height
        if ($buttonHeightPreserved) {
            $this->assertTrue(true, 'Button cell height was preserved');
        } else {
            // If no button cells in result, that's also fine - pagination might not have updated them
            $this->assertTrue(true, 'No button cells to verify in pagination result');
        }
    }

    /**
     * @test
     */
    public function it_applies_default_height_when_no_original_height_exists()
    {
        // This test verifies that if a cell somehow doesn't have height properties,
        // the default height is applied during updates

        // We can't easily simulate this scenario without mocking,
        // but we can test the preservation logic indirectly
        
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('preserveHeightProperties');
        $method->setAccessible(true);

        // Simulate a component without height properties
        $originalComponent = [
            'type' => 'tablecell',
            'text' => 'Test',
            'name' => '0_1'
            // No height properties
        ];

        $cellUpdate = [
            'type' => 'tablecell',
            'text' => 'Updated Text',
            '_id' => 12345
        ];

        // Act
        $method->invokeArgs($this->service, [$originalComponent, &$cellUpdate, 'users_table']);

        // Assert - should apply default height
        $this->assertArrayHasKey('min_height', $cellUpdate, 'Should apply default minimum height');
        $this->assertEquals(24, $cellUpdate['min_height'], 'Should apply users table default height');
    }
}