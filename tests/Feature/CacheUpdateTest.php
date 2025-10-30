<?php

namespace Tests\Feature;

use App\Services\Screens\TableDemoService;
use Tests\TestCase;

/**
 * Test cache update functionality when modifying component attributes
 */
class CacheUpdateTest extends TestCase
{
    /**
     * @test
     */
    public function it_updates_cache_manually_after_component_modification()
    {
        // Arrange
        $service = new TableDemoService();
        $service->clearStoredUI();
        
        // Get initial UI to establish cache
        $initialUI = $service->getUI();
        
        // Find a cell with text
        $cellId = null;
        $originalText = null;
        foreach ($initialUI as $id => $component) {
            if ($component['type'] === 'tablecell' && isset($component['text']) && $component['text'] !== '') {
                $cellId = $id;
                $originalText = $component['text'];
                break;
            }
        }
        
        $this->assertNotNull($cellId, 'Should find a cell to modify');
        
        // Act - Use updateComponentCache to update directly by ID
        $newText = 'MANUALLY_UPDATED_' . time();
        $service->updateComponentCache($cellId, [
            'text' => $newText
        ]);
        
        // Assert - Get UI again, it should have the new text from cache
        $updatedUI = $service->getUI();
        $this->assertEquals($newText, $updatedUI[$cellId]['text'],
            'Cache should be updated with new text');
        $this->assertNotEquals($originalText, $updatedUI[$cellId]['text'],
            'Text should be different from original');
    }

    /**
     * @test
     */
    public function it_modifies_and_caches_in_one_operation()
    {
        // Arrange
        $service = new TableDemoService();
        $service->clearStoredUI();
        
        $initialUI = $service->getUI();
        
        // Find a cell name to modify
        $cellName = null;
        $cellId = null;
        foreach ($initialUI as $id => $component) {
            if ($component['type'] === 'tablecell' && 
                isset($component['name']) && 
                isset($component['text']) && 
                $component['text'] !== '') {
                $cellName = $component['name'];
                $cellId = $id;
                break;
            }
        }
        
        $this->assertNotNull($cellName, 'Should find a named cell');
        
        // Act - Use updateComponentCache to update in one operation
        $newText = 'ONE_OPERATION_' . time();
        $service->updateComponentCache($cellName, [
            'text' => $newText
        ]);
        
        // Assert - Get UI again, cache should be updated
        $updatedUI = $service->getUI();
        $this->assertEquals($newText, $updatedUI[$cellId]['text'],
            'modifyAndCache should update cache immediately');
    }

    /**
     * @test
     */
    public function it_modifies_multiple_properties_and_caches()
    {
        // Arrange
        $service = new TableDemoService();
        $service->clearStoredUI();
        
        $initialUI = $service->getUI();
        
        $cellId = null;
        foreach ($initialUI as $id => $component) {
            if ($component['type'] === 'tablecell' && isset($component['text'])) {
                $cellId = $id;
                break;
            }
        }
        
        // Act - Modify multiple properties
        $newText = 'MULTI_PROP_' . time();
        $service->updateComponentCache($cellId, [
            'text' => $newText,
            'align' => 'right'
        ]);
        
        // Assert
        $updatedUI = $service->getUI();
        $this->assertEquals($newText, $updatedUI[$cellId]['text'],
            'Text should be updated');
        $this->assertEquals('right', $updatedUI[$cellId]['align'],
            'Align should be updated');
    }

    /**
     * @test
     */
    public function it_throws_exception_when_component_not_found()
    {
        // Arrange
        $service = new TableDemoService();
        
        // Act & Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Component with identifier 'nonexistent_component' not found");
        
        $service->updateComponentCache('nonexistent_component', [
            'text' => 'test'
        ]);
    }

    /**
     * @test
     */
    public function it_persists_changes_across_multiple_modifications()
    {
        // Arrange
        $service = new TableDemoService();
        $service->clearStoredUI();
        
        $initialUI = $service->getUI();
        
        $cellId = null;
        foreach ($initialUI as $id => $component) {
            if ($component['type'] === 'tablecell' && isset($component['text']) && $component['text'] !== '') {
                $cellId = $id;
                break;
            }
        }
        
        // Act - Multiple sequential modifications
        $text1 = 'FIRST_' . time();
        $service->updateComponentCache($cellId, [
            'text' => $text1
        ]);
        
        $ui1 = $service->getUI();
        $this->assertEquals($text1, $ui1[$cellId]['text']);
        
        // Second modification
        $text2 = 'SECOND_' . time();
        $service->updateComponentCache($cellId, [
            'text' => $text2
        ]);
        
        // Assert - Last modification should be in cache
        $ui2 = $service->getUI();
        $this->assertEquals($text2, $ui2[$cellId]['text'],
            'Second modification should overwrite first');
    }

    /**
     * @test
     */
    public function it_works_with_chained_fluent_methods()
    {
        // Arrange
        $service = new TableDemoService();
        $service->clearStoredUI();
        
        $initialUI = $service->getUI();
        
        $cellId = null;
        foreach ($initialUI as $id => $component) {
            if ($component['type'] === 'tablecell' && isset($component['text'])) {
                $cellId = $id;
                break;
            }
        }
        
        // Act - Update multiple properties at once (simulates chained methods)
        $newText = 'CHAINED_' . time();
        $service->updateComponentCache($cellId, [
            'text' => $newText,
            'align' => 'center'
        ]);
        
        // Assert
        $updatedUI = $service->getUI();
        $this->assertEquals($newText, $updatedUI[$cellId]['text']);
        $this->assertEquals('center', $updatedUI[$cellId]['align']);
    }
}
