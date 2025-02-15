<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Events\GameEventListenerManager;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameService extends Model
{
    public $timestamps = false;
    protected $fillable = ['id', 'slug', 'type', 'game_id'];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function super(): BelongsTo
    {
        return $this->belongsTo(GameService::class, 'id');
    }

    public function subclass() : GameService
    {
        return $this->type::find($this->id);
    }

    public function events()
    {
        return $this->morphToMany(GameEventListenerManager::class, 'listenerable', 'event_listeners');
    }

    public function getService(string $slug): GameService
    {
        $parent = $this->super;
        if ($parent === null) {
            dd("No parent found for $slug");
        }
        $_game = $parent->game;
        return $_game->getService($slug);
    }
}
