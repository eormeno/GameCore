<?php

namespace App\GameApps\bba\prefabs;

use App\Models\Prefab\Prefab;

class Ball extends Prefab
{
    public static function structure(): array
    {
        return [
            'components' => [
                'transform-2d' => [
                    'x' => 11,
                    'y' => 112,
                ],
                'sprite-renderer' => [
                    'texture' => 'soccer_ball.png',
                    'layer' => 1,
                ]
            ],
        ];
    }
}
