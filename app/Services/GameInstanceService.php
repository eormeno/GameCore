<?php

namespace App\Services;

use App\Models\Game;
use App\Models\User;
use App\Models\GameApp;

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
}
