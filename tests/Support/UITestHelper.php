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
     * Assert indexed response format (following backend-ui-responses.md)
     * 
     * CRITICAL: Response must be an object indexed by _id, NOT a simple array.
     * 
     * Validates:
     * - Response is indexed by integer component IDs (not sequential 0,1,2...)
     * - Each component's _id matches its array index
     * - Components have required attributes based on context
     * 
     * Correct format:
     * {
     *   "56155660": {
     *     "_id": 56155660,
     *     "text": "Hello",
     *     "style": "success"
     *   }
     * }
     * 
     * Incorrect format (will fail):
     * [
     *   {
     *     "_id": 56155660,
     *     "text": "Hello"
     *   }
     * ]
     * 
     * @param array $response Response from ui-event endpoint or /api/demo-ui
     * @param bool $requireType Whether 'type' field is required (true for full UI, false for updates)
     * @param bool $requireParent Whether 'parent' field is required (true for full UI, false for updates)
     * @return void
     */
    public static function assertIndexedResponseFormat(
        array $response, 
        bool $requireType = false, 
        bool $requireParent = false
    ): void {
        Assert::assertIsArray($response, 'Response should be an array/object');
        Assert::assertNotEmpty($response, 'Response should not be empty');
        
        // Verify response is indexed by numeric keys (component IDs)
        $keys = array_keys($response);
        foreach ($keys as $key) {
            Assert::assertIsInt($key, "Response must be indexed by integer component IDs, found key: " . var_export($key, true));
        }
        
        // Verify each component has _id that matches its index
        foreach ($response as $componentId => $component) {
            Assert::assertIsArray($component, "Component at index $componentId should be an array");
            Assert::assertArrayHasKey('_id', $component, "Component at index $componentId must have '_id' field");
            Assert::assertIsInt($component['_id'], "Component '_id' at index $componentId must be integer");
            Assert::assertEquals(
                $componentId,
                $component['_id'],
                "Component '_id' must match its array index: expected {$componentId}, got {$component['_id']}"
            );
            
            // Check required fields based on context
            if ($requireType) {
                Assert::assertArrayHasKey('type', $component, "Component at index $componentId must have 'type' field");
            }
            
            if ($requireParent) {
                Assert::assertArrayHasKey('parent', $component, "Component at index $componentId must have 'parent' field");
            }
            
            // Verify component has attributes beyond just _id
            Assert::assertGreaterThan(1, count($component), "Component should have attributes besides _id");
        }
    }
    
    /**
     * Assert full UI structure (includes type and parent validation)
     * 
     * Use this for validating complete UI responses from endpoints like /api/demo-ui
     * 
     * @param array $ui Full UI structure
     * @return void
     */
    public static function assertUIStructure(array $ui): void
    {
        self::assertIndexedResponseFormat($ui, requireType: true, requireParent: true);
    }
    
    /**
     * Assert update response format (following backend-ui-responses.md)
     * 
     * Alias for assertIndexedResponseFormat with allowType=false
     * Use this for event responses (updates only)
     * 
     * @param array $response Response from ui-event endpoint
     * @return void
     */
    public static function assertUpdateResponseFormat(array $response): void
    {
        self::assertIndexedResponseFormat($response, false);
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

