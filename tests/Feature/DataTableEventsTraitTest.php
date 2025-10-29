<?php

namespace Tests\Feature;

use App\Services\Screens\TableDemoService;
use Tests\TestCase;

/**
 * Test suite for DataTableEventsTrait functionality
 * 
 * Tests the generic table event handling capabilities:
 * - onEditRow: Generic row editing
 * - onRemoveRow: Generic row removal
 * - Multiple table support through table_name parameter
 */
class DataTableEventsTraitTest extends TestCase
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
    public function it_can_edit_row_using_generic_method()
    {
        // Arrange
        $params = [
            'table_name' => 'users_table',
            'id' => 1,
            'row' => 0,
            'data' => [
                'name' => 'Updated Name [EDITED]'
            ]
        ];

        // Act
        $result = $this->service->onEditRow($params);

        // Assert
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        // Should contain UI updates
        foreach ($result as $componentId => $update) {
            $this->assertArrayHasKey('type', $update);
            $this->assertEquals('tablecell', $update['type']);
            $this->assertArrayHasKey('text', $update);
            $this->assertArrayHasKey('_id', $update);
        }
    }

    /**
     * @test
     */
    public function it_can_remove_row_using_generic_method()
    {
        // Arrange
        $params = [
            'table_name' => 'users_table',
            'id' => 1,
            'row' => 0,
            'description' => '[DELETED]'
        ];

        // Act
        $result = $this->service->onRemoveRow($params);

        // Assert
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        // Should contain UI updates for multiple cells (full row)
        $this->assertGreaterThan(1, count($result));
        
        // All updates should be tablecells
        foreach ($result as $componentId => $update) {
            $this->assertArrayHasKey('type', $update);
            $this->assertEquals('tablecell', $update['type']);
            $this->assertArrayHasKey('text', $update);
            $this->assertArrayHasKey('_id', $update);
        }
    }

    /**
     * @test
     */
    public function it_handles_invalid_table_name_gracefully()
    {
        // Arrange
        $params = [
            'table_name' => 'nonexistent_table',
            'id' => 1,
            'row' => 0,
            'data' => ['name' => 'Test']
        ];

        // Act
        $result = $this->service->onEditRow($params);

        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result); // Should return empty array for invalid table
    }

    /**
     * @test
     */
    public function it_handles_missing_required_parameters()
    {
        // Test missing table_name
        $result1 = $this->service->onEditRow(['id' => 1, 'data' => []]);
        $this->assertEmpty($result1);

        // Test missing id
        $result2 = $this->service->onEditRow(['table_name' => 'users_table', 'data' => []]);
        $this->assertEmpty($result2);

        // Test missing table_name for removal
        $result3 = $this->service->onRemoveRow(['id' => 1]);
        $this->assertEmpty($result3);

        // Test missing id for removal
        $result4 = $this->service->onRemoveRow(['table_name' => 'users_table']);
        $this->assertEmpty($result4);
    }

    /**
     * @test
     */
    public function it_maintains_backward_compatibility_with_legacy_methods()
    {
        // Test that legacy onEditUser still works
        $legacyEditParams = [
            'user_id' => 1,
            'row' => 0,
            'name' => 'Legacy User'
        ];

        $editResult = $this->service->onEditUser($legacyEditParams);
        $this->assertIsArray($editResult);

        // Test that legacy onRemoveUser still works
        $legacyRemoveParams = [
            'user_id' => 2,
            'row' => 1
        ];

        $removeResult = $this->service->onRemoveUser($legacyRemoveParams);
        $this->assertIsArray($removeResult);
    }

    /**
     * @test
     */
    public function it_uses_model_configured_removal_over_custom_description()
    {
        // Arrange - provide custom description that should be overridden by model config
        $customDescription = '[CUSTOM REMOVAL MESSAGE]';
        $params = [
            'table_name' => 'users_table',
            'id' => 1,
            'row' => 0,
            'description' => $customDescription
        ];

        // Act
        $result = $this->service->onRemoveRow($params);

        // Assert
        $this->assertNotEmpty($result);
        
        // The model configuration should override the custom description
        $foundModelConfiguredMessage = false;
        foreach ($result as $update) {
            if (isset($update['text']) && $update['text'] === '[USER REMOVED]') {
                $foundModelConfiguredMessage = true;
                break;
            }
        }
        
        $this->assertTrue($foundModelConfiguredMessage, 'Model-configured removal message should override custom description');
    }

    /**
     * @test
     */
    public function it_can_get_data_model_for_valid_table()
    {
        // Use reflection to test the protected method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getDataModelForTable');
        $method->setAccessible(true);

        // Test valid table name
        $dataModel = $method->invoke($this->service, 'users_table');
        $this->assertNotNull($dataModel);
        $this->assertInstanceOf(\App\Services\UI\DataTable\UsersDataTableModel::class, $dataModel);

        // Test invalid table name
        $nullModel = $method->invoke($this->service, 'invalid_table');
        $this->assertNull($nullModel);
    }

    /**
     * @test
     */
    public function it_uses_model_configured_removal_when_no_description_provided()
    {
        // Arrange - omit description parameter
        $params = [
            'table_name' => 'users_table',
            'id' => 1,
            'row' => 0
            // No 'description' parameter
        ];

        // Act
        $result = $this->service->onRemoveRow($params);

        // Assert
        $this->assertNotEmpty($result);
        
        // Should use model-configured message, not default '[REMOVED]'
        $foundModelConfiguredMessage = false;
        foreach ($result as $update) {
            if (isset($update['text']) && $update['text'] === '[USER REMOVED]') {
                $foundModelConfiguredMessage = true;
                break;
            }
        }
        
        $this->assertTrue($foundModelConfiguredMessage, 'Model-configured removal message should be used even when description not provided');
    }
}