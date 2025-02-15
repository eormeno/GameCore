<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\GameApp;
use App\Models\Prefab\Prefab;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
		];
	}

	public function forGameApp(GameApp|string $gameApp): static
	{
		if (is_string($gameApp)) {
			$gameApp = GameApp::where('prefix', $gameApp)->first();
		}
		return $this->state(function (array $attributes) use ($gameApp) {
			return [
				'game_app_id' => $gameApp->id,
				'invitation_code' => uniqid(),
			];
		});
	}

	// After creating the Game, attach the authenticated User
	public function forAuthUser(): static
	{
		$user = auth()->user();
		return $this->afterCreating(function ($game) use ($user) {
			$game->players()->attach($user);
		});
	}

	// After creating the Game, attach the User with the given email
	public function forUserEmail(string $email): static
	{
		$user = User::where('email', $email)->first();
		return $this->afterCreating(function ($game) use ($user) {
			$game->players()->attach($user);
		});
	}

	// After creating the Game, create a GameService for each service in the GameApp
	public function withServices(): static
	{
		return $this->afterCreating(function ($game) {
			$services = $game->gameApp->service_registry;
			foreach ($services as $slug => $class_name) {
				$game->addService($slug, new $class_name());
			}
		});
	}

	// After creating the Game, instantiate the GameObject defined by the prefab
	public function withGameObject(): static
	{
		return $this->afterCreating(function ($game) {
			$appPrefabAttributes = $game->gameApp->prefab_attributes ?? [];
			$prefab = Prefab::castPrefab($game->gameApp->prefab);
			$root = $prefab->buildRootGameObject($game, true, $appPrefabAttributes)->id;
			$game->update(['game_object_id' => $root]);
		});
	}
}
