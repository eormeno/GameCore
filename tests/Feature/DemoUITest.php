<?php

use Tests\Support\UITestHelper;

test("1. User receives the demo UI structure", function () {
    $response = $this->get("/api/demo-ui");
    $response->assertStatus(200);
    $ui = $response->json();

    // Assert UI structure using helper
    UITestHelper::assertUIStructure($ui);
});

test("2. User clicks 'Test Update' button and label is updated", function () {
    // Get initial UI
    $initialResponse = $this->get("/api/demo-ui");
    $initialResponse->assertStatus(200);
    $initialUI = $initialResponse->json();

    // Find component IDs using helper
    $ids = UITestHelper::findComponentIdsByNames($initialUI, [
        'btn_test_update',
        'lbl_welcome'
    ]);

    expect($ids['btn_test_update'] ?? null)->not->toBeNull('btn_test_update should exist');
    expect($ids['lbl_welcome'] ?? null)->not->toBeNull('lbl_welcome should exist');

    // Simulate button click
    $response = $this->post('/api/ui-event', [
        'component_id' => $ids['btn_test_update'],
        'event' => 'click',
        'action' => 'test_action',
        'parameters' => []
    ]);

    $response->assertStatus(200);
    $responseData = $response->json();

    // Assert response format (indexed by component ID)
    expect($responseData)->toHaveCount(1);
    expect($responseData)->toHaveKey($ids['lbl_welcome']);
    
    // Verify component changes
    $labelChanges = $responseData[$ids['lbl_welcome']];
    expect($labelChanges['text'])->toBe('¡Botón presionado! Acción ejecutada exitosamente.');
    expect($labelChanges['style'])->toBe('success');
    expect($labelChanges['_id'])->toEqual($ids['lbl_welcome']);
});

test("3. Counter starts at 0", function () {
    // Get initial UI
    $response = $this->get("/api/demo-ui");
    $response->assertStatus(200);
    $ui = $response->json();

    // Find lbl_counter component using helper
    $counterLabel = UITestHelper::findComponentByName($ui, 'lbl_counter');

    expect($counterLabel)->not->toBeNull('lbl_counter should exist in UI');

    // Assert expected properties
    UITestHelper::assertComponentProperties($counterLabel, [
        'text' => '0',
    ]);
    expect($counterLabel['_id'])->toBeInt();
});

test("4. User clicks 'Increment' button and counter value updates", function () {
    // Get initial UI
    $response = $this->get("/api/demo-ui");
    $response->assertStatus(200);
    $initialUI = $response->json();

    // Find component IDs using helper
    $ids = UITestHelper::findComponentIdsByNames($initialUI, [
        'btn_increment',
        'lbl_counter'
    ]);

    $buttonId = $ids['btn_increment'] ?? null;
    $counterId = $ids['lbl_counter'] ?? null;

    expect($buttonId)->not->toBeNull('btn_increment should exist');
    expect($counterId)->not->toBeNull('lbl_counter should exist');

    // Click increment button and verify response structure
    $response = $this->post('/api/ui-event', [
        'component_id' => $buttonId,
        'event' => 'click',
        'action' => 'increment_counter',
        'parameters' => []
    ]);

    $response->assertStatus(200);
    $responseData = $response->json();
    
    // Verify response structure (indexed by component ID)
    expect($responseData)->toHaveCount(1, 'Only one component should be updated');
    expect($responseData)->toHaveKey($counterId);
    
    // Verify counter changes
    $counterChanges = $responseData[$counterId];
    expect($counterChanges)->toHaveKey('text');
    expect($counterChanges)->toHaveKey('_id');
    expect($counterChanges['_id'])->toEqual($counterId);
    
    // Verify counter value is numeric and positive
    expect($counterChanges['text'])->toBeNumeric("Counter should be numeric");
    expect((int)$counterChanges['text'])->toBeGreaterThanOrEqual(1, "Counter should be at least 1");
    
    // Style may or may not be present depending on whether it changed
    if (isset($counterChanges['style'])) {
        expect($counterChanges['style'])->toBeString();
    }
});

test("5. Increment action returns correct response format", function () {
    // Get initial UI
    $initialResponse = $this->get("/api/demo-ui");
    $initialResponse->assertStatus(200);
    $initialUI = $initialResponse->json();

    // Find component IDs using helper
    $ids = UITestHelper::findComponentIdsByNames($initialUI, [
        'btn_increment',
        'lbl_counter'
    ]);

    $buttonId = $ids['btn_increment'] ?? null;
    $counterId = $ids['lbl_counter'] ?? null;

    expect($buttonId)->not->toBeNull();
    expect($counterId)->not->toBeNull();

    // Trigger increment action
    $response = $this->post('/api/ui-event', [
        'component_id' => $buttonId,
        'event' => 'click',
        'action' => 'increment_counter',
        'parameters' => []
    ]);

    $response->assertStatus(200);
    $responseData = $response->json();

    // Verify response format (indexed by component ID)
    expect($responseData)->toBeArray();
    expect($responseData)->toHaveCount(1);
    expect($responseData)->toHaveKey($counterId);

    // Verify structure
    $counterChanges = $responseData[$counterId];
    expect($counterChanges)->toHaveKey('_id');
    expect($counterChanges)->toHaveKey('text');
    expect($counterChanges['_id'])->toEqual($counterId);

    // Verify text is numeric
    expect($counterChanges['text'])->toBeString();
    
    // Style may or may not be present depending on whether it changed
    if (isset($counterChanges['style'])) {
        expect($counterChanges['style'])->toBeString();
    }
    expect(is_numeric($counterChanges['text']))->toBeTrue('Counter text should be numeric');
});

test("6. Decrement action returns correct response format", function () {
    // Get initial UI
    $initialResponse = $this->get("/api/demo-ui");
    $initialResponse->assertStatus(200);
    $initialUI = $initialResponse->json();

    // Find component IDs using helper
    $ids = UITestHelper::findComponentIdsByNames($initialUI, [
        'btn_decrement',
        'lbl_counter'
    ]);

    $decrementButtonId = $ids['btn_decrement'] ?? null;
    $counterId = $ids['lbl_counter'] ?? null;

    expect($decrementButtonId)->not->toBeNull();
    expect($counterId)->not->toBeNull();

    // Trigger decrement action
    $response = $this->post('/api/ui-event', [
        'component_id' => $decrementButtonId,
        'event' => 'click',
        'action' => 'decrement_counter',
        'parameters' => []
    ]);

    $response->assertStatus(200);
    $responseData = $response->json();

    // Assert response format
    expect($responseData)->toBeArray();
    expect($responseData)->toHaveCount(1);

    // Verify response is indexed by component ID
    expect($responseData)->toHaveKey($counterId);
    $counterChanges = $responseData[$counterId];

    // Verify structure
    expect($counterChanges)->toHaveKey('_id');
    expect($counterChanges)->toHaveKey('text');
    expect($counterChanges)->toHaveKey('style');
    expect($counterChanges['_id'])->toEqual($counterId);

    // Verify text is numeric
    expect($counterChanges['text'])->toBeString();
    expect(is_numeric($counterChanges['text']))->toBeTrue('Counter text should be numeric');
});

test("7. Response format follows backend-ui-responses.md pattern for updates", function () {
    // Get initial UI
    $initialResponse = $this->get("/api/demo-ui");
    $initialResponse->assertStatus(200);
    $initialUI = $initialResponse->json();

    // Find test button using helper
    $buttonId = UITestHelper::findComponentIdByName($initialUI, 'btn_test_update');

    expect($buttonId)->not->toBeNull();

    // Trigger update
    $response = $this->post('/api/ui-event', [
        'component_id' => $buttonId,
        'event' => 'click',
        'action' => 'test_action',
        'parameters' => []
    ]);

    $response->assertStatus(200);
    $responseData = $response->json();

    // Assert update response format using helper
    UITestHelper::assertUpdateResponseFormat($responseData);
});

test("8. UI structure has correct _order values (relative to parent)", function () {
    $response = $this->get("/api/demo-ui");
    $response->assertStatus(200);
    $ui = $response->json();

    // Assert relative order using helper
    UITestHelper::assertRelativeOrder($ui);
});
