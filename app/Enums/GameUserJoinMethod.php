<?php

namespace App\Enums;

enum GameUserJoinMethod: string
{
	case REQUEST = "request";
	case INVITATION = "invitation";
	case AUTO = "auto";

	/**
	 * Get the translated label for this join method
	 */
	public function label(): string
	{
		return t("enum.game_user_join_method.{$this->value}");
	}

	/**
	 * Get the translated description for this join method
	 */
	public function description(): string
	{
		return t("enum.game_user_join_method.{$this->value}_description");
	}

	/**
	 * Get a color/badge variant for UI display
	 */
	public function color(): string
	{
		return match($this) {
			self::REQUEST => 'info',
			self::INVITATION => 'warning',
			self::AUTO => 'success',
		};
	}

	/**
	 * Get an icon name for UI display
	 */
	public function icon(): string
	{
		return match($this) {
			self::REQUEST => 'hand',
			self::INVITATION => 'mail',
			self::AUTO => 'zap',
		};
	}

	/**
	 * Get all join methods as an array for selects/dropdowns
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
	 * Get the default join method
	 */
	public static function default(): self
	{
		return self::REQUEST;
	}
}
