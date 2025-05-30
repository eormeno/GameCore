<?php

namespace App\Services;

use App\Models\Game;
use App\Models\GameApp;
use App\Models\User;
use App\Exceptions\MaxInstancesExceededException;

class GameInstanceService
{
    public function getOrCreateUserGame($user, GameApp $gameApp): Game
    {
        $count = $this->countUserGameInstances($user, $gameApp);
        if ($count < $gameApp->max_instances_per_user) {
            Game::factory()->forGameApp($gameApp)->forAuthUser()->withServices()->withGameObject()->create();
        }
        $currentGame = auth()->user()->games()->where('game_app_id', $gameApp->id)->first();
        $currentGame->gameObject;
        $currentGame->title = $gameApp->name;
        $currentGame->description = $gameApp->description;
        return $currentGame;
    }

    private function countUserGameInstances($user, GameApp $gameApp)
    {
        return $user->games()->where('game_app_id', $gameApp->id)->count();
    }

    public function getPlayerGameOptions(User $user, GameApp $gameApp): array
    {
        $activeGames = $gameApp->getUserActiveGames($user);
        $canCreateNew = $gameApp->canUserCreateNewInstance($user);

        return [
            'active_games' => $activeGames,
            'can_create_new' => $canCreateNew,
            'max_instances' => $gameApp->max_instances_per_user,
            'current_count' => $activeGames->count(),
            'game_type' => $gameApp->isSinglePlayer() ? 'single_player' : 'multiplayer'
        ];
    }

    public function validateUserCanJoinGame(User $user, GameApp $gameApp): void
    {
        if (!$gameApp->canUserCreateNewInstance($user)) {
            throw new MaxInstancesExceededException(
                "User has reached maximum instances ({$gameApp->max_instances_per_user}) for this game"
            );
        }
    }
}
