<?php

namespace App\Enums;

enum GameUserRole: string
{
    case OWNER = "owner";
    case ADMINISTRATOR = "administrator";
    case TESTER = "tester";
    case PLAYER = "player";
}
