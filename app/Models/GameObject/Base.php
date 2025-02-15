<?php

namespace App\Models\GameObject;

use App\Models\Game;
use App\Traits\DebugHelper;
use App\Utils\ReflectionUtils;
use App\Models\Components\Component;
use Illuminate\Database\Eloquent\Model;
use App\Models\Events\GameEventListenerManager;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

abstract class Base extends Model
{
	use DebugHelper;
	protected const INITIAL_STATE = 'initial';

	public $timestamps = false;

	protected $fillable = ['name', 'version', 'active', 'active_parents', 'game_object_id', 'game_id', 'state', 'state_components', 'indexed_children'];

	protected $casts = [
		'active' => 'boolean',
		'active_parents' => 'boolean',
		'state_components' => 'array',
		'indexed_children' => 'array',
	];

	public function game(): BelongsTo
	{
		return $this->belongsTo(Game::class);
	}

	public function isRoot(): bool
	{
		return $this->game_object_id === null;
	}

	public function isActive(): bool
	{
		return $this->active && $this->active_parents;
	}

	public function components(): HasMany
	{
		return $this->hasMany(Component::class);
	}

	public function children(): HasMany
	{
		return $this->hasMany(GameObject::class, 'game_object_id');
	}

	public function events()
	{
		return $this->morphToMany(GameEventListenerManager::class, 'listenerable', 'event_listeners');
	}

	public function findChild(string $name): ?GameObject
	{
		return $this->children()->where('name', $name)->first();
	}

	public function activate(): void
	{
		if ($this->active) {
			return;
		}
		$this->update(['active' => true]);
		$this->updateActive(true);
	}

	public function deactivate(): void
	{
		if (!$this->active) {
			return;
		}
		$this->update(['active' => false]);
		$this->updateActive(false);
	}

	private function updateActive(bool $value): void
	{
		$children = $this->children()->get();
		foreach ($children as $child) {
			$child->update(['active_parents' => $value]);
			$child->updateActive($value);
		}
	}

	public function scopeActivesOfGame($query, Game $game): void
	{
		$query->where('game_id', $game->id)->where(['active' => true, 'active_parents' => true]);
	}

	public function parent(): BelongsTo
	{
		return $this->belongsTo(GameObject::class, 'game_object_id');
	}

	public function isStateManaged(): bool
	{
		return !empty($this->state_components);
	}

	protected function currentStateComponent(): ?Component
	{
		if (!$this->isStateManaged()) {
			return null;
		}
		$currentStateName = $this->state ?? $this->state_components['__initial__'];
		$currentStateComponentId = $this->state_components[$currentStateName] ?? null;
		if (!$currentStateComponentId) {
			return null;
		}
		$currentStateComponent = Component::find($currentStateComponentId)->subclass();
		if (!$this->state) {
			$this->update(['state' => $currentStateName]);
			$currentStateComponent->super()->update(['enabled' => true]);
			$currentStateComponent->onEnter();
		}
		return $currentStateComponent;
	}

	public function changeState(string $state): void
	{
		if ($this->state === $state) {
			return;
		}
		// disable the current state component
		$stateComponent = $this->currentStateComponent();
		if ($stateComponent) {
			$stateComponent->super()->update(['enabled' => false]);
			$stateComponent->onExit();
		}
		$this->update(['state' => $state]);
		// enable the new state component
		$stateComponent = $this->currentStateComponent();
		if ($stateComponent) {
			$stateComponent->super()->update(['enabled' => true]);
			$stateComponent->onEnter();
		}
	}

	public function componentsIterator(callable $callback, bool $includeDisabled = false): void {
		$query = $this->components();

		if (!$includeDisabled) {
			$query->where('enabled', true);
		}

		$components = $query->get();

		foreach ($components as $component) {
			$callback($component->subclass());
		}
	}

	public function addComponent(string $slug_type, array $attributes = [], string|null $forState = null): Component
	{
		return Component::createFromSlug($this, $slug_type, $attributes, $forState);
	}

	public function getComponent(string $slug_type): ?Component
	{
		$type = ReflectionUtils::componentClassFromSlug($slug_type);
		$component = $this->components()->where('type', $type)->first();
		return $component ? $type::find($component->id) : null;
	}

	public function removeComponent(string $slug_type): bool
	{
		$type = ReflectionUtils::componentClassFromSlug($slug_type);
		$component = $this->components()->where('type', $type)->first();
		if ($component) {
			return $component->delete(); // Esto elimina tanto el componente base como el específico por la relación
		}
		return false;
	}

	public function __tostring(): string
	{
		return $this->name;
	}
}
