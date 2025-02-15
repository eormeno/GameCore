<?php

namespace App\GameApps\bba\Components;

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

	public function onStartEvent(): string|null
	{
		return 'playing';
	}

}
