<?php

namespace App\Models\Events;

use App\Models\Game;
use App\Events\GameEvent;
use App\Models\GameService;
use App\Models\Components\Component;
use App\Models\GameObject\GameObject;
use Illuminate\Database\Eloquent\Model;

class GameEventListenerManager extends Model
{
	protected $fillable = ['game_app_event_id'];

	public $timestamps = false;

	public static function addListener(Game $game, array|string $eventNames, Component|GameService $listener)
	{
		$gameAppId = $game->gameApp()->first()->id;
		$eventNames = is_array($eventNames) ? $eventNames : [$eventNames];
		foreach ($eventNames as $name) {
			$gameAppEvent = GameAppEvent::firstOrCreate(['game_app_id' => $gameAppId, 'name' => $name]);
			$event = static::firstOrCreate(['game_app_event_id' => $gameAppEvent->id]);
			if (is_a($listener, Component::class)) {
				if ($listener->super->state) {
					$event->gameObjects()->syncWithoutDetaching($listener->gameObject);
					continue;
				}
				$event->components()->syncWithoutDetaching($listener);
				continue;
			}
			if (is_a($listener, GameService::class)) {
				$event->gameServices()->syncWithoutDetaching($listener);
				continue;
			}
			throw new \Exception("Listener must be a Component or GameService");
		}
	}

	public static function listenersOf(GameEvent $gameEvent)
	{
		$eventName = $gameEvent->event['event'];
		$gameAppId = $gameEvent->game->gameApp()->first()->id;
		$gameAppEvent = GameAppEvent::where(['game_app_id' => $gameAppId, 'name' => $eventName])->first();
		if (!$gameAppEvent) {
			return [];
		}
		$event = static::where(['game_app_event_id' => $gameAppEvent->id])->first();
		return $event->allListeners();
	}

	public function allListeners()
	{
		$gameObjects = $this->gameObjects()->get();
		$components = $this->components()->get();
		$gameServices = $this->gameServices()->get();
		return $gameObjects->concat($components)->concat($gameServices);
	}

	public function gameObjects()
	{
		return $this->morphedByMany(GameObject::class, 'listenerable', 'event_listeners');
	}

	public function components()
	{
		return $this->morphedByMany(Component::class, 'listenerable', 'event_listeners');
	}

	public function gameServices()
	{
		return $this->morphedByMany(GameService::class, 'listenerable', 'event_listeners');
	}


}
