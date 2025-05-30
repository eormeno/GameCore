<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameUser extends Model
{
    protected $table = 'game_user';

    protected $fillable = [
        'game_id',
        'user_id',
        'is_owner',
        'is_administrator',
        'is_tester',
        'status',
        'join_method',
        'actioned_by',
        'reason',
        'joined_at',
        'left_at'
    ];

    protected $casts = [
        'is_owner' => 'boolean',
        'is_administrator' => 'boolean',
        'is_tester' => 'boolean',
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    // Constantes para status
    public const STATUS_PENDING_OWNER = 'pending_owner_approval';
    public const STATUS_PENDING_PLAYER = 'pending_player_acceptance';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_LEFT = 'left';
    public const STATUS_KICKED = 'kicked';
    public const STATUS_BANNED = 'banned';

    // Relaciones
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function actionedBy()
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING_OWNER,
            self::STATUS_PENDING_PLAYER
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
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isPending(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING_OWNER,
            self::STATUS_PENDING_PLAYER
        ]);
    }

    public function hasRole(string $role): bool
    {
        return match ($role) {
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
        if ($this->is_owner)
            $roles[] = 'owner';
        if ($this->is_administrator)
            $roles[] = 'administrator';
        if ($this->is_tester)
            $roles[] = 'tester';
        return $roles;
    }
}
