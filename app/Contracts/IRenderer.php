<?php

namespace App\Contracts;

use App\Models\Game;

interface IRenderer
{
    public function render(Game $game, array $eventInfo): array;
}
