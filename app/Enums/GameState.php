<?php

namespace App\Enums;

enum GameState: string
{
    case WAITING = "waiting";
    case RUNNING = "running";
    case FINISHED = "finished";
    case CANCELLED = "cancelled";
    case PAUSED = "paused";

    /**
     * Get the translated label for this game state
     */
    public function label(): string
    {
        return t("enum.game_state.{$this->value}");
    }

    /**
     * Get the translated description for this game state
     */
    public function description(): string
    {
        return t("enum.game_state.{$this->value}_description");
    }

    /**
     * Get a color/badge variant for UI display
     */
    public function color(): string
    {
        return match($this) {
            self::WAITING => 'warning',
            self::RUNNING => 'success',
            self::FINISHED => 'info',
            self::CANCELLED => 'danger',
            self::PAUSED => 'secondary',
        };
    }

    /**
     * Get an icon name for UI display
     */
    public function icon(): string
    {
        return match($this) {
            self::WAITING => 'clock',
            self::RUNNING => 'play-circle',
            self::FINISHED => 'check-circle',
            self::CANCELLED => 'x-circle',
            self::PAUSED => 'pause-circle',
        };
    }

    /**
     * Get all states as an array for selects/dropdowns
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
     * Get the default state
     */
    public static function default(): self
    {
        return self::WAITING;
    }
}
