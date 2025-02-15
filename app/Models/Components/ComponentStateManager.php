<?php

namespace App\Models\Components;

use App\Utils\ReflectionUtils;

abstract class ComponentStateManager extends ComponentMessages
{
	public function handleStateEvent(array $event): string|null
	{
		$eventName = $event['event'];
		$eventData = $event['data'];
		$source = $event['source'];
		$destination = $event['destination'];
		$currentState = $this->super->gameObject->state;
		$nextState = $this->passTo();

		if (!in_array($eventName, [null, '', 'reload'])) {
			// $method = 'on' . CaseConverters::snakeToPascal($eventName) . 'Event';
			// if (method_exists($this, $method)) {
			// 	$nextState = ReflectionUtils::invokeMethod($this, $method, $eventData);
			// }
			$nextState = ReflectionUtils::invokeEventMethod($this, $event);
		}

		if (!$nextState) {
			$nextState = $currentState;
		}

		// TODO Validate the next state is a valid state for the game object

		return $nextState;
	}
}
