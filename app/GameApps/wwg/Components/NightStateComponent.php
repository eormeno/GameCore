<?php

namespace App\GameApps\wwg\Components;

use App\Traits\HasNamespacePrefix;
use App\Models\Components\PersistentComponent;

class NightStateComponent extends PersistentComponent
{
	use HasNamespacePrefix;

	public function onEnter(): void
	{
		$initialView = $this->gameObject->findChild('night_view');
		if ($initialView) {
			$initialView->activate();
		}
	}

	public function onExit(): void
	{
		$initialView = $this->gameObject->findChild('night_view');
		if ($initialView) {
			$initialView->deactivate();
		}
	}

	public function onStartEvent(): string|null
	{
		return 'vote';
	}

}
