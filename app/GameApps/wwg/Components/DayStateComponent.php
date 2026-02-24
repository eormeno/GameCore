<?php

namespace App\GameApps\wwg\Components;

use App\Traits\HasNamespacePrefix;
use App\Models\Components\PersistentComponent;

class DayStateComponent extends PersistentComponent
{
	use HasNamespacePrefix;

	public function onEnter(): void
	{
		$initialView = $this->gameObject->findChild('day_view');
		if ($initialView) {
			$initialView->activate();
		}
	}
	public function onStartEvent(): string|null
	{
		return 'vote';
	}
	public function onExit(): void
	{
		$initialView = $this->gameObject->findChild('day_view');
		if ($initialView) {
			$initialView->deactivate();
		}
	}



}
