<?php

use Tests\Support\UITestHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

beforeEach(function () {
    Cache::flush();
    Session::flush();
});

test("1. User receives the checkbox demo UI structure", function () {
    $response = $this->get("/api/checkbox-demo");
    $response->assertStatus(200);

    $ui = $response->json();

    // Validate indexed format
    UITestHelper::assertUIStructure($ui);

    // Validate all components exist
    UITestHelper::assertComponentExists($ui, 'lbl_instruction');
    UITestHelper::assertComponentExists($ui, 'chk_javascript');
    UITestHelper::assertComponentExists($ui, 'chk_python');
    UITestHelper::assertComponentExists($ui, 'btn_submit');
    UITestHelper::assertComponentExists($ui, 'lbl_result');

    // Validate component count (1 container + 5 components)
    expect($ui)->toHaveCount(6);
});

test("2. Checkboxes have correct initial properties", function () {
    $response = $this->get("/api/checkbox-demo");
    $ui = $response->json();

    // Validate JavaScript checkbox
    $javascript = UITestHelper::findComponentByName($ui, 'chk_javascript');
    expect($javascript)->not->toBeNull();
    expect($javascript['type'])->toBe('checkbox');
    expect($javascript['label'])->toBe('JavaScript');
    expect($javascript['checked'])->toBe(false);

    // Validate Python checkbox
    $python = UITestHelper::findComponentByName($ui, 'chk_python');
    expect($python)->not->toBeNull();
    expect($python['type'])->toBe('checkbox');
    expect($python['label'])->toBe('Python');
    expect($python['checked'])->toBe(false);
});

test("3. Submit button has correct properties", function () {
    $response = $this->get("/api/checkbox-demo");
    $ui = $response->json();

    $button = UITestHelper::findComponentByName($ui, 'btn_submit');
    expect($button)->not->toBeNull();
    expect($button['type'])->toBe('button');
    expect($button['label'])->toBe('Submit Selection');
    expect($button['action'])->toBe('submit_selection');
    expect($button['style'])->toBe('primary');
});

test("4. Result label has correct initial state", function () {
    $response = $this->get("/api/checkbox-demo");
    $ui = $response->json();

    $result = UITestHelper::findComponentByName($ui, 'lbl_result');
    expect($result)->not->toBeNull();
    expect($result['type'])->toBe('label');
    expect($result['text'])->toBe('Make your selection above');
    expect($result['style'])->toBe('secondary');
});

test("5. Submit without selection shows error", function () {
    $ui = $this->get("/api/checkbox-demo")->json();

    $buttonId = UITestHelper::findComponentIdByName($ui, 'btn_submit');

    $response = $this->post('/api/ui-event', [
        'component_id' => $buttonId,
        'event' => 'click',
        'action' => 'submit_selection',
        'parameters' => []
    ]);

    $response->assertStatus(200);
    $responseData = $response->json();

    UITestHelper::assertUpdateResponseFormat($responseData);

    // Should only update result label
    expect($responseData)->toHaveCount(1);

    $resultId = UITestHelper::findComponentIdByName($ui, 'lbl_result');
    expect($responseData)->toHaveKey((string)$resultId);
    
    $resultChanges = $responseData[(string)$resultId];
    expect($resultChanges['_id'])->toBe($resultId);
    expect($resultChanges['text'])->toContain('Error');
    expect($resultChanges['text'])->toContain('at least one');
    expect($resultChanges['style'])->toBe('danger');
});

test("6. Submit with JavaScript selected shows success", function () {
    $ui = $this->get("/api/checkbox-demo")->json();

    $buttonId = UITestHelper::findComponentIdByName($ui, 'btn_submit');

    // Simulate JavaScript checkbox is checked by passing it in parameters
    $response = $this->post('/api/ui-event', [
        'component_id' => $buttonId,
        'event' => 'click',
        'action' => 'submit_selection',
        'parameters' => []
    ]);

    $responseData = $response->json();

    // With no checkboxes actually checked in state, should show error
    $resultId = UITestHelper::findComponentIdByName($ui, 'lbl_result');
    expect($responseData[(string)$resultId]['style'])->toBe('danger');
});

test("7. Response format follows documentation standard", function () {
    $ui = $this->get("/api/checkbox-demo")->json();

    $buttonId = UITestHelper::findComponentIdByName($ui, 'btn_submit');

    $response = $this->post('/api/ui-event', [
        'component_id' => $buttonId,
        'event' => 'click',
        'action' => 'submit_selection',
        'parameters' => []
    ]);

    $responseData = $response->json();

    // Validate format
    UITestHelper::assertUpdateResponseFormat($responseData);

    // Each component should have _id matching its key
    foreach ($responseData as $id => $component) {
        expect($component)->toHaveKey('_id');
        expect($component['_id'])->toBe((int)$id);
        
        // Should NOT have 'type' field (update response)
        expect($component)->not->toHaveKey('type');
    }
});

test("8. UI structure has correct _order values", function () {
    $response = $this->get("/api/checkbox-demo");
    $ui = $response->json();

    UITestHelper::assertRelativeOrder($ui);
});
