<?php

namespace App\GameApps\wwg\Components;

use App\Traits\HasNamespacePrefix;
use App\Models\Components\PersistentComponent;

class PlayerLobbyStateComponent extends PersistentComponent
{
	use HasNamespacePrefix;

	public function onEnter(): void
	{
		$playerLobbyView = $this->gameObject->findChild('player_lobby_view');
		if ($playerLobbyView) {
			$playerLobbyView->activate();
		}
	}

	public function onExit(): void
	{
		$playerLobbyView = $this->gameObject->findChild('player_lobby_view');
		if ($playerLobbyView) {
			$this->log('PlayerLobbyStateComponent::onExit() found player_lobby_view');
			$playerLobbyView->deactivate();
		}
	}
}
