<?php

namespace App\Models;

use ReflectionClass;
use App\Contracts\IPersistent;
use App\Models\GameObject\GameObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Game extends Model
{
	use HasFactory;
	protected $fillable = ['invitation_code', 'game_app_id', 'game_object_id', 'elapsed'];

	public function gameApp(): BelongsTo
	{
		return $this->belongsTo(GameApp::class);
	}

	public function gameObject(): HasOne
	{
		return $this->hasOne(GameObject::class, 'id', 'game_object_id');
	}

	public function gameObjects(): HasMany
	{
		return $this->hasMany(GameObject::class);
	}

	public function findGameObject(string $name): GameObject|null
	{
		return $this->gameObjects->firstWhere('name', $name);
	}

	public function players(): BelongsToMany
	{
		return $this->belongsToMany(User::class)->withTimestamps();
	}

	public function services(): HasMany
	{
		return $this->hasMany(GameService::class);
	}

	public function addService(string $slug, GameService $service): void
	{
		// TODO Enviar este código a un helper o a la clase GameService como método estático
		$reflection_class = new ReflectionClass($service);
		$new_service = $this->services()->create([
			'game_id' => $this->id,
			'type' => $reflection_class->getName(),
			'slug' => $slug,
		]);
		// If the service is persistent, create a record in the services table
		if ($reflection_class->implementsInterface(IPersistent::class)) {
			$service::create(['id' => $new_service->id]);
		}
	}

	public function getService(string $slug): GameService
	{
		$service = $this->services->firstWhere('slug', $slug);
		$type = $service->type;
		// TODO Enviar este código a un helper o a la clase GameService como método estático
		// Check the service inherits from GameService and implements IPersistent interface
		if (
			!is_subclass_of($type, GameService::class) ||
			!in_array(IPersistent::class, class_implements($type))
		) {
			return new $type(['id' => $service->id]);
		}
		return $type::find($service->id);
	}
}
