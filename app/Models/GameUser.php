<?php

namespace App\Models;

use App\Enums\GameUserRole;
use App\Enums\GameUserStatus;
use App\Enums\GameUserJoinMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GameUser extends Model
{
    use HasFactory;

    protected $table = 'game_user';

    protected $fillable = [
        'game_id', 'user_id', 'role',
        'status', 'join_method', 'actioned_by', 'reason',
        'joined_at', 'left_at'
    ];

    protected $casts = [
        'role' => GameUserRole::class,
        'status' => GameUserStatus::class,
        'join_method' => GameUserJoinMethod::class,
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    // Relaciones
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', GameUserStatus::ACTIVE);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            GameUserStatus::PENDING_OWNER_APPROVAL,
            GameUserStatus::PENDING_PLAYER_ACCEPTANCE
        ]);
    }

    public function scopeOwners($query)
    {
        return $query->where('role', GameUserRole::OWNER);
    }

    public function scopeAdministrators($query)
    {
        return $query->where('role', GameUserRole::ADMINISTRATOR);
    }

    public function scopeTesters($query)
    {
        return $query->where('role', GameUserRole::TESTER);
    }

    public function scopeByRole($query, GameUserRole|string $role)
    {
        if (is_string($role)) {
            return $query->where('role', $role);
        }
        return $query->where('role', $role);
    }

    public function scopeAdministrative($query)
    {
        return $query->whereIn('role', [GameUserRole::OWNER, GameUserRole::ADMINISTRATOR]);
    }

    // Métodos de estado
    public function isActive(): bool
    {
        return $this->status === GameUserStatus::ACTIVE;
    }

    public function isPending(): bool
    {
        return in_array($this->status, [
            GameUserStatus::PENDING_OWNER_APPROVAL,
            GameUserStatus::PENDING_PLAYER_ACCEPTANCE
        ]);
    }

    public function hasRole(GameUserRole|string $role): bool
    {
        if (is_string($role)) {
            $role = GameUserRole::from($role);
        }
        return $this->role === $role;
    }

    public function isOwner(): bool
    {
        return $this->role === GameUserRole::OWNER;
    }
}
