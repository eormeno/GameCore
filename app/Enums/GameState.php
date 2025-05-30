<?php

namespace App\Enums;

enum GameState: int
{
    case WAITING = 0;
    case RUNNING = 1;
    case FINISHED = 2;
    case CANCELLED = 3; // opcional

    public function label(): string
    {
        return match($this) {
            self::WAITING => 'Waiting',
            self::RUNNING => 'Running',
            self::FINISHED => 'Finished',
            self::CANCELLED => 'Cancelled',
        };
    }
}
