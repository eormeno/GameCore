<?php

namespace App\Models;

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
        'game_id', 'user_id', 'is_owner', 'is_administrator', 'is_tester',
        'status', 'join_method', 'actioned_by', 'reason',
        'joined_at', 'left_at'
    ];

    protected $casts = [
        'status' => GameUserStatus::class,
        'join_method' => GameUserJoinMethod::class,
        'is_owner' => 'boolean',
        'is_administrator' => 'boolean',
        'is_tester' => 'boolean',
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
        return $query->where('is_owner', true);
    }

    public function scopeAdministrators($query)
    {
        return $query->where('is_administrator', true);
    }

    public function scopeTesters($query)
    {
        return $query->where('is_tester', true);
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

    public function hasRole(string $role): bool
    {
        return match($role) {
            'owner' => $this->is_owner,
            'administrator' => $this->is_administrator,
            'tester' => $this->is_tester,
            default => false
        };
    }

    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }
        return false;
    }

    public function getRoles(): array
    {
        $roles = [];
        if ($this->is_owner) $roles[] = 'owner';
        if ($this->is_administrator) $roles[] = 'administrator';
        if ($this->is_tester) $roles[] = 'tester';
        return $roles;
    }
}
