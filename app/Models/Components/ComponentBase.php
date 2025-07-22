<?php

namespace App\Models\Components;

use App\Models\Game;
use App\Events\GameEvent;
use App\Traits\DebugHelper;
use App\Utils\ReflectionUtils;
use App\Models\GameObject\Base;
use App\Contracts\IGameEventListener;
use App\Models\GameObject\GameObject;
use Illuminate\Database\Eloquent\Model;
use App\Models\Events\GameEventListenerManager;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $type
 * @property int $game_object_id
 * @property bool $enabled
 * @property string|null $state
 * @property array|null $messages
 */
class ComponentBase extends Model implements IGameEventListener
{
	use DebugHelper;
	public $timestamps = false;
	protected $fillable = ['type', 'game_object_id', 'enabled', 'state', 'messages'];
	protected $casts = [
		'enabled' => 'boolean',
		'messages' => 'array',
	];

	public function gameObject(): BelongsTo
	{
		// if current class is Component.
		if (get_class($this) === Component::class) {
			return $this->belongsTo(GameObject::class);
		}
        // if current class is subclass of Component.
        return $this->super->gameObject();
	}

	public function game(): Game
	{
        return $this->gameObject->game;
	}

	public function handle(GameEvent $event): void
	{
		if (!$this->enabled) {
			return;
		}
		$gameObject = $this->gameObject()->first();
		if (!$gameObject->isActive()) {
			return;
		}
		ReflectionUtils::invokeEventMethod($this->subclass(), $event->event);
	}

	public function onReloadEvent($eventData): void
	{
	}

	public function events()
	{
		return $this->morphToMany(GameEventListenerManager::class, 'listenerable', 'event_listeners');
	}

	public function findGameObject(string $name): GameObject|null
	{
		return $this->game()->findGameObject($name);
	}

	public function parentGameObject(): GameObject|null
	{
		$parentGameObject = $this->gameObject()->first()->parent()->first();
		$parentIsRoot = $parentGameObject->isRoot();
		return $parentIsRoot ? null : $parentGameObject;
	}

	public function super(): BelongsTo
	{
		return $this->belongsTo(Component::class, 'id');
	}

    public function subclass(): Component
    {
        return ReflectionUtils::isSubclassOf($this->type, PersistentComponent::class)
            ? $this->type::find($this->id)
            : new $this->type($this->attributes);
    }

	public function view()
	{
		return null;
	}

	public static function createFromSlug(
		Base $gameObject,
		string $slug_type,
		array $attributes,
		string|null $forState = null
	): Component {
		$type = ReflectionUtils::componentClassFromSlug($slug_type);

		$component = $gameObject->components()->create(['type' => $type, 'enabled' => $attributes['enabled'] ?? true, 'state' => $forState]);
		unset($attributes['enabled'], $attributes['type']);


		if (ReflectionUtils::isSubclassOf($type, PersistentComponent::class)) {
			$newComponent = $type::create(array_merge(['id' => $component->id], $attributes));
			$eventMethods = ReflectionUtils::retrieveEventMethods($type);
			GameEventListenerManager::addListener($gameObject->game, $eventMethods, $newComponent);
			return $newComponent;
		}

		return new $type(array_merge(['id' => $component->id], $attributes));
		// return $type::create(array_merge(['id' => $component->id], $attributes));
	}

	public function __tostring(): string
	{
		return class_basename($this);
	}
}
