<?php

namespace App\Enums;

enum GameState: string
{
    case WAITING = "waiting";
    case RUNNING = "running";
    case FINISHED = "finished";
    case CANCELLED = "cancelled";
    case PAUSED = "paused";
}
