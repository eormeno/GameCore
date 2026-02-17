<?php

namespace App\GameApps\wwg\Components;

use App\Traits\HasNamespacePrefix;
use App\Models\Components\PersistentComponent;

class WerewolfNightStateComponent extends PersistentComponent
{
	use HasNamespacePrefix;

	public function onEnter(): void
	{
		$werewolfNightView = $this->gameObject->findChild('werewolf_night_view');
		if ($werewolfNightView) {
			$werewolfNightView->activate();
		}
	}

	public function onExit(): void
	{
		$werewolfNightView = $this->gameObject->findChild('werewolf_night_view');
		if ($werewolfNightView) {
			$this->log('WerewolfNightStateComponent::onExit() found werewolf_night_view');
			$werewolfNightView->deactivate();
		}
	}
}
