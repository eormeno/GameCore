<?php

namespace App\GameApps\wwg\Components;

use App\Traits\HasNamespacePrefix;
use App\Models\Components\PersistentComponent;

class VillagerNightStateComponent extends PersistentComponent
{
	use HasNamespacePrefix;

	public function onEnter(): void
	{
		$villagerNightView = $this->gameObject->findChild('villager_night_view');
		if ($villagerNightView) {
			$villagerNightView->activate();
		}
	}

	public function onExit(): void
	{
		$villagerNightView = $this->gameObject->findChild('villager_night_view');
		if ($villagerNightView) {
			$this->log('VillagerNightStateComponent::onExit() found villager_night_view');
			$villagerNightView->deactivate();
		}
	}
}
