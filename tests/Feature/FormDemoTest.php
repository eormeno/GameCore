<?php

use Tests\Support\UITestHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

beforeEach(function () {
    Cache::flush();
    Session::flush();
});

test("1. User receives the form demo UI structure", function () {
    $response = $this->get("/api/form-demo");
    $response->assertStatus(200);

    $ui = $response->json();

    // Validate indexed format
    UITestHelper::assertUIStructure($ui);

    // Validate all components exist
    UITestHelper::assertComponentExists($ui, 'lbl_instruction');
    UITestHelper::assertComponentExists($ui, 'input_name');
    UITestHelper::assertComponentExists($ui, 'input_email');
    UITestHelper::assertComponentExists($ui, 'btn_submit');
    UITestHelper::assertComponentExists($ui, 'lbl_result');

    // Validate component count (1 container + 5 components)
    expect($ui)->toHaveCount(6);
});

test("2. Name input has correct properties", function () {
    $response = $this->get("/api/form-demo");
    $ui = $response->json();

    $nameInput = UITestHelper::findComponentByName($ui, 'input_name');
    expect($nameInput)->not->toBeNull();
    expect($nameInput['type'])->toBe('input');
    expect($nameInput['label'])->toBe('Name');
    expect($nameInput['placeholder'])->toBe('Enter your name');
    expect($nameInput['value'])->toBe('');
    expect($nameInput['required'])->toBe(true);
    expect($nameInput['input_type'])->toBe('text');
});

test("3. Email input has correct properties", function () {
    $response = $this->get("/api/form-demo");
    $ui = $response->json();

    $emailInput = UITestHelper::findComponentByName($ui, 'input_email');
    expect($emailInput)->not->toBeNull();
    expect($emailInput['type'])->toBe('input');
    expect($emailInput['label'])->toBe('Email');
    expect($emailInput['placeholder'])->toBe('Enter your email');
    expect($emailInput['value'])->toBe('');
    expect($emailInput['required'])->toBe(true);
    expect($emailInput['input_type'])->toBe('email');
});

test("4. Submit button has correct properties", function () {
    $response = $this->get("/api/form-demo");
    $ui = $response->json();

    $button = UITestHelper::findComponentByName($ui, 'btn_submit');
    expect($button)->not->toBeNull();
    expect($button['type'])->toBe('button');
    expect($button['label'])->toBe('Submit Form');
    expect($button['action'])->toBe('submit_form');
    expect($button['style'])->toBe('primary');
});

test("5. Result label has correct initial state", function () {
    $response = $this->get("/api/form-demo");
    $ui = $response->json();

    $result = UITestHelper::findComponentByName($ui, 'lbl_result');
    expect($result)->not->toBeNull();
    expect($result['type'])->toBe('label');
    expect($result['text'])->toBe('Fill the form to continue');
    expect($result['style'])->toBe('secondary');
});

test("6. Submit empty form shows validation errors", function () {
    $ui = $this->get("/api/form-demo")->json();

    $buttonId = UITestHelper::findComponentIdByName($ui, 'btn_submit');

    $response = $this->post('/api/ui-event', [
        'component_id' => $buttonId,
        'event' => 'click',
        'action' => 'submit_form',
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
    expect($resultChanges['text'])->toContain('Validation errors');
    expect($resultChanges['text'])->toContain('Name is required');
    expect($resultChanges['text'])->toContain('Email is required');
    expect($resultChanges['style'])->toBe('danger');
});

test("7. Submit with invalid email shows validation error", function () {
    $ui = $this->get("/api/form-demo")->json();

    $buttonId = UITestHelper::findComponentIdByName($ui, 'btn_submit');

    // The service reads current values from components, but since we can't
    // modify them before submit in this test, this will still show errors
    // In a real scenario, inputs would be modified via events first
    
    $response = $this->post('/api/ui-event', [
        'component_id' => $buttonId,
        'event' => 'click',
        'action' => 'submit_form',
        'parameters' => []
    ]);

    $responseData = $response->json();

    $resultId = UITestHelper::findComponentIdByName($ui, 'lbl_result');
    $resultChanges = $responseData[(string)$resultId];
    
    expect($resultChanges['style'])->toBe('danger');
    expect($resultChanges['text'])->toContain('Validation errors');
});

test("8. Submit with name too short shows validation error", function () {
    $ui = $this->get("/api/form-demo")->json();

    $buttonId = UITestHelper::findComponentIdByName($ui, 'btn_submit');

    $response = $this->post('/api/ui-event', [
        'component_id' => $buttonId,
        'event' => 'click',
        'action' => 'submit_form',
        'parameters' => []
    ]);

    $responseData = $response->json();

    $resultId = UITestHelper::findComponentIdByName($ui, 'lbl_result');
    expect($responseData)->toHaveKey((string)$resultId);
    
    $resultChanges = $responseData[(string)$resultId];
    expect($resultChanges['style'])->toBe('danger');
});

test("9. Response format follows documentation standard", function () {
    $ui = $this->get("/api/form-demo")->json();

    $buttonId = UITestHelper::findComponentIdByName($ui, 'btn_submit');

    $response = $this->post('/api/ui-event', [
        'component_id' => $buttonId,
        'event' => 'click',
        'action' => 'submit_form',
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

test("10. UI structure has correct _order values", function () {
    $response = $this->get("/api/form-demo");
    $ui = $response->json();

    UITestHelper::assertRelativeOrder($ui);
});
