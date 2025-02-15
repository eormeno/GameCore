<?php

namespace App\Models\Events;

use App\Models\GameApp;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameAppEvent extends Model
{
	protected $fillable = ['name', 'description', 'game_app_id'];

	public $timestamps = false;

	public function setNameAttribute($value)
	{
		$this->attributes['name'] = Str::slug($value, '_');
	}

	public function gameApp(): BelongsTo
	{
		return $this->belongsTo(GameApp::class);
	}

	public function gameEventListenerManagers()
	{
		return $this->hasMany(GameEventListenerManager::class);
	}
}
