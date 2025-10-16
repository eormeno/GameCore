<?php

namespace App\Enums;

enum GameUserRole: string
{
    case OWNER = "owner";
    case ADMINISTRATOR = "administrator";
    case TESTER = "tester";
    case PLAYER = "player";

    /**
     * Get the translated label for this role
     */
    public function label(): string
    {
        return t("enum.game_user_role.{$this->value}");
    }

    /**
     * Get the translated description for this role
     */
    public function description(): string
    {
        return t("enum.game_user_role.{$this->value}_description");
    }

    /**
     * Get a color/badge variant for UI display
     */
    public function color(): string
    {
        return match($this) {
            self::OWNER => 'primary',
            self::ADMINISTRATOR => 'warning',
            self::TESTER => 'info',
            self::PLAYER => 'success',
        };
    }

    /**
     * Get an icon name for UI display
     */
    public function icon(): string
    {
        return match($this) {
            self::OWNER => 'crown',
            self::ADMINISTRATOR => 'shield',
            self::TESTER => 'flask',
            self::PLAYER => 'user',
        };
    }

    /**
     * Get all roles as an array for selects/dropdowns
     * 
     * @return array<string, string> [value => label]
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }

    /**
     * Get all enum values (useful for migrations)
     * 
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get the default role
     */
    public static function default(): self
    {
        return self::PLAYER;
    }
}
