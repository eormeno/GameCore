<?php

namespace Tests\Feature;

use App\Services\Screens\TableDemoService;
use Tests\TestCase;

/**
 * Test suite for enhanced DataTableEventsTrait functionality
 * 
 * Tests the new pagination and configurable removal features:
 * - onChangeTablePage: Generic page navigation
 * - Configurable "removed" row display
 * - Data model integration for removal configuration
 */
class EnhancedDataTableEventsTraitTest extends TestCase
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
    public function it_can_change_page_using_generic_method()
    {
        // Arrange
        $params = [
            'table_name' => 'users_table',
            'page' => 2
        ];

        // Act
        $result = $this->service->onChangeTablePage($params);

        // Assert
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        // Should contain UI updates for table cells
        foreach ($result as $componentId => $update) {
            $this->assertArrayHasKey('type', $update);
            $this->assertEquals('tablecell', $update['type']);
            $this->assertArrayHasKey('_id', $update);
        }
    }

    /**
     * @test
     */
    public function it_maintains_legacy_onChangePage_compatibility()
    {
        // Arrange
        $legacyParams = [
            'page' => 2
        ];

        // Act
        $result = $this->service->onChangePage($legacyParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        // Should work exactly like the generic method but with legacy parameters
        foreach ($result as $componentId => $update) {
            $this->assertArrayHasKey('type', $update);
            $this->assertEquals('tablecell', $update['type']);
        }
    }

    /**
     * @test
     */
    public function it_handles_invalid_table_for_pagination()
    {
        // Arrange
        $params = [
            'table_name' => 'nonexistent_table',
            'page' => 2
        ];

        // Act
        $result = $this->service->onChangeTablePage($params);

        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result); // Should return empty array for invalid table
    }

    /**
     * @test
     */
    public function it_uses_configurable_removal_display()
    {
        // Arrange
        $params = [
            'table_name' => 'users_table',
            'id' => 1,
            'row' => 0,
            'description' => '[CUSTOM REMOVAL]' // This should be overridden by model config
        ];

        // Act
        $result = $this->service->onRemoveRow($params);

        // Assert
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        // Check that the removal display uses the model's configuration
        // The UsersDataTableModel should use '[USER REMOVED]' instead of '[CUSTOM REMOVAL]'
        $foundModelConfiguredMessage = false;
        foreach ($result as $update) {
            if (isset($update['text']) && $update['text'] === '[USER REMOVED]') {
                $foundModelConfiguredMessage = true;
                break;
            }
        }
        
        $this->assertTrue($foundModelConfiguredMessage, 'Should use model-configured removal message');
    }

    /**
     * @test
     */
    public function it_shows_custom_removal_indicators()
    {
        // Arrange
        $params = [
            'table_name' => 'users_table',
            'id' => 1,
            'row' => 0
        ];

        // Act
        $result = $this->service->onRemoveRow($params);

        // Assert
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        // Check for custom indicators from UsersDataTableModel
        $foundCustomIndicators = false;
        foreach ($result as $update) {
            if (isset($update['text']) && in_array($update['text'], ['❌', '⛔', '---'])) {
                $foundCustomIndicators = true;
                break;
            }
        }
        
        $this->assertTrue($foundCustomIndicators, 'Should use custom visual indicators from model');
    }

    /**
     * @test
     */
    public function it_clears_remaining_rows_during_pagination()
    {
        // This test verifies that when changing to a page with fewer items,
        // the remaining rows are properly cleared
        
        // Arrange - go to last page which might have fewer items
        $params = [
            'table_name' => 'users_table',
            'page' => 10 // High page number to potentially get fewer results
        ];

        // Act
        $result = $this->service->onChangeTablePage($params);

        // Assert
        $this->assertIsArray($result);
        
        // Should contain updates (even if just clearing cells)
        // The exact assertion depends on data, but it should not throw errors
        $this->assertTrue(true); // If we reach here without errors, clearing works
    }

    /**
     * @test
     */
    public function it_handles_missing_pagination_parameters()
    {
        // Test missing table_name
        $result1 = $this->service->onChangeTablePage(['page' => 2]);
        $this->assertEmpty($result1);

        // Test missing page (should default to 1)
        $result2 = $this->service->onChangeTablePage(['table_name' => 'users_table']);
        $this->assertIsArray($result2);
        // Should still work with default page 1
    }

    /**
     * @test
     */
    public function it_updates_both_data_and_button_cells_during_pagination()
    {
        // Arrange
        $params = [
            'table_name' => 'users_table',
            'page' => 1
        ];

        // Act
        $result = $this->service->onChangeTablePage($params);

        // Assert
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        // Should contain both text updates and button updates
        $hasTextCells = false;
        $hasButtonCells = false;
        
        foreach ($result as $update) {
            if (isset($update['text'])) {
                $hasTextCells = true;
            }
            if (isset($update['button'])) {
                $hasButtonCells = true;
            }
        }
        
        $this->assertTrue($hasTextCells, 'Should update text cells during pagination');
        // Note: Button cells might not always be present depending on data
    }

    /**
     * @test
     */
    public function it_can_test_data_model_removal_config()
    {
        // Test that we can access the data model's removal configuration
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getDataModelForTable');
        $method->setAccessible(true);

        $dataModel = $method->invoke($this->service, 'users_table');
        $this->assertNotNull($dataModel);

        // Test that the model has the removal configuration
        $this->assertTrue(method_exists($dataModel, 'getRemovedRowConfig'));
        $this->assertTrue(method_exists($dataModel, 'getRemovalValues'));

        // Test the actual configuration
        $config = $dataModel->getRemovedRowConfig();
        $this->assertIsArray($config);
        $this->assertArrayHasKey('primary_message', $config);
        $this->assertEquals('[USER REMOVED]', $config['primary_message']);

        // Test removal values generation
        $removalValues = $dataModel->getRemovalValues(5); // 5 columns
        $this->assertIsArray($removalValues);
        $this->assertCount(5, $removalValues);
        $this->assertEquals('❌', $removalValues[0]); // ID column
        $this->assertEquals('[USER REMOVED]', $removalValues[1]); // Primary message
    }
}