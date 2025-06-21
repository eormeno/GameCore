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

    // Constantes para compatibilidad con código existente
    const STATUS_ACTIVE = 'active';
    const STATUS_PENDING_OWNER = 'pending_owner_approval';
    const STATUS_PENDING_PLAYER = 'pending_player_acceptance';
    const STATUS_LEFT = 'left';
    const STATUS_KICKED = 'kicked';
    const STATUS_BANNED = 'banned';

    const JOIN_REQUEST = 'request';
    const JOIN_INVITATION = 'invitation';
    const JOIN_AUTO = 'auto';

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

    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }
        return false;
    }

    public function isOwner(): bool
    {
        return $this->role === GameUserRole::OWNER;
    }

    public function isAdministrator(): bool
    {
        return $this->role === GameUserRole::ADMINISTRATOR;
    }

    public function isTester(): bool
    {
        return $this->role === GameUserRole::TESTER;
    }

    public function isPlayer(): bool
    {
        return $this->role === GameUserRole::PLAYER;
    }

    public function hasAdministrativePrivileges(): bool
    {
        return $this->role->isAdministrative();
    }

    public function canManageRole(GameUserRole $targetRole): bool
    {
        return $this->role->isHigherThan($targetRole);
    }

    public function getRoleName(): string
    {
        return $this->role->value;
    }
}
