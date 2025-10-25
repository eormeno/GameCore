<?php

use Tests\Support\UITestHelper;

test("1. User receives the button demo UI structure", function () {
    $response = $this->get("/api/button-demo");
    
    $response->assertStatus(200);
    $ui = $response->json();
    
    // Validate UI structure
    UITestHelper::assertUIStructure($ui);
    
    // Verify component exists
    UITestHelper::assertComponentExists($ui, 'btn_toggle');
    
    // Should have container + button
    expect($ui)->toHaveCount(2);
});

test("2. Button component has correct initial properties", function () {
    $response = $this->get("/api/button-demo");
    $ui = $response->json();
    
    $button = UITestHelper::findComponentByName($ui, 'btn_toggle');
    
    // Verify button properties
    UITestHelper::assertComponentProperties($button, [
        'type' => 'button',
        'label' => 'Click Me!',
        'action' => 'toggle_label',
        'style' => 'primary'
    ]);
});

test("3. Button click toggles label to Clicked", function () {
    // Get initial UI
    $ui = $this->get("/api/button-demo")->json();
    $buttonId = UITestHelper::findComponentIdByName($ui, 'btn_toggle');
    
    // Simulate button click
    $response = $this->post('/api/ui-event', [
        'component_id' => $buttonId,
        'event' => 'click',
        'action' => 'toggle_label',
        'parameters' => []
    ]);
    
    $response->assertStatus(200);
    $responseData = $response->json();
    
    // Verify response contains button changes
    expect($responseData)->toHaveKey((string)$buttonId);
    
    // Verify label changed
    $buttonChanges = $responseData[(string)$buttonId];
    expect($buttonChanges)->toHaveKey('label');
    expect($buttonChanges['label'])->toBe('Clicked! 🎉');
});

test("4. Response format follows documentation structure", function () {
    $ui = $this->get("/api/button-demo")->json();
    $buttonId = UITestHelper::findComponentIdByName($ui, 'btn_toggle');
    
    $response = $this->post('/api/ui-event', [
        'component_id' => $buttonId,
        'event' => 'click',
        'action' => 'toggle_label',
        'parameters' => []
    ]);
    
    $responseData = $response->json();
    
    // Response should be indexed by component ID
    expect($responseData)->toBeArray();
    expect($responseData)->toHaveKey((string)$buttonId);
    
    // Component data should have _id field
    $buttonChanges = $responseData[(string)$buttonId];
    expect($buttonChanges)->toHaveKey('_id');
    expect($buttonChanges['_id'])->toBe((int)$buttonId);
});

test("5. Container has child components in correct order", function () {
    $response = $this->get("/api/button-demo");
    $ui = $response->json();
    
    // Find container
    $container = null;
    foreach ($ui as $component) {
        if ($component['type'] === 'container') {
            $container = $component;
            break;
        }
    }
    
    expect($container)->not->toBeNull();
    
    // Verify button exists as child
    $buttonId = UITestHelper::findComponentIdByName($ui, 'btn_toggle');
    expect($buttonId)->not->toBeNull();
    
    // Count total components
    expect($ui)->toHaveCount(2); // Container + button
});
