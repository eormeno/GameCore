<?php

namespace App\Enums;

enum GameUserJoinMethod: string
{
	case REQUEST = "request";
	case INVITATION = "invitation";
	case AUTO = "auto";
}
