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
}
