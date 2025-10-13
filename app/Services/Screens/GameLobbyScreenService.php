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

        // Calculate user permissions
        $userPermissions = $this->calculateUserPermissions($user, $gameApp);

        return [
            'game_lobby_screen' => [
                'game_app' => $game_app,
                'saved_games' => $saved_games,
                'permissions' => $userPermissions,
            ]
        ];
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

    /**
     * Calculate user permissions for the game app
     *
     * @param User $user
     * @param GameApp $gameApp
     * @return array
     */
    private function calculateUserPermissions(User $user, GameApp $gameApp): array
    {
        $savedGamesCount = $this->gameInstanceService->countActiveUserGameInstances($user, $gameApp);
        
        $canCreateNewGame = false;
        if ($gameApp->max_instances_per_user > 0) {
            $canCreateNewGame = $savedGamesCount < $gameApp->max_instances_per_user;
        } else {
            $canCreateNewGame = true;
        }

        return [
            'can_create_new_game' => $canCreateNewGame
        ];
    }
}
