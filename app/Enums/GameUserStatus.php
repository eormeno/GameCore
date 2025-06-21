<?php

namespace App\Enums;

enum GameUserStatus: string
{
	case PENDING_OWNER_APPROVAL = "pending_owner_approval";
	case PENDING_PLAYER_ACCEPTANCE = "pending_player_acceptance";
	case ACTIVE = "active";
	case LEFT = "left";
	case KICKED = "kicked";
	case BANNED = "banned";

	/**
	 * Get the default status for new game-user relationships
	 */
	public static function default(): self
	{
		return self::PENDING_OWNER_APPROVAL;
	}
}
