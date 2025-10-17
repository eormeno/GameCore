<?php

/**
 * EJEMPLO PRÁCTICO: Uso del Sistema de IDs Auto-Incrementales
 * 
 * Este archivo muestra cómo el sistema funciona en un servicio real.
 * No es un archivo ejecutable, solo documentación con ejemplos.
 */

namespace App\Services\Screens;

use App\Models\User;
use App\Models\GameApp;
use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\LayoutType;

class GameLobbyScreenServiceExample
{
    public function getGameLobbyScreen(User $user, GameApp $gameApp): array
    {
        /*
         * NOTA: No necesitas hacer nada especial.
         * El sistema detecta automáticamente el contexto "GameLobbyScreenServiceExample"
         * y asigna un offset único (ej: 67890000)
         */
        
        // Crear el contenedor principal
        $container = UIBuilder::container('game_lobby_screen')
            ->slot('canvas')
            ->layout(LayoutType::VERTICAL)
            ->title('Game Lobby');
        
        // Botón "New Game" - Recibirá ID interno: 67890001
        $newGameButton = UIBuilder::button('new_game')
            ->label('New Game')
            ->action('create_game')
            ->style('primary')
            ->icon('plus');
        
        // Puedes acceder al ID interno si lo necesitas
        $internalId = $newGameButton->getInternalId();
        // $internalId será algo como: 67890001
        
        $container->add($newGameButton);
        
        // Label informativo - Recibirá ID interno: 67890002
        $infoLabel = UIBuilder::label('info')
            ->text('Select a saved game or create a new one')
            ->style('info');
        
        $container->add($infoLabel);
        
        // Tabla de partidas guardadas - Recibirá ID interno: 67890003
        $savedGamesTable = UIBuilder::table('saved_games')
            ->headers([
                'Name',
                'Last Played', 
                'Progress',
                'Actions'
            ])
            ->rows([
                [
                    'My Game 1',
                    '2025-10-16',
                    '45%',
                    UIBuilder::container('actions_1')
                        ->layout(LayoutType::HORIZONTAL)
                        ->add(UIBuilder::button('play_1')->label('Play')->style('success'))
                        ->add(UIBuilder::button('delete_1')->label('Delete')->style('danger'))
                ],
                [
                    'My Game 2',
                    '2025-10-15',
                    '78%',
                    UIBuilder::container('actions_2')
                        ->layout(LayoutType::HORIZONTAL)
                        ->add(UIBuilder::button('play_2')->label('Play')->style('success'))
                        ->add(UIBuilder::button('delete_2')->label('Delete')->style('danger'))
                ]
            ]);
        
        $container->add($savedGamesTable);
        
        /*
         * RESULTADO: JSON con IDs de usuario
         * 
         * El JSON que se retorna usa los IDs definidos por ti (strings):
         * 
         * {
         *   "game_lobby_screen": {
         *     "type": "container",
         *     "slot": "canvas",
         *     "title": "Game Lobby",
         *     "elements": {
         *       "new_game": {
         *         "type": "button",
         *         "label": "New Game",
         *         "action": "create_game"
         *       },
         *       "info": {
         *         "type": "label",
         *         "text": "Select a saved game..."
         *       },
         *       "saved_games": {
         *         "type": "table",
         *         "headers": [...]
         *       }
         *     }
         *   }
         * }
         * 
         * Pero internamente:
         * - new_game tiene ID interno: 67890001
         * - info tiene ID interno: 67890002
         * - saved_games tiene ID interno: 67890003
         * - actions_1 tiene ID interno: 67890004
         * - play_1 tiene ID interno: 67890005
         * - delete_1 tiene ID interno: 67890006
         * - actions_2 tiene ID interno: 67890007
         * - play_2 tiene ID interno: 67890008
         * - delete_2 tiene ID interno: 67890009
         */
        
        return $container->build();
    }
    
    /**
     * Ejemplo de debugging: Ver información del contexto
     */
    public function debugContextInfo(): array
    {
        $info = \App\Services\UI\Components\UIComponent::getContextInfo(
            'GameLobbyScreenServiceExample'
        );
        
        /*
         * Retorna:
         * [
         *   'context' => 'GameLobbyScreenServiceExample',
         *   'offset' => 67890000,
         *   'current_count' => 9  // Cuántos componentes se crearon
         * ]
         */
        
        return $info;
    }
}

/**
 * EJEMPLO 2: Otra pantalla con diferente offset
 */
class GamePlayScreenServiceExample
{
    public function getGamePlayScreen(): array
    {
        /*
         * Este servicio tendrá un offset DIFERENTE (ej: 78230000)
         * porque el hash CRC32 de "GamePlayScreenServiceExample" es diferente
         */
        
        $container = UIBuilder::container('gameplay')
            ->slot('canvas')
            ->layout(LayoutType::VERTICAL);
        
        // ID interno: 78230001 (diferente offset que GameLobby)
        $pauseButton = UIBuilder::button('pause')
            ->label('Pause')
            ->action('pause_game');
        
        // ID interno: 78230002
        $scoreLabel = UIBuilder::label('score')
            ->text('Score: 0');
        
        // ID interno: 78230003
        $menuButton = UIBuilder::button('menu')
            ->label('Menu')
            ->action('show_menu');
        
        $container
            ->add($pauseButton)
            ->add($scoreLabel)
            ->add($menuButton);
        
        return $container->build();
    }
}

/**
 * COMPARACIÓN: Mismo ID de usuario, diferentes IDs internos
 */
function demonstrateContextSeparation()
{
    // En GameLobbyScreenServiceExample
    $lobbyButton = UIBuilder::button('back');
    $lobbyInternalId = $lobbyButton->getInternalId();
    // $lobbyInternalId = 67890001
    
    // En GamePlayScreenServiceExample
    $gameplayButton = UIBuilder::button('back');
    $gameplayInternalId = $gameplayButton->getInternalId();
    // $gameplayInternalId = 78230001
    
    /*
     * Ambos tienen el MISMO ID de usuario: 'back'
     * Pero DIFERENTES IDs internos: 67890001 vs 78230001
     * 
     * Esto permite:
     * 1. Usar los mismos nombres de IDs en diferentes pantallas sin conflicto
     * 2. Identificar de qué pantalla viene un componente por su ID interno
     * 3. Debugging más fácil (el rango del ID te dice la pantalla)
     */
}

/**
 * VENTAJAS DEL SISTEMA
 * 
 * 1. AUTOMÁTICO:
 *    - No necesitas configurar nada
 *    - No necesitas pensar en offsets manualmente
 *    - Funciona "out of the box"
 * 
 * 2. PREDECIBLE:
 *    - Mismo servicio = mismo offset siempre
 *    - IDs secuenciales dentro del servicio
 *    - Fácil de debugear
 * 
 * 3. ESCALABLE:
 *    - Soporta hasta 9999 servicios diferentes
 *    - Cada servicio puede tener 9999 componentes
 *    - Sin colisiones prácticas
 * 
 * 4. FLEXIBLE:
 *    - ID de usuario sigue siendo string (no breaking change)
 *    - ID interno es opcional (solo si lo necesitas)
 *    - Compatible con código existente
 */
