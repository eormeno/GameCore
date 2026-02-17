<?php

namespace App\GameApps\wwg\Components;

use App\Traits\HasNamespacePrefix;
use App\Models\Components\PersistentComponent;

class ModeratorPlayingStateComponent extends PersistentComponent
{
	use HasNamespacePrefix;

	public function onEnter(): void
	{
		$moderatorPlayingView = $this->gameObject->findChild('moderator_playing_view');
		if ($moderatorPlayingView) {
			$moderatorPlayingView->activate();
		}
	}

	public function onExit(): void
	{
		$moderatorPlayingView = $this->gameObject->findChild('moderator_playing_view');
		if ($moderatorPlayingView) {
			$this->log('ModeratorPlayingStateComponent::onExit() found moderator_playing_view');
			$moderatorPlayingView->deactivate();
		}
	}
}
