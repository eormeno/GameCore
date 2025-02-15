<?php

namespace App\Listeners;

use App\Events\GameEvent;
use App\Contracts\IGameEventListener;
use App\Models\Events\GameEventListenerManager;

class GameEventListener implements IGameEventListener
{
	public function handle(GameEvent $frontEvent): void
	{
		$listeners = GameEventListenerManager::listenersOf($frontEvent);
		foreach ($listeners as $listener) {
			$listener->handle($frontEvent);
		}
	}
}
