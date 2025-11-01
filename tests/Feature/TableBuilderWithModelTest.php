<?php

namespace Tests\Feature;

use App\Services\UI\Components\TableBuilder;
use App\Services\UI\DataTable\UsersDataTableModel;
use App\Services\UI\UIBuilder;
use Tests\TestCase;

/**
 * Test suite for TableBuilder with DataModel functionality
 * 
 * Tests the new architecture where TableBuilder can receive a data model
 * and configure itself automatically.
 */
class TableBuilderWithModelTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_create_table_with_data_model()
    {
        // Arrange
        $dataModel = new UsersDataTableModel(); // 3 per page, page 1
        
        // Act
        $table = UIBuilder::tableWithModel('test_table', $dataModel);
        $tableJson = $table->toJson();
        
        // Assert
        $this->assertInstanceOf(TableBuilder::class, $table);
        
        // Check that table has correct dimensions from model
        $dimensions = $table->getDimensions();
        $this->assertEquals(3, $dimensions['rows']); // per_page from model
        $this->assertEquals(5, $dimensions['cols']); // number of columns from model
        
        // Check pagination configuration
        $paginationInfo = $table->getPaginationInfo();
        $this->assertEquals(1, $paginationInfo['current_page']);
        $this->assertEquals(3, $paginationInfo['per_page']);
        $this->assertTrue($paginationInfo['total_items'] > 0);
    }

    /**
     * @test
     */
    public function it_automatically_fills_headers_from_model()
    {
        // Arrange
        $dataModel = new UsersDataTableModel(5, 1);
        
        // Act
        $table = UIBuilder::tableWithModel('test_table', $dataModel);
        $headerRow = $table->getHeaderRow();
        
        // Assert
        $this->assertNotNull($headerRow);
        
        // Get the table JSON to inspect header cells
        $tableJson = $table->toJson();
        
        // Find header cells and verify they have correct labels
        $headerCells = array_filter($tableJson, function($component) {
            return $component['type'] === 'tableheadercell';
        });
        
        $this->assertCount(5, $headerCells); // 5 columns
        
        // Check that headers match model column labels
        $columns = $dataModel->getColumns();
        $expectedHeaders = array_column($columns, 'label');
        
        // Sort header cells by column index to match order
        usort($headerCells, function($a, $b) {
            return $a['column'] - $b['column'];
        });
        
        foreach ($headerCells as $index => $cell) {
            $this->assertEquals($expectedHeaders[$index], $cell['text']);
        }
    }

    /**
     * @test
     */
    public function it_automatically_fills_data_from_model()
    {
        // Arrange
        $dataModel = new UsersDataTableModel(2, 1); // 2 per page to test limited data
        
        // Act
        $table = UIBuilder::tableWithModel('test_table', $dataModel);
        $tableJson = $table->toJson();
        
        // Assert - find data cells (not header cells)
        $dataCells = array_filter($tableJson, function($component) {
            return $component['type'] === 'tablecell';
        });
        
        $this->assertGreaterThan(0, count($dataCells));
        
        // Verify we have data in first row, first column (should be user ID)
        $firstRowFirstCol = array_filter($dataCells, function($cell) {
            return isset($cell['name']) && $cell['name'] === '0_0';
        });
        
        $this->assertCount(1, $firstRowFirstCol);
        $firstCell = array_values($firstRowFirstCol)[0];
        $this->assertNotEmpty($firstCell['text']); // Should have user ID
    }

    /**
     * @test
     */
    public function it_applies_column_widths_from_model()
    {
        // Arrange
        $dataModel = new UsersDataTableModel(3, 1);
        
        // Act
        $table = UIBuilder::tableWithModel('test_table', $dataModel);
        $tableJson = $table->toJson();
        
        // Assert - check that cells have width constraints
        $dataCells = array_filter($tableJson, function($component) {
            return $component['type'] === 'tablecell';
        });
        
        // Find first column cells (should have width constraints)
        $firstColumnCells = array_filter($dataCells, function($cell) {
            return isset($cell['name']) && strpos($cell['name'], '_0') !== false;
        });
        
        $this->assertGreaterThan(0, count($firstColumnCells));
        
        // Check that at least some cells have width configuration
        $cellsWithWidth = array_filter($firstColumnCells, function($cell) {
            return isset($cell['min_width']) || isset($cell['max_width']);
        });
        
        $this->assertGreaterThan(0, count($cellsWithWidth));
    }

    /**
     * @test
     */
    public function it_handles_null_data_model_gracefully()
    {
        // Act
        $table = UIBuilder::tableWithModel('test_table', null);
        
        // Assert
        $this->assertInstanceOf(TableBuilder::class, $table);
        
        // Should have default empty dimensions
        $dimensions = $table->getDimensions();
        $this->assertEquals(0, $dimensions['rows']);
        $this->assertEquals(0, $dimensions['cols']);
    }
}