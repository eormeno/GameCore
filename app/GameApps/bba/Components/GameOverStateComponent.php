<?php

namespace App\GameApps\bba\Components;

use App\Traits\HasNamespacePrefix;
use App\Models\Components\PersistentComponent;

class GameOverStateComponent extends PersistentComponent
{
	use HasNamespacePrefix;

	public function onEnter(): void
	{
		$gameOverView = $this->gameObject->findChild('game_over_view');
		if ($gameOverView) {
			$gameOverView->activate();
		}
	}

	public function onExit(): void
	{
		$gameOverView = $this->gameObject->findChild('game_over_view');
		if ($gameOverView) {
			$this->log('GameOverStateComponent::onExit() found game_over_view');
			$gameOverView->deactivate();
		}
	}
}
