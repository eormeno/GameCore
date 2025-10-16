<?php

namespace App\Enums;

enum GameUserStatus: string
{
	case REQUESTED = "requested";
	case INVITED = "invited";
	case ACTIVE = "active";
	case LEFT = "left";
	case KICKED = "kicked";
	case BANNED = "banned";

	/**
	 * Get the translated label for this status
	 */
	public function label(): string
	{
		return t("enum.game_user_status.{$this->value}");
	}

	/**
	 * Get the translated description for this status
	 */
	public function description(): string
	{
		return t("enum.game_user_status.{$this->value}_description");
	}

	/**
	 * Get a color/badge variant for UI display
	 */
	public function color(): string
	{
		return match($this) {
			self::REQUESTED => 'info',
			self::INVITED => 'warning',
			self::ACTIVE => 'success',
			self::LEFT => 'secondary',
			self::KICKED => 'danger',
			self::BANNED => 'dark',
		};
	}

	/**
	 * Get an icon name for UI display
	 */
	public function icon(): string
	{
		return match($this) {
			self::REQUESTED => 'help-circle',
			self::INVITED => 'mail',
			self::ACTIVE => 'check-circle',
			self::LEFT => 'log-out',
			self::KICKED => 'user-x',
			self::BANNED => 'shield-off',
		};
	}

	/**
	 * Get all statuses as an array for selects/dropdowns
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
	 * Get the default status
	 */
	public static function default(): self
	{
		return self::REQUESTED;
	}
}
