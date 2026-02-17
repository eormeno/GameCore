<?php

namespace App\GameApps\wwg\Components;

use App\Traits\HasNamespacePrefix;
use App\Models\Components\PersistentComponent;

class PlayerRoleHintStateComponent extends PersistentComponent
{
	use HasNamespacePrefix;

	public function onEnter(): void
	{
		$playerRoleHintView = $this->gameObject->findChild('player_role_hint_view');
		if ($playerRoleHintView) {
			$playerRoleHintView->activate();
		}
	}

	public function onExit(): void
	{
		$playerRoleHintView = $this->gameObject->findChild('player_role_hint_view');
		if ($playerRoleHintView) {
			$this->log('PlayerRoleHintStateComponent::onExit() found player_role_hint_view');
			$playerRoleHintView->deactivate();
		}
	}
}
