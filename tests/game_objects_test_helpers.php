<?php

use App\Models\Game;
use App\Models\GameApp;
use App\Models\Prefab\Prefab;
use App\Utils\ReflectionUtils;
use App\Models\GameObject\GameObject;

function existsGameObjectsInDatabase(array $rows): void
{
	$table = 'game_objects';
	$columns = ['id', 'name', 'game_id', 'game_object_id', 'state'];
	foreach ($rows as $id => $row) {
		test()->assertDatabaseHas($table, array_combine($columns, array_merge([$id], $row))); // phpcs:ignore
	}
}

function existsRootPrefab(string $prefix): Prefab
{
	$gameApp = reloadGameApps($prefix);
	$prefab = Prefab::castPrefab($gameApp->prefab);
	test()->assertNotNull($prefab);
	return $prefab;
}


function rootGameObjectIsCreated(Game $game): GameObject
{
	$rootGameObject = GameObject::where('game_id', $game->id)->where('game_object_id', null)->first();
	test()->assertNotNull($rootGameObject);
	return $rootGameObject;
}

function gameObjectHasComponents(Prefab $prefab, GameObject $gameObject): void
{
	$definedComponents = getDefinedComponents($prefab);
	$components = $gameObject->components()->get();
	test()->assertEquals(count($components), count($definedComponents));
	foreach ($components as $component) {
		//$component->type = substr(strrchr($component->type, "\\"), 1);
		test()->assertTrue(in_array($component->type, $definedComponents), "Component type {$component->type} is not in the list of expected components");
	}
}

function getDefinedComponents(Prefab $prefab): array
{
	$ret = [];
	// get the components associated to states
	$states = $prefab->structure()['states'] ?? [];
	foreach ($states as $state => $value) {
		$slug_type = array_key_first($value);
		$ret[] = ReflectionUtils::componentClassFromSlug($slug_type);
	}
	// get the components defined in the prefab
	$components = $prefab->structure()['components'] ?? [];
	foreach ($components as $slug_type => $value) {
		$ret[] = ReflectionUtils::componentClassFromSlug($slug_type);
	}
	return $ret;
}
