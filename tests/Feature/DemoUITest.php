<?php

use App\Services\Screens\DemoUIService;

test("1. User receives the demo UI structure", function () {
    $response = $this->get("/api/demo-ui");
    $response->assertStatus(200);
    // write($response);
    
    // Assert JSON structure with required fields for all components
    $response->assertJsonStructure([
        '*' => [
            'type',
            'parent',
            '_id',
        ]
    ]);
});

test("2. User clicks 'Test Update' button and label is updated", function () {
    // First, get the initial UI to find the btn_test_update component ID
    $initialResponse = $this->get("/api/demo-ui");
    $initialResponse->assertStatus(200);
    $initialUI = $initialResponse->json();
    
    // Find btn_test_update component and its _id
    $buttonId = null;
    foreach ($initialUI as $component) {
        if (isset($component['name']) && $component['name'] === 'btn_test_update') {
            $buttonId = $component['_id'];
            break;
        }
    }

    // Find lbl_welcome component and its _id
    $labelId = null;
    foreach ($initialUI as $component) {
        if (isset($component['name']) && $component['name'] === 'lbl_welcome') {
            $labelId = $component['_id'];
            break;
        }
    }
    
    expect($buttonId)->not->toBeNull('btn_test_update should exist in UI');
    expect($labelId)->not->toBeNull('lbl_welcome should exist in UI');

    // Simulate button click by posting to ui-event endpoint
    $response = $this->post('/api/ui-event', [
        'component_id' => $buttonId,
        'event' => 'click',
        'action' => 'test_action',
        'parameters' => []
    ]);
    
    $response->assertStatus(200);
    // write($response);

    /*
    [
        {
            "text": "¡Botón presionado! Acción ejecutada exitosamente.",
            "style": "success",
            "_id": 56155660
        },
    ]
    */

    // Assert that only ONE element is returned (the updated label)
    $responseData = $response->json();
    expect($responseData)->toBeArray();
    expect($responseData)->toHaveCount(1);

    // Assert the response structure and content
    $response->assertJsonStructure([
        '*' => [
            'text',
            'style',
            '_id'
        ]
    ]);
    
    // Verify the updated content (without checking specific _id since it's deterministic)
    expect($responseData[0]['text'])->toBe('¡Botón presionado! Acción ejecutada exitosamente.');
    expect($responseData[0]['style'])->toBe('success');
    expect($responseData[0]['_id'])->toEqual($labelId);
    
});
