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
}
