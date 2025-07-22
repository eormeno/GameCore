<?php

namespace App\Models;

use App\Enums\GameState;
use App\Enums\GameUserStatus;
use App\Models\Prefab\Prefab;
use App\Models\Events\GameAppEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $prefix
 * @property string $name
 * @property string $description
 * @property int $min_age
 * @property string|null $image
 * @property string|null $prefab_name
 * @property array|null $prefab_attributes
 * @property string $client
 * @property int $width
 * @property int $height
 * @property string|null $version
 * @property int $max_instances_per_user
 * @property int $min_players_per_instance
 * @property int $max_players_per_instance
 * @property bool $allow_late_join
 * @property bool $active
 * @property array|null $service_registry
 */
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
        'allow_late_join',
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

	public function isSinglePlayer(): bool
	{
		return $this->max_players_per_instance === 1;
	}

	public function isMultiplayer(): bool
	{
		return $this->max_players_per_instance > 1;
	}

	public function allowsMultipleSaves(): bool
	{
		return $this->max_instances_per_user > 1;
	}

	public function getUserActiveGames(User $user)
	{
		return $this->games()
			->whereHas('gameUsers', function($query) use ($user) {
				$query->where('user_id', $user->id)
					  ->where('status', GameUserStatus::ACTIVE);
			})
			->whereIn('state', [GameState::WAITING, GameState::RUNNING]) // usar enum
			->with(['gameUsers' => function($query) use ($user) {
				$query->where('user_id', $user->id);
			}])
			->orderBy('created_at', 'desc')
			->get();
	}

	public function canUserCreateNewInstance(User $user): bool
	{
		$activeGameCount = $this->getUserActiveGames($user)->count();
		return $activeGameCount < $this->max_instances_per_user;
	}
}
