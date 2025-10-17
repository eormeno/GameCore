<?php

use App\Services\UI\UIBuilder;
use App\Services\UI\Components\BaseUIBuilder;

describe('BaseUIBuilder Auto-Increment with Context', function () {
    
    test('components from same context have sequential IDs with same offset', function () {
        // Simular creación desde GameLobbyScreenService
        $button1 = UIBuilder::button('btn1');
        $button2 = UIBuilder::button('btn2');
        $button3 = UIBuilder::button('btn3');
        
        $id1 = $button1->getInternalId();
        $id2 = $button2->getInternalId();
        $id3 = $button3->getInternalId();
        
        // Verificar que son secuenciales
        expect($id2)->toBe($id1 + 1)
            ->and($id3)->toBe($id2 + 1);
    });
    
    test('internal ID is different from user-defined ID', function () {
        $button = UIBuilder::button('my_button');
        
        $internalId = $button->getInternalId();
        $json = $button->build();
        
        // El ID interno debe ser numérico
        expect($internalId)->toBeInt()
            ->and($internalId)->toBeGreaterThan(0);
        
        // El JSON debe usar el ID definido por el usuario
        expect($json)->toHaveKey('my_button');
    });
    
    test('getContextInfo returns correct information', function () {
        $info = BaseUIBuilder::getContextInfo('GameLobbyScreenService');
        
        expect($info)->toHaveKey('context')
            ->and($info)->toHaveKey('offset')
            ->and($info)->toHaveKey('current_count')
            ->and($info['context'])->toBe('GameLobbyScreenService')
            ->and($info['offset'])->toBeInt()
            ->and($info['offset'])->toBeGreaterThanOrEqual(0);
    });
    
    test('default context has offset 0', function () {
        $info = BaseUIBuilder::getContextInfo('default');
        
        expect($info['offset'])->toBe(0);
    });
    
    test('different context names generate different offsets', function () {
        $info1 = BaseUIBuilder::getContextInfo('GameLobbyScreenService');
        $info2 = BaseUIBuilder::getContextInfo('GamePlayScreenService');
        $info3 = BaseUIBuilder::getContextInfo('SettingsScreenService');
        
        // Todos deben ser diferentes
        expect($info1['offset'])->not->toBe($info2['offset'])
            ->and($info2['offset'])->not->toBe($info3['offset'])
            ->and($info1['offset'])->not->toBe($info3['offset']);
        
        // Todos deben ser múltiplos de 10000
        expect($info1['offset'] % 10000)->toBe(0)
            ->and($info2['offset'] % 10000)->toBe(0)
            ->and($info3['offset'] % 10000)->toBe(0);
    });
    
    test('same context name always generates same offset', function () {
        $info1 = BaseUIBuilder::getContextInfo('TestService');
        $info2 = BaseUIBuilder::getContextInfo('TestService');
        
        expect($info1['offset'])->toBe($info2['offset']);
    });
    
    test('internal IDs are unique across multiple components', function () {
        $components = [];
        $internalIds = [];
        
        // Crear 10 componentes de diferentes tipos
        for ($i = 1; $i <= 10; $i++) {
            $components[] = UIBuilder::button("btn$i");
        }
        
        // Obtener todos los IDs internos
        foreach ($components as $component) {
            $internalIds[] = $component->getInternalId();
        }
        
        // Verificar que todos son únicos
        $uniqueIds = array_unique($internalIds);
        expect(count($uniqueIds))->toBe(count($internalIds));
    });
});

describe('BaseUIBuilder Context Detection', function () {
    
    test('detects calling context from outside UI namespace', function () {
        // Este test se ejecuta desde Pest, así que el contexto debería ser detectado
        $button = UIBuilder::button('test');
        
        // El ID interno debe tener un offset aplicado
        $internalId = $button->getInternalId();
        
        expect($internalId)->toBeInt()
            ->and($internalId)->toBeGreaterThan(0);
    });
    
    test('components created in sequence have predictable IDs', function () {
        $button1 = UIBuilder::button('first');
        $label1 = UIBuilder::label('second');
        $button2 = UIBuilder::button('third');
        
        $id1 = $button1->getInternalId();
        $id2 = $label1->getInternalId();
        $id3 = $button2->getInternalId();
        
        // Deben ser secuenciales (mismo contexto)
        expect($id2)->toBe($id1 + 1)
            ->and($id3)->toBe($id2 + 1);
    });
});
