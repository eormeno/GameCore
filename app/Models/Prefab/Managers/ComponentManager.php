<?php

namespace App\Models\Prefab\Managers;

use App\Models\GameObject\GameObject;

class ComponentManager
{

	public function awakeComponents(GameObject $gameObject, array $initParams): void
	{
		$gameObject->components->each(function ($component) use ($initParams) {
			$component->subclass()->onAwake($initParams);
		});
	}

	public function createComponents(GameObject $gameObject, array $components): void
	{
		foreach ($components as $slug_type => $attributes) {
			$gameObject->addComponent($slug_type, $attributes);
		}
	}

	public function createStateComponents(GameObject $gameObject, array $states): void
	{
		$stateComponents = [];
		if (empty($states)) {
			return;
		}
		$defaultState = array_key_first($states) ?? null;
		$stateComponents['__initial__'] = $defaultState;
		foreach ($states as $state => $componentSettings) {
			$componentKey = array_key_first($componentSettings);
			$componentAttributes = $componentSettings[$componentKey];
			$componentAttributes['enabled'] = false;
			$component = $gameObject->addComponent($componentKey, $componentAttributes, $state);
			$stateComponents[$state] = $component->id;
		}
		$gameObject->update(['state_components' => $stateComponents]);
	}
}
