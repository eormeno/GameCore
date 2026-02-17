<?php

namespace App\GameApps\wwg\Components;

use App\Traits\HasNamespacePrefix;
use App\Models\Components\PersistentComponent;

class VoteStateComponent extends PersistentComponent
{
	use HasNamespacePrefix;

	public function onEnter(): void
	{
		$initialView = $this->gameObject->findChild('vote_view');
		if ($initialView) {
			$initialView->activate();
		}
	}

	public function onExit(): void
	{
		$initialView = $this->gameObject->findChild('vote_view');
		if ($initialView) {
			$initialView->deactivate();
		}
	}

	public function onStartEvent(): string|null
	{
		return 'day';
	}

}
