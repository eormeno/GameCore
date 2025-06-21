<?php

namespace App\Enums;

enum GameUserRole: string
{
    case OWNER = "owner";
    case ADMINISTRATOR = "administrator";
    case TESTER = "tester";
    case PLAYER = "player";

    /**
     * Get the default role for new game-user relationships
     */
    public static function default(): self
    {
        return self::PLAYER;
    }

    /**
     * Get roles that have administrative privileges
     */
    public static function administrativeRoles(): array
    {
        return [self::OWNER, self::ADMINISTRATOR];
    }

    /**
     * Get all available roles
     */
    public static function all(): array
    {
        return [self::OWNER, self::ADMINISTRATOR, self::TESTER, self::PLAYER];
    }

    /**
     * Check if this role has administrative privileges
     */
    public function isAdministrative(): bool
    {
        return in_array($this, self::administrativeRoles());
    }

    /**
     * Check if this role is owner
     */
    public function isOwner(): bool
    {
        return $this === self::OWNER;
    }

    /**
     * Check if this role is higher than another role
     */
    public function isHigherThan(GameUserRole $role): bool
    {
        $hierarchy = [
            self::PLAYER->value => 1,
            self::TESTER->value => 2,
            self::ADMINISTRATOR->value => 3,
            self::OWNER->value => 4,
        ];

        return $hierarchy[$this->value] > $hierarchy[$role->value];
    }
}
