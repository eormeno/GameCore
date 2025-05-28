<?php

namespace App\Enums;

enum GameState: int
{
    case WAITING = 0;
    case RUNNING = 1;
    case FINISHED = 2;

    public function label(): string
    {
        return match($this) {
            self::WAITING => 'Waiting',
            self::RUNNING => 'Running',
            self::FINISHED => 'Finished',
        };
    }
}
