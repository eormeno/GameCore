<?php

namespace App\GameApps\wwg\Components;

use App\Traits\HasNamespacePrefix;
use App\Models\Components\PersistentComponent;

class SpectatorStateComponent extends PersistentComponent
{
	use HasNamespacePrefix;

	public function onEnter(): void
	{
		$spectatorView = $this->gameObject->findChild('spectator_view');
		if ($spectatorView) {
			$spectatorView->activate();
		}
	}

	public function onExit(): void
	{
		$spectatorView = $this->gameObject->findChild('spectator_view');
		if ($spectatorView) {
			$this->log('SpectatorStateComponent::onExit() found spectator_view');
			$spectatorView->deactivate();
		}
	}
}
