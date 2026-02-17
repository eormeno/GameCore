<?php

namespace App\GameApps\wwg\Components;

use App\Traits\HasNamespacePrefix;
use App\Models\Components\PersistentComponent;
use PhpParser\Node\Expr\FuncCall;

class CreateRoomStateComponent extends PersistentComponent
{
	use HasNamespacePrefix;

	public function onEnter(): void
	{
		$initialView = $this->gameObject->findChild('create_room_view');
		if ($initialView) {
			$initialView->activate();
		}
	}

	public function onExit(): void
	{
		$initialView = $this->gameObject->findChild('create_room_view');
		if ($initialView) {
			$initialView->deactivate();
		}
	}

	public function onStartEvent(): string|null
	{
		return 'night';
	}

	public function roleBasedRender()	
	{
		$roleManager = $this->findGameObject(name: 'wwg.werewolves-root-prefab')->getComponent('role-manager');
		if ($roleManager->role === 'moderator') {
			return 'moderator_view';
		} elseif ($roleManager->role === 'werewolf') {
			return 'night_action_view';
		} elseif ($roleManager->role === 'villager') {
			return 'night_idle_view';
		}
		return null;
	}

	public function activateViewBasedOnRole( string $roleName): void
	{
		$viewName = $this->roleBasedRender();
		if ($viewName) {
			$view = $this->gameObject->findChild($viewName);
			if ($view) {
				$view->activate();
			}
		}
	}

	


}
