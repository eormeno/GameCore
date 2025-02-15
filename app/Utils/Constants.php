<?php

namespace App\Utils;

class Constants
{
    public const EMPTY_EVENT = [
        'event' => null,
        'is_signal' => false,
        'source' => null,
        'data' => null,
        'destination' => null,
        'rendered' => [],
    ];
}
