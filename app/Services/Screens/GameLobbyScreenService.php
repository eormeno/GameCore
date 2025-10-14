<?php

namespace App\Services\Screens;

use App\Models\User;
use App\Models\GameApp;
use App\Services\GameAppService;
use App\Services\GameInstanceService;

class GameLobbyScreenService
{
    private GameAppService $gameAppService;
    private GameInstanceService $gameInstanceService;

    public function __construct(
        GameAppService $gameAppService,
        GameInstanceService $gameInstanceService
    ) {
        $this->gameAppService = $gameAppService;
        $this->gameInstanceService = $gameInstanceService;
    }

    /**
     * Get the game lobby screen data for a user and game app
     *
     * @param User $user
     * @param GameApp $gameApp
     * @return array
     */
    public function getGameLobbyScreen(User $user, GameApp $gameApp): array
    {
        // Get game app in API format
        $game_app = $this->gameAppService->gameAppToApiFormat($gameApp);

        // Get saved games for the user
        $savedGames = $this->gameInstanceService->getSavedGames($user, $gameApp);
        $saved_games = $this->gameInstanceService->toApiFormat($savedGames);

        $ui = $this->uiElements($user, $gameApp);

        return [
            'game_lobby_screen' => [
                'game_app' => $game_app,
                'saved_games' => $saved_games,
                'ui' => $ui,
            ]
        ];
    }

    /**
     * Define UI elements for the game lobby screen
     * 
     * @return array
     */
    private function uiElements(User $user, GameApp $gameApp): array
    {
        $canCreateNewGame = $this->canCreateNewGame($user, $gameApp);
        $maxInstances = $gameApp->max_instances_per_user;

        $newGameButtonTooltip = $canCreateNewGame ? t('new_game_button_tooltip') : t('cannot_create_new_game');
        $warningMessage = $canCreateNewGame ? null : t('instances_limit_reached', ['max' => $maxInstances]);

        return [
            'new_game_button' => [
                'label' => t('new_game_button_label'),
                'tooltip' => $newGameButtonTooltip,
                'action' => 'create_new_game',
                'enabled' => $canCreateNewGame,
                'icon' => 'plus',
                'style' => 'primary',
            ],

            'warning_message_label' => $warningMessage,
            'saved_games_label' => t('saved_games_description'),
            'saved_games_list' => [
                'empty_message' => t('no_saved_games_message'),
                'item_actions' => [
                    'load' => [
                        'label' => t('load_game_action'),
                        'action' => 'load_game',
                        'icon' => 'play',
                        'style' => 'success',
                    ],
                    'delete' => [
                        'label' => t('delete_game_action'),
                        'action' => 'delete_game',
                        'icon' => 'trash',
                        'style' => 'danger',
                    ],
                ],
            ],
        ];
    }

    private function canCreateNewGame(User $user, GameApp $gameApp): bool
    {
        $savedGamesCount = $this->gameInstanceService->countActiveUserGameInstances($user, $gameApp);

        if ($gameApp->max_instances_per_user > 0) {
            return $savedGamesCount < $gameApp->max_instances_per_user;
        }

        return true; // No limit
    }

    /**
     * Get the expected response structure (useful for validation/testing)
     * 
     * @return array
     */
    public static function getResponseStructure(): array
    {
        return GameLobbyScreenStructure::response();
    }
}
