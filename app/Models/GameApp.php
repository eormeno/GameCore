<?php

namespace App\Models;

use App\Models\Prefab\Prefab;
use App\Models\Events\GameAppEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GameApp extends Model
{
	use HasFactory;
	public $timestamps = false;

	protected $fillable = [
		'prefix',
		'name',
		'description',
		'min_age',
		'image',
		'prefab_name',
		'prefab_attributes',
		'client',
		'width',
		'height',
		'version',
		'max_instances_per_user',
		'min_players_per_instance',
		'max_players_per_instance',
		'active',
		'service_registry',
	];

	protected $casts = [
		'active' => 'boolean',
		'prefab_attributes' => 'array',
		'service_registry' => 'array',
	];

	public function games(): HasMany
	{
		return $this->hasMany(Game::class);
	}

	public function prefab(): HasOne
	{
		return $this->hasOne(Prefab::class, 'name', 'prefab_name');
	}

	public function gameAppEvents(): HasMany
	{
		return $this->hasMany(GameAppEvent::class);
	}
}
