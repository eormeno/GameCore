<?php

namespace App\Contracts;

use App\Events\GameEvent;

interface IGameEventListener
{
    public function handle(GameEvent $event): void;
}
