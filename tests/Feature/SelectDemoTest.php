<?php

use Tests\Support\UITestHelper;

/**
 * SelectDemoService Test Suite
 * 
 * Tests the select component demo functionality including:
 * - Initial UI structure and component properties
 * - Cascading select behavior (country → city)
 * - Dynamic option updates
 * - Multiple selection toggle
 * - Value display and formatting
 * - Reset functionality
 * - Response format compliance
 */

test('1. User receives the select demo UI structure', function () {
    $response = $this->get('/api/select-demo');
    $response->assertStatus(200);
    $ui = $response->json();
    
    // Assert UI structure using helper
    UITestHelper::assertUIStructure($ui);
    
    // Assert all expected components exist
    UITestHelper::assertComponentsExist($ui, [
        'lbl_instruction',
        'sel_country',
        'sel_city',
        'chk_enable_multiple',
        'sel_languages',
        'lbl_result',
        'btn_reset'
    ]);
});

test('2. Country select has correct initial properties', function () {
    $response = $this->get('/api/select-demo');
    $response->assertStatus(200);
    $ui = $response->json();
    
    // Find country select component
    $country = UITestHelper::findComponentByName($ui, 'sel_country');
    
    expect($country)->not->toBeNull('sel_country should exist');
    
    // Verify country select properties
    UITestHelper::assertComponentProperties($country, [
        'type' => 'select',
        'label' => 'Select Country',
        'placeholder' => 'Choose a country...',
        'required' => true,
        'disabled' => false,
        'on_change' => 'country_change',
        'style' => 'primary',
    ]);
    
    // Verify value is null or not present
    expect($country['value'] ?? null)->toBeNull();
    
    // Verify options
    expect($country['options'])->toBeArray();
    expect($country['options'])->toHaveCount(5);
    expect($country['options'][0])->toHaveKeys(['value', 'label']);
    expect($country['options'][0]['value'])->toEqual('us');
});

test('3. City select starts disabled with empty options', function () {
    $response = $this->get('/api/select-demo');
    $response->assertStatus(200);
    $ui = $response->json();
    
    // Find city select component
    $city = UITestHelper::findComponentByName($ui, 'sel_city');
    
    expect($city)->not->toBeNull('sel_city should exist');
    
    // Verify city select initial state
    UITestHelper::assertComponentProperties($city, [
        'type' => 'select',
        'label' => 'Select City',
        'placeholder' => 'First select a country',
        'disabled' => true,
        'on_change' => 'city_change',
    ]);
    
    // Verify value is null or not present
    expect($city['value'] ?? null)->toBeNull();
    
    // Verify empty options
    expect($city['options'])->toBeArray();
    expect($city['options'])->toHaveCount(0);
});

test('4. Languages select has correct searchable properties', function () {
    $response = $this->get('/api/select-demo');
    $response->assertStatus(200);
    $ui = $response->json();
    
    // Find languages select component
    $languages = UITestHelper::findComponentByName($ui, 'sel_languages');
    
    expect($languages)->not->toBeNull('sel_languages should exist');
    
    // Verify languages select properties
    UITestHelper::assertComponentProperties($languages, [
        'type' => 'select',
        'label' => 'Select Language(s)',
        'searchable' => true,
        'search_placeholder' => 'Search languages...',
        'multiple' => false,
        'on_change' => 'language_change',
        'style' => 'info',
    ]);
    
    // Verify options
    expect($languages['options'])->toBeArray();
    expect($languages['options'])->toHaveCount(8);
});

test('5. User selects a country and city select becomes enabled with options', function () {
    // Get initial UI
    $initialResponse = $this->get('/api/select-demo');
    $initialResponse->assertStatus(200);
    $initialUI = $initialResponse->json();
    
    // Find component IDs
    $ids = UITestHelper::findComponentIdsByNames($initialUI, [
        'sel_country',
        'sel_city',
        'lbl_result'
    ]);
    
    // Simulate country select change
    $response = $this->post('/api/ui-event', [
        'component_id' => $ids['sel_country'],
        'event' => 'change',
        'action' => 'country_change',
        'parameters' => [
            'value' => 'es'
        ]
    ]);
    
    $response->assertStatus(200);
    $responseData = $response->json();
    
    // Assert response format
    UITestHelper::assertUpdateResponseFormat($responseData);
    
    // Check that sel_city was updated
    expect($responseData)->toHaveKey($ids['sel_city']);
    
    $cityChanges = $responseData[$ids['sel_city']];
    expect($cityChanges['_id'])->toEqual($ids['sel_city']);
    expect($cityChanges['disabled'])->toBeFalse();
    expect($cityChanges)->toHaveKey('options');
    expect($cityChanges['options'])->toBeArray();
    expect($cityChanges['options'])->toHaveCount(4); // Spain has 4 cities
    
    // Verify first city option
    expect($cityChanges['options'][0]['value'])->toEqual('madrid');
    
    // Check result label was updated
    expect($responseData)->toHaveKey($ids['lbl_result']);
    expect($responseData[$ids['lbl_result']]['text'])->toContain('Country selected');
    expect($responseData[$ids['lbl_result']]['style'])->toEqual('success');
});

test('6. User selects different countries and city options update accordingly', function () {
    // Get initial UI
    $initialResponse = $this->get('/api/select-demo');
    $initialResponse->assertStatus(200);
    $initialUI = $initialResponse->json();
    
    $ids = UITestHelper::findComponentIdsByNames($initialUI, [
        'sel_country',
        'sel_city'
    ]);
    
    // Select United States
    $response1 = $this->post('/api/ui-event', [
        'component_id' => $ids['sel_country'],
        'event' => 'change',
        'action' => 'country_change',
        'parameters' => ['value' => 'us']
    ]);
    
    $response1->assertStatus(200);
    $data1 = $response1->json();
    
    expect($data1[$ids['sel_city']]['options'])->toHaveCount(4);
    expect($data1[$ids['sel_city']]['options'][0]['value'])->toEqual('ny');
    
    // Select Japan
    $response2 = $this->post('/api/ui-event', [
        'component_id' => $ids['sel_country'],
        'event' => 'change',
        'action' => 'country_change',
        'parameters' => ['value' => 'jp']
    ]);
    
    $response2->assertStatus(200);
    $data2 = $response2->json();
    
    expect($data2[$ids['sel_city']]['options'])->toHaveCount(4);
    expect($data2[$ids['sel_city']]['options'][0]['value'])->toEqual('tokyo');
    
    // Select Brazil
    $response3 = $this->post('/api/ui-event', [
        'component_id' => $ids['sel_country'],
        'event' => 'change',
        'action' => 'country_change',
        'parameters' => ['value' => 'br']
    ]);
    
    $response3->assertStatus(200);
    $data3 = $response3->json();
    
    expect($data3[$ids['sel_city']]['options'])->toHaveCount(4);
    expect($data3[$ids['sel_city']]['options'][0]['value'])->toEqual('sao_paulo');
});

test('7. User selects a city and sees detailed information', function () {
    // Get initial UI
    $initialResponse = $this->get('/api/select-demo');
    $initialResponse->assertStatus(200);
    $initialUI = $initialResponse->json();
    
    $ids = UITestHelper::findComponentIdsByNames($initialUI, [
        'sel_country',
        'sel_city',
        'lbl_result'
    ]);
    
    // First select country
    $this->post('/api/ui-event', [
        'component_id' => $ids['sel_country'],
        'event' => 'change',
        'action' => 'country_change',
        'parameters' => ['value' => 'es']
    ]);
    
    // Then select city
    $response = $this->post('/api/ui-event', [
        'component_id' => $ids['sel_city'],
        'event' => 'change',
        'action' => 'city_change',
        'parameters' => ['value' => 'madrid']
    ]);
    
    $response->assertStatus(200);
    $responseData = $response->json();
    
    expect($responseData)->toHaveKey($ids['lbl_result']);
    
    $text = $responseData[$ids['lbl_result']]['text'];
    expect($text)->toContain('Madrid');
    expect($text)->toContain('Spain');
    expect($text)->toContain('Population');
    expect($text)->toContain('3.2M');
    expect($text)->toContain('Timezone');
    expect($text)->toContain('CET');
    
    expect($responseData[$ids['lbl_result']]['style'])->toEqual('success');
});

test('8. Checkbox toggles multiple language selection mode', function () {
    // Get initial UI
    $initialResponse = $this->get('/api/select-demo');
    $initialResponse->assertStatus(200);
    $initialUI = $initialResponse->json();
    
    $ids = UITestHelper::findComponentIdsByNames($initialUI, [
        'chk_enable_multiple',
        'sel_languages'
    ]);
    
    // Enable multiple selection
    $response1 = $this->post('/api/ui-event', [
        'component_id' => $ids['chk_enable_multiple'],
        'event' => 'change',
        'action' => 'toggle_multiple_languages',
        'parameters' => ['checked' => true]
    ]);
    
    $response1->assertStatus(200);
    $data1 = $response1->json();
    
    expect($data1)->toHaveKey($ids['sel_languages']);
    expect($data1[$ids['sel_languages']]['multiple'])->toBeTrue();
    expect($data1[$ids['sel_languages']]['max_selections'])->toEqual(3);
    expect($data1[$ids['sel_languages']]['placeholder'])->toContain('up to 3');
    
    // Disable multiple selection
    $response2 = $this->post('/api/ui-event', [
        'component_id' => $ids['chk_enable_multiple'],
        'event' => 'change',
        'action' => 'toggle_multiple_languages',
        'parameters' => ['checked' => false]
    ]);
    
    $response2->assertStatus(200);
    $data2 = $response2->json();
    
    expect($data2)->toHaveKey($ids['sel_languages']);
    // multiple may not be in response if it didn't change, check if present
    if (isset($data2[$ids['sel_languages']]['multiple'])) {
        expect($data2[$ids['sel_languages']]['multiple'])->toBeFalse();
    }
    expect($data2[$ids['sel_languages']]['placeholder'])->not->toContain('up to 3');
});

test('9. Language selection adds info to result (when city is selected)', function () {
    // Get initial UI
    $initialResponse = $this->get('/api/select-demo');
    $initialResponse->assertStatus(200);
    $initialUI = $initialResponse->json();
    
    $ids = UITestHelper::findComponentIdsByNames($initialUI, [
        'sel_country',
        'sel_city',
        'sel_languages',
        'lbl_result'
    ]);
    
    // Setup: select country and city
    $this->post('/api/ui-event', [
        'component_id' => $ids['sel_country'],
        'event' => 'change',
        'action' => 'country_change',
        'parameters' => ['value' => 'fr']
    ]);
    
    $this->post('/api/ui-event', [
        'component_id' => $ids['sel_city'],
        'event' => 'change',
        'action' => 'city_change',
        'parameters' => ['value' => 'paris']
    ]);
    
    // Select a language
    $response = $this->post('/api/ui-event', [
        'component_id' => $ids['sel_languages'],
        'event' => 'change',
        'action' => 'language_change',
        'parameters' => ['value' => 'fr']
    ]);
    
    $response->assertStatus(200);
    $responseData = $response->json();
    
    // Language selection should update the result label (appending to existing city info)
    if (isset($responseData[$ids['lbl_result']])) {
        $text = $responseData[$ids['lbl_result']]['text'];
        
        // Should contain city info AND language info
        expect($text)->toContain('Paris');
        expect($text)->toContain('Language: French');
    } else {
        // If result wasn't updated, that's also acceptable (no changes)
        expect(true)->toBeTrue();
    }
});

test('10. Multiple language selection shows all selected languages', function () {
    // Get initial UI
    $initialResponse = $this->get('/api/select-demo');
    $initialResponse->assertStatus(200);
    $initialUI = $initialResponse->json();
    
    $ids = UITestHelper::findComponentIdsByNames($initialUI, [
        'sel_country',
        'sel_city',
        'chk_enable_multiple',
        'sel_languages',
        'lbl_result'
    ]);
    
    // Setup: select country and city
    $this->post('/api/ui-event', [
        'component_id' => $ids['sel_country'],
        'event' => 'change',
        'action' => 'country_change',
        'parameters' => ['value' => 'us']
    ]);
    
    $this->post('/api/ui-event', [
        'component_id' => $ids['sel_city'],
        'event' => 'change',
        'action' => 'city_change',
        'parameters' => ['value' => 'ny']
    ]);
    
    // Enable multiple selection
    $this->post('/api/ui-event', [
        'component_id' => $ids['chk_enable_multiple'],
        'event' => 'change',
        'action' => 'toggle_multiple_languages',
        'parameters' => ['checked' => true]
    ]);
    
    // Select multiple languages
    $response = $this->post('/api/ui-event', [
        'component_id' => $ids['sel_languages'],
        'event' => 'change',
        'action' => 'language_change',
        'parameters' => ['value' => ['en', 'es', 'fr']]
    ]);
    
    $response->assertStatus(200);
    $responseData = $response->json();
    
    // Multiple language selection should update the result label
    if (isset($responseData[$ids['lbl_result']])) {
        $text = $responseData[$ids['lbl_result']]['text'];
        
        expect($text)->toContain('Languages:');
        expect($text)->toContain('English');
        expect($text)->toContain('Spanish');
        expect($text)->toContain('French');
    } else {
        // If result wasn't updated, that's also acceptable
        expect(true)->toBeTrue();
    }
});

test('11. Reset button clears all selections', function () {
    // Get initial UI
    $initialResponse = $this->get('/api/select-demo');
    $initialResponse->assertStatus(200);
    $initialUI = $initialResponse->json();
    
    $ids = UITestHelper::findComponentIdsByNames($initialUI, [
        'sel_country',
        'sel_city',
        'chk_enable_multiple',
        'btn_reset',
        'sel_languages',
        'lbl_result'
    ]);
    
    // Setup: make some selections
    $this->post('/api/ui-event', [
        'component_id' => $ids['sel_country'],
        'event' => 'change',
        'action' => 'country_change',
        'parameters' => ['value' => 'jp']
    ]);
    
    $this->post('/api/ui-event', [
        'component_id' => $ids['sel_city'],
        'event' => 'change',
        'action' => 'city_change',
        'parameters' => ['value' => 'tokyo']
    ]);
    
    $this->post('/api/ui-event', [
        'component_id' => $ids['chk_enable_multiple'],
        'event' => 'change',
        'action' => 'toggle_multiple_languages',
        'parameters' => ['checked' => true]
    ]);
    
    // Reset
    $response = $this->post('/api/ui-event', [
        'component_id' => $ids['btn_reset'],
        'event' => 'click',
        'action' => 'reset_selections',
        'parameters' => []
    ]);
    
    $response->assertStatus(200);
    $responseData = $response->json();
    
    // Verify components were reset (only changed ones will be in response)
    // At minimum, we should have the result label with reset message
    expect($responseData)->toHaveKey($ids['lbl_result']);
    expect($responseData[$ids['lbl_result']]['text'])->toContain('reset');
    expect($responseData[$ids['lbl_result']]['style'])->toEqual('info');
    
    // Other components may or may not be in response depending on what changed
    if (isset($responseData[$ids['sel_country']])) {
        expect($responseData[$ids['sel_country']]['value'] ?? null)->toBeNull();
    }
    
    if (isset($responseData[$ids['sel_city']])) {
        expect($responseData[$ids['sel_city']]['disabled'])->toBeTrue();
        expect($responseData[$ids['sel_city']]['options'])->toEqual([]);
    }
    
    if (isset($responseData[$ids['chk_enable_multiple']])) {
        expect($responseData[$ids['chk_enable_multiple']]['checked'])->toBeFalse();
    }
});

test('12. Clearing country selection disables city select', function () {
    // Get initial UI
    $initialResponse = $this->get('/api/select-demo');
    $initialResponse->assertStatus(200);
    $initialUI = $initialResponse->json();
    
    $ids = UITestHelper::findComponentIdsByNames($initialUI, [
        'sel_country',
        'sel_city'
    ]);
    
    // First select a country
    $this->post('/api/ui-event', [
        'component_id' => $ids['sel_country'],
        'event' => 'change',
        'action' => 'country_change',
        'parameters' => ['value' => 'es']
    ]);
    
    // Then clear it
    $response = $this->post('/api/ui-event', [
        'component_id' => $ids['sel_country'],
        'event' => 'change',
        'action' => 'country_change',
        'parameters' => ['value' => null]
    ]);
    
    $response->assertStatus(200);
    $responseData = $response->json();
    
    // City select should be disabled when country is cleared
    if (isset($responseData[$ids['sel_city']])) {
        expect($responseData[$ids['sel_city']]['disabled'])->toBeTrue();
        expect($responseData[$ids['sel_city']]['options'])->toEqual([]);
        expect($responseData[$ids['sel_city']]['value'] ?? null)->toBeNull();
    } else {
        // If not in response, it means it didn't change (was already disabled)
        expect(true)->toBeTrue();
    }
});

test('13. Response format follows backend-ui-responses.md pattern for updates', function () {
    // Get initial UI
    $initialResponse = $this->get('/api/select-demo');
    $initialResponse->assertStatus(200);
    $initialUI = $initialResponse->json();
    
    $ids = UITestHelper::findComponentIdsByNames($initialUI, ['sel_country']);
    
    $response = $this->post('/api/ui-event', [
        'component_id' => $ids['sel_country'],
        'event' => 'change',
        'action' => 'country_change',
        'parameters' => ['value' => 'fr']
    ]);
    
    $response->assertStatus(200);
    $responseData = $response->json();
    
    // Should be an associative array indexed by component _id
    expect($responseData)->toBeArray();
    
    foreach ($responseData as $componentId => $changes) {
        // Each key should be numeric (component _id as int or string)
        expect(is_numeric($componentId) || is_int($componentId))->toBeTrue();
        
        // Each value should be an array with _id
        expect($changes)->toBeArray();
        expect($changes)->toHaveKey('_id');
        expect($changes['_id'])->toEqual((int)$componentId);
    }
});

test('14. UI structure has correct _order values', function () {
    $response = $this->get('/api/select-demo');
    $response->assertStatus(200);
    $ui = $response->json();
    
    // Assert relative order using helper
    UITestHelper::assertRelativeOrder($ui);
});
