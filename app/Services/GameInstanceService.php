<?php

namespace App\Services;

use App\Models\Game;
use App\Models\User;
use App\Models\GameApp;
use App\Enums\GameState;
use App\Models\GameUser;
use App\Enums\GameUserRole;
use App\Enums\GameUserStatus;
use App\Models\Prefab\Prefab;
use App\Enums\GameUserJoinMethod;

class GameInstanceService
{
	public function getOrCreateUserGame($user, GameApp $gameApp): Game
	{
		$count = $this->countActiveUserGameInstances($user, $gameApp);
		if ($count == 0) {
			$this->newGame($user, $gameApp);
		}
		$currentGame = $user->games()->where('game_app_id', $gameApp->id)->first();
		$currentGame->gameObject;
		$currentGame->title = $gameApp->name;
		$currentGame->description = $gameApp->description;
		return $currentGame;
	}

	private function newGame(
		User $user,
		GameApp $gameApp,
		GameUserRole $role = GameUserRole::OWNER
	): Game {
		// 1. Crear el Game con game_app_id e invitation_code
		$game = Game::create([
			'game_app_id' => $gameApp->id,
			'invitation_code' => uniqid(),
			'state' => GameState::RUNNING
		]);

		// 2. Crear GameUser asociando el usuario pasado por parámetro como OWNER
		$game->gameUsers()->create([
			'user_id' => $user->id,
			'role' => $role,
			'status' => GameUserStatus::ACTIVE,
			'join_method' => GameUserJoinMethod::AUTO,
			'joined_at' => now(),
		]);

		// 3. Crear los servicios del GameApp
		$services = $gameApp->service_registry;
		foreach ($services as $slug => $class_name) {
			$game->addService($slug, new $class_name());
		}

		// 4. Crear el GameObject raíz usando el prefab del GameApp
		$appPrefabAttributes = $gameApp->prefab_attributes ?? [];
		$prefab = Prefab::castPrefab($gameApp->prefab);
		$root = $prefab->buildRootGameObject($game, true, $appPrefabAttributes)->id;
		$game->update(['game_object_id' => $root]);

		return $game;
	}

	private function countActiveUserGameInstances($user, GameApp $gameApp)
	{
		$query = $user->games();
		$query->where('game_app_id', $gameApp->id);
		$query->whereIn('state', [GameState::RUNNING, GameState::PAUSED, GameState::WAITING]);
		return $query->count();
	}
}
