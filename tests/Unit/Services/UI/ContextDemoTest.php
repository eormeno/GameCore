<?php

use App\Services\UI\UIBuilder;
use App\Services\UI\Components\UIComponent;

/**
 * Test de demostración: Simulación de múltiples servicios de pantallas
 * creando componentes con IDs automáticos contextualizados
 */
describe('Context-Based Auto-Increment Demo', function () {
    
    test('demonstrates context-based ID generation with multiple screen services', function () {
        // Simular GameLobbyScreenService creando componentes
        $lobbyComponents = createLobbyScreen();
        
        // Simular GamePlayScreenService creando componentes  
        $gameplayComponents = createGameplayScreen();
        
        // Simular SettingsScreenService creando componentes
        $settingsComponents = createSettingsScreen();
        
        // Obtener información de contextos
        $lobbyInfo = UIComponent::getContextInfo('ClosureOne');  // Nombre del closure del test
        $gameplayInfo = UIComponent::getContextInfo('ClosureOne');
        $settingsInfo = UIComponent::getContextInfo('ClosureOne');
        
        // Verificar que todos los componentes de Lobby tienen IDs con el mismo offset
        $lobbyIds = array_map(fn($c) => $c->getInternalId(), $lobbyComponents);
        $lobbyOffsets = array_map(fn($id) => floor($id / 10000) * 10000, $lobbyIds);
        $uniqueLobbyOffset = array_unique($lobbyOffsets);
        
        expect(count($uniqueLobbyOffset))->toBe(1) // Todos tienen el mismo offset
            ->and($lobbyIds)->toHaveCount(4) // 4 componentes creados
            ->and($lobbyIds[0])->toBeLessThan($lobbyIds[1]) // IDs son secuenciales
            ->and($lobbyIds[1])->toBeLessThan($lobbyIds[2])
            ->and($lobbyIds[2])->toBeLessThan($lobbyIds[3]);
    });
    
    test('shows internal ID vs user-defined ID difference', function () {
        $button = UIBuilder::button('my_custom_id');
        
        $internalId = $button->getInternalId();
        $json = $button->build();
        
        // El ID interno es numérico y puede ser grande
        expect($internalId)->toBeInt()
            ->and($internalId)->toBeGreaterThan(0);
        
        // El JSON usa el ID definido por el usuario (string)
        expect($json)->toHaveKey('my_custom_id')
            ->and($json['my_custom_id'])->toBeArray()
            ->and($json['my_custom_id']['type'])->toBe('button');
        
        // El usuario no ve el ID interno en el JSON por defecto
        expect($json['my_custom_id'])->not->toHaveKey('_id');
    });
    
    test('demonstrates offset calculation for different services', function () {
        $services = [
            'GameLobbyScreenService',
            'GamePlayScreenService',
            'SettingsScreenService',
            'ProfileScreenService',
            'LeaderboardScreenService',
        ];
        
        $offsets = [];
        foreach ($services as $service) {
            $info = UIComponent::getContextInfo($service);
            $offsets[$service] = $info['offset'];
        }
        
        // Todos los offsets deben ser diferentes
        $uniqueOffsets = array_unique(array_values($offsets));
        expect(count($uniqueOffsets))->toBe(count($services));
        
        // Todos deben ser múltiplos de 10000
        foreach ($offsets as $offset) {
            expect($offset % 10000)->toBe(0);
        }
        
        // Mostrar los offsets (útil para debugging)
        // dump($offsets);
    });
});

// Funciones auxiliares para simular servicios
function createLobbyScreen(): array
{
    return [
        UIBuilder::button('new_game'),
        UIBuilder::label('title'),
        UIBuilder::button('load_game'),
        UIBuilder::table('saved_games'),
    ];
}

function createGameplayScreen(): array
{
    return [
        UIBuilder::button('pause'),
        UIBuilder::label('score'),
        UIBuilder::button('menu'),
    ];
}

function createSettingsScreen(): array
{
    return [
        UIBuilder::button('save'),
        UIBuilder::button('cancel'),
        UIBuilder::label('settings_title'),
    ];
}
