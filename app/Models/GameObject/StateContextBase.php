<?php

namespace App\Models\GameObject;

use App\Utils\Constants;
use App\Events\GameEvent;
use App\Contracts\IStateContext;
use App\Contracts\IGameEventListener;

abstract class StateContextBase extends Base implements IStateContext, IGameEventListener
{
	public function handle(GameEvent $gameEvent): void
	{
		if (!$this->isActive()) {
			return;
		}
		$this->request($gameEvent->event);
	}

	public function request(array $event)
	{
		do {
			$stateComponent = $this->currentStateComponent();
			$stateName = $this->state;
			$stateComponent->onStart();
			$nextState = $stateComponent->handleStateEvent($event);
			$this->changeState($nextState);
			$event = Constants::EMPTY_EVENT;
		} while ($nextState !== $stateName);
	}
}
