<?php

namespace App\Models\Prefab;

use App\Models\Game;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use App\Models\GameObject\GameObject;
use App\Models\Prefab\Managers\ComponentManager;
use App\Models\Prefab\Parsers\GameObjectNameParser;
use Illuminate\Contracts\Container\BindingResolutionException;

abstract class BaseBuilder extends Base
{
	private function gameObjectNameParser(): GameObjectNameParser
	{
		try {
			return app()->make(GameObjectNameParser::class);
		} catch (BindingResolutionException $e) {
			return new GameObjectNameParser();
		}
	}

	private function componentManager(): ComponentManager
	{
		try {
			return app()->make(ComponentManager::class);
		} catch (BindingResolutionException $e) {
			return new ComponentManager();
		}
	}

	public function buildRootGameObject(Game $game, bool $active = true, array $attributes = []): GameObject
	{
		$gameObject = DB::transaction(function () use ($game, $active, $attributes) {
			return $this->buildGameObjectFromPrefab($game, null, null, $active, $attributes);
		});
		return $gameObject;
	}

	private function buildGameObjectFromPrefab(
		Game $game,
		?GameObject $parent = null,
		?string $gameObjectName = null,	// new game object optional name
		bool $active = true,
		array $initParams = []
	): GameObject {
		if ($gameObjectName === null) {
			$gameObjectName = $this->name;
		}
		$gameObject = GameObject::create(
			[
				'name' => $gameObjectName ?? $this->name,
				'active' => $active,
				'game_object_id' => $parent?->id,
				'active_parents' => $parent ? $parent->active && $parent->active_parents : true,
				'game_id' => $game->id
			]
		);
		$this->componentManager()->createStateComponents($gameObject, $this->structure()['states'] ?? []);
		$this->componentManager()->createComponents($gameObject, $this->structure()['components'] ?? []);
		$this->createChildren($game, $gameObject, $this->structure());
		$this->componentManager()->awakeComponents($gameObject, $initParams);
		return $gameObject;
	}

	private function createChildren(Game $game, GameObject $parent, array $children): void
	{
		if (empty($children)) {
			return;
		}
		foreach ($children as $childName => $childConfig) {
			if (
				$childName === 'states' ||
				$childName === 'components' || $childName === 'active' || $childName === 'attributes'
			) {
				continue;
			}
			$this->buildChild($game, $parent, $childName, $childConfig);
		}
	}

	private function buildChild(
		Game $game,
		GameObject $parent,
		string $childName,
		array $childConfig
	): GameObject {
		$active = $this->validateActive($childConfig, $childName);
		$attributes = $childConfig['attributes'] ?? [];
		$result = $this->gameObjectNameParser()->parse($childName);
		$child = null;
		if ($result->isPrefab) {
			$foundChildPrefab = Prefab::findPrefab($result->prefab);
			$child = $foundChildPrefab->buildGameObjectFromPrefab($game, $parent, $result->name, $active, $attributes);
		} else {
			$child = GameObject::create([
				'name' => $result->name,
				'active' => $active,
				'game_object_id' => $parent->id,
				'active_parents' => $parent->active,
				'game_id' => $game->id
			]);
		}
		foreach ($childConfig as $grandChildName => $grandChildConfig) {
			if ($grandChildName === 'active' || $grandChildName === 'attributes') {
				continue;
			}
			if ($grandChildName === 'states') {
				$this->componentManager()->createStateComponents($child, $grandChildConfig);
				continue;
			}
			if ($grandChildName === 'components') {
				$this->componentManager()->createComponents($child, $grandChildConfig);
				continue;
			}
			$this->buildChild($game, $child, $grandChildName, $grandChildConfig);
		}
		$this->componentManager()->awakeComponents($child, $attributes);
		return $child;
	}

	private function validateActive(array $childConfig, string $childName): bool
	{
		if (array_key_exists('active', $childConfig)) {
			$value = $childConfig['active'];
			if (!is_bool($value)) {
				throw new InvalidArgumentException("The 'active' key must be a boolean value for $childName.");
			}
			return $value;
		}
		return true;
	}
}
