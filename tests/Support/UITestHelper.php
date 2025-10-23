<?php

namespace Tests\Support;

use PHPUnit\Framework\Assert;

/**
 * UI Test Helper
 * 
 * Provides utility methods for UI testing, including component search,
 * structure validation, and common assertions.
 */
class UITestHelper
{
    /**
     * Find component by name in UI structure
     * 
     * @param array $ui UI structure (from /api/demo-ui or similar)
     * @param string $name Component name to search for
     * @return array|null Component data if found, null otherwise
     */
    public static function findComponentByName(array $ui, string $name): ?array
    {
        foreach ($ui as $component) {
            if (isset($component['name']) && $component['name'] === $name) {
                return $component;
            }
        }
        
        return null;
    }
    
    /**
     * Find component ID by name
     * 
     * @param array $ui UI structure
     * @param string $name Component name
     * @return int|null Component _id if found, null otherwise
     */
    public static function findComponentIdByName(array $ui, string $name): ?int
    {
        $component = self::findComponentByName($ui, $name);
        return $component['_id'] ?? null;
    }
    
    /**
     * Find multiple component IDs by their names
     * 
     * @param array $ui UI structure
     * @param array $names Array of component names to find
     * @return array Associative array [name => _id]
     */
    public static function findComponentIdsByNames(array $ui, array $names): array
    {
        $result = [];
        
        foreach ($names as $name) {
            $id = self::findComponentIdByName($ui, $name);
            if ($id !== null) {
                $result[$name] = $id;
            }
        }
        
        return $result;
    }
    
    /**
     * Assert UI component structure
     * 
     * Validates that all components have required fields: type, parent, _id
     * 
     * @param array $ui UI structure
     * @return void
     */
    public static function assertUIStructure(array $ui): void
    {
        Assert::assertIsArray($ui, 'UI should be an array');
        Assert::assertNotEmpty($ui, 'UI should not be empty');
        
        foreach ($ui as $key => $component) {
            Assert::assertIsArray($component, "Component at key $key should be an array");
            Assert::assertArrayHasKey('type', $component, "Component at key $key must have 'type'");
            Assert::assertArrayHasKey('parent', $component, "Component at key $key must have 'parent'");
            Assert::assertArrayHasKey('_id', $component, "Component at key $key must have '_id'");
            Assert::assertIsInt($component['_id'], "Component _id at key $key must be integer");
        }
    }
    
    /**
     * Assert update response format (following backend-ui-responses.md)
     * 
     * Validates that response is an array where each element has:
     * - _id (integer)
     * - Modified attributes (but NOT 'type' for updates)
     * 
     * @param array $response Response from ui-event endpoint
     * @return void
     */
    public static function assertUpdateResponseFormat(array $response): void
    {
        Assert::assertIsArray($response, 'Response should be an array');
        Assert::assertNotEmpty($response, 'Response should not be empty');
        
        foreach ($response as $index => $change) {
            Assert::assertIsArray($change, "Change at index $index should be an array");
            Assert::assertArrayHasKey('_id', $change, "Change at index $index must have '_id'");
            Assert::assertIsInt($change['_id'], "Change _id at index $index must be integer");
            Assert::assertArrayNotHasKey('type', $change, "Update should not include 'type' field");
            Assert::assertGreaterThan(1, count($change), "Change should have modified attributes besides _id");
        }
    }
    
    /**
     * Assert component exists by name
     * 
     * @param array $ui UI structure
     * @param string $name Component name
     * @param string|null $message Optional custom message
     * @return void
     */
    public static function assertComponentExists(array $ui, string $name, ?string $message = null): void
    {
        $component = self::findComponentByName($ui, $name);
        $message = $message ?? "Component '$name' should exist in UI";
        Assert::assertNotNull($component, $message);
    }
    
    /**
     * Assert multiple components exist by names
     * 
     * @param array $ui UI structure
     * @param array $names Array of component names
     * @return void
     */
    public static function assertComponentsExist(array $ui, array $names): void
    {
        foreach ($names as $name) {
            self::assertComponentExists($ui, $name);
        }
    }
    
    /**
     * Assert _order is relative within each parent
     * 
     * Validates that _order starts at 1 and is sequential within each parent group
     * 
     * @param array $ui UI structure
     * @return void
     */
    public static function assertRelativeOrder(array $ui): void
    {
        // Group components by parent
        $childrenByParent = [];
        foreach ($ui as $component) {
            $parent = $component['parent'];
            $parentKey = is_string($parent) ? $parent : $parent;
            
            if (!isset($childrenByParent[$parentKey])) {
                $childrenByParent[$parentKey] = [];
            }
            
            $childrenByParent[$parentKey][] = $component;
        }
        
        // Verify _order is relative within each parent
        foreach ($childrenByParent as $parent => $children) {
            $orderedChildren = array_filter($children, fn($c) => isset($c['_order']));
            
            if (count($orderedChildren) > 1) {
                // Extract _order values
                $orders = array_map(fn($c) => $c['_order'], $orderedChildren);
                
                // Verify they start at 1 and are sequential
                sort($orders);
                Assert::assertEquals(
                    1, 
                    $orders[0], 
                    "First child of parent $parent should have _order = 1"
                );
                
                // Verify sequential
                for ($i = 0; $i < count($orders) - 1; $i++) {
                    $expected = $i + 1;
                    Assert::assertEquals(
                        $expected,
                        $orders[$i],
                        "_order should be sequential within parent $parent"
                    );
                }
            }
        }
    }
    
    /**
     * Assert component has expected properties
     * 
     * @param array $component Component data
     * @param array $expectedProperties Expected properties [key => value]
     * @return void
     */
    public static function assertComponentProperties(array $component, array $expectedProperties): void
    {
        foreach ($expectedProperties as $key => $expectedValue) {
            Assert::assertArrayHasKey($key, $component, "Component should have property '$key'");
            Assert::assertEquals(
                $expectedValue,
                $component[$key],
                "Component property '$key' should equal '$expectedValue'"
            );
        }
    }
}

