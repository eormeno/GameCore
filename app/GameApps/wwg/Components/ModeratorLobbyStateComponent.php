<?php

namespace App\GameApps\wwg\Components;

use App\Traits\HasNamespacePrefix;
use App\Models\Components\PersistentComponent;

class ModeratorLobbyStateComponent extends PersistentComponent
{
	use HasNamespacePrefix;

	public function onEnter(): void
	{
		$moderatorLobbyView = $this->gameObject->findChild('moderator_lobby_view');
		if ($moderatorLobbyView) {
			$moderatorLobbyView->activate();
		}
	}

	public function onExit(): void
	{
		$moderatorLobbyView = $this->gameObject->findChild('moderator_lobby_view');
		if ($moderatorLobbyView) {
			$this->log('ModeratorLobbyStateComponent::onExit() found moderator_lobby_view');
			$moderatorLobbyView->deactivate();
		}
	}
}
