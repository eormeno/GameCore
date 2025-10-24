<?php

use Tests\Support\UITestHelper;

test("1. User receives the input demo UI structure", function () {
    $response = $this->get("/api/input-demo");
    $response->assertStatus(200);
    $ui = $response->json();

    // Assert UI structure using helper
    UITestHelper::assertUIStructure($ui);

    // Assert all expected components exist
    UITestHelper::assertComponentsExist($ui, [
        'lbl_instruction',
        'input_text',
        'btn_get_value',
        'lbl_result'
    ]);
});

test("2. Input component has correct initial properties", function () {
    $response = $this->get("/api/input-demo");
    $response->assertStatus(200);
    $ui = $response->json();

    // Find input component
    $input = UITestHelper::findComponentByName($ui, 'input_text');

    expect($input)->not->toBeNull('input_text should exist');

    // Verify input properties
    UITestHelper::assertComponentProperties($input, [
        'type' => 'input',
        'value' => '',
        'required' => false,
    ]);

    expect($input)->toHaveKey('placeholder');
    expect($input['placeholder'])->toBeString();
    expect($input['_id'])->toBeInt();
});

test("3. Get Value button exists and has correct properties", function () {
    $response = $this->get("/api/input-demo");
    $response->assertStatus(200);
    $ui = $response->json();

    // Find button component
    $button = UITestHelper::findComponentByName($ui, 'btn_get_value');

    expect($button)->not->toBeNull('btn_get_value should exist');

    // Verify button properties
    UITestHelper::assertComponentProperties($button, [
        'type' => 'button',
        'action' => 'get_value',
        'style' => 'primary',
    ]);

    expect($button)->toHaveKey('label');
    expect($button['_id'])->toBeInt();
});

test("4. Result label has correct initial state", function () {
    $response = $this->get("/api/input-demo");
    $response->assertStatus(200);
    $ui = $response->json();

    // Find result label
    $resultLabel = UITestHelper::findComponentByName($ui, 'lbl_result');

    expect($resultLabel)->not->toBeNull('lbl_result should exist');

    // Verify initial properties
    UITestHelper::assertComponentProperties($resultLabel, [
        'type' => 'label',
        'text' => 'Result will appear here',
        'style' => 'default',
    ]);
});

test("5. Get Value action with non-empty input returns success response", function () {
    // Get initial UI
    $initialResponse = $this->get("/api/input-demo");
    $initialResponse->assertStatus(200);
    $initialUI = $initialResponse->json();

    // Find component IDs
    $ids = UITestHelper::findComponentIdsByNames($initialUI, [
        'btn_get_value',
        'lbl_result',
        'input_text'
    ]);

    expect($ids['btn_get_value'] ?? null)->not->toBeNull();
    expect($ids['lbl_result'] ?? null)->not->toBeNull();
    expect($ids['input_text'] ?? null)->not->toBeNull();

    // Simulate button click with input value
    $response = $this->post('/api/ui-event', [
        'component_id' => $ids['btn_get_value'],
        'event' => 'click',
        'action' => 'get_value',
        'parameters' => [
            'value' => 'Hello World'
        ]
    ]);

    $response->assertStatus(200);
    $responseData = $response->json();

    // Assert response format
    UITestHelper::assertUpdateResponseFormat($responseData);

    // Assert only result label was updated
    expect($responseData)->toHaveCount(1);
    expect($responseData)->toHaveKey($ids['lbl_result']);
    $resultChanges = $responseData[$ids['lbl_result']];

    // Verify the response content
    expect($resultChanges['_id'])->toEqual($ids['lbl_result']);
    expect($resultChanges)->toHaveKey('text');
    expect($resultChanges)->toHaveKey('style');
    expect($resultChanges['text'])->toContain('Hello World');
    expect($resultChanges['style'])->toBe('success');
});

test("6. Get Value action with empty input returns warning response", function () {
    // Get initial UI
    $initialResponse = $this->get("/api/input-demo");
    $initialResponse->assertStatus(200);
    $initialUI = $initialResponse->json();

    // Find component IDs
    $ids = UITestHelper::findComponentIdsByNames($initialUI, [
        'btn_get_value',
        'lbl_result'
    ]);

    expect($ids['btn_get_value'] ?? null)->not->toBeNull();
    expect($ids['lbl_result'] ?? null)->not->toBeNull();

    // Simulate button click with empty value
    $response = $this->post('/api/ui-event', [
        'component_id' => $ids['btn_get_value'],
        'event' => 'click',
        'action' => 'get_value',
        'parameters' => [
            'value' => ''
        ]
    ]);

    $response->assertStatus(200);
    $responseData = $response->json();

    // Assert response format
    UITestHelper::assertUpdateResponseFormat($responseData);

    // Assert only result label was updated
    expect($responseData)->toHaveCount(1);
    expect($responseData)->toHaveKey($ids['lbl_result']);
    $resultChanges = $responseData[$ids['lbl_result']];

    // Verify warning response
    expect($resultChanges['_id'])->toEqual($ids['lbl_result']);
    expect($resultChanges['text'])->toContain('empty');
    expect($resultChanges['style'])->toBe('warning');
});

test("7. Response format follows backend-ui-responses.md pattern", function () {
    // Get initial UI
    $initialResponse = $this->get("/api/input-demo");
    $initialResponse->assertStatus(200);
    $initialUI = $initialResponse->json();

    // Find button ID
    $buttonId = UITestHelper::findComponentIdByName($initialUI, 'btn_get_value');
    expect($buttonId)->not->toBeNull();

    // Trigger action
    $response = $this->post('/api/ui-event', [
        'component_id' => $buttonId,
        'event' => 'click',
        'action' => 'get_value',
        'parameters' => [
            'value' => 'Test'
        ]
    ]);

    $response->assertStatus(200);
    $responseData = $response->json();

    // Assert update response format
    UITestHelper::assertUpdateResponseFormat($responseData);

    // Verify no 'type' field in response (updates don't include type)
    foreach ($responseData as $change) {
        expect($change)->not->toHaveKey('type', 'Updates should not include type field');
    }
});

test("8. UI structure has correct _order values", function () {
    $response = $this->get("/api/input-demo");
    $response->assertStatus(200);
    $ui = $response->json();

    // Assert relative order using helper
    UITestHelper::assertRelativeOrder($ui);
});
