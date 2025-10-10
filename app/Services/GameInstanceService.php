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
	private GameAppService $gameAppService;

	public function __construct(GameAppService $gameAppService)
	{
		$this->gameAppService = $gameAppService;
	}

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

	/**
	 * Get open games for a user and game app
	 * Open games are games that are not finished and can accept more players
	 */
	public function getOpenGames($user, GameApp $gameApp): array
	{
		$gameAppInfo = $this->gameAppService->toApiFormat($gameApp);
		
		// Get all games for this game app that the user is part except itself
		// and that are not finished nor cancelled
		$userGames = $user->games()
			->where('game_app_id', $gameApp->id)
			->whereIn('state', [GameState::RUNNING, GameState::PAUSED, GameState::WAITING])
			->orderBy('created_at', 'desc')
			->get();

		// For each game, get its id, name, invitation_code, state, users (id, name, is_owner) and created_at,
		// excluding current user, and return as array
		$openGames = $userGames->map(function ($game) use ($user) {
			return [
				'id' => $game->id,
				'name' => $game->title,
				'invitationCode' => $game->invitation_code,
				'state' => $game->state,
				'users' => $game->gameUsers->filter(function ($gu) use ($user) {
					return $gu->user_id !== $user->id;
				})->map(function ($gu) {
					return [
						'id' => $gu->user->id,
						'name' => $gu->user->name,
						'is_owner' => $gu->role === 'OWNER',
					];
				})->values(),
				'createdAt' => $game->created_at->toDateTimeString(),
			];
		})->values()->toArray();

		return [
			'open_games' => [
				'game_app' => $gameAppInfo,
				'games' => $openGames,
			]
		];
	}
}
