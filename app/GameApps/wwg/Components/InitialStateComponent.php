<?php

namespace App\GameApps\wwg\Components;

use App\Traits\HasNamespacePrefix;
use App\Models\Components\PersistentComponent;

class InitialStateComponent extends PersistentComponent
{
	use HasNamespacePrefix;

	public function onEnter(): void
	{
		$initialView = $this->gameObject->findChild('initial_view');
		if ($initialView) {
			$initialView->activate();
		}
	}

	public function onExit(): void
	{
		$initialView = $this->gameObject->findChild('initial_view');
		if ($initialView) {
			$initialView->deactivate();
		}
	}
	public function onCreateRoomEvent(): string|null
	{
		$this->assignRole(role: 'moderator');
		return 'create_room';
	}
	public function onJoinRoomEvent(): string|null
	{
		$this->assignRole(role: 'player');
		return 'join_room';
	}

	private function assignRole(string $role): void
	{
		$roleManager = $this->findGameObject(name: 'wwg.werewolves-root-prefab')->getComponent('role-manager');
		$roleManager->fill(attributes: ['role' => $role]);
		return;
	}
}
