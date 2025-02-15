<?php

namespace App\GameApps\mtq\Components;

use App\Traits\HasNamespacePrefix;
use App\Models\Components\PersistentComponent;

class PlayingStateComponent extends PersistentComponent
{
	use HasNamespacePrefix;

	public function onEnter(): void
	{
		$playingView = $this->gameObject->findChild('playing_view');
		if ($playingView) {
			$playingView->activate();
		}
	}

	public function onExit(): void
	{
		$playingView = $this->gameObject->findChild('playing_view');
		if ($playingView) {
			$playingView->deactivate();
		}
	}

	public function onStartEvent(): string|null
	{
		return 'initial';
	}

}
