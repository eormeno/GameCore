<?php

namespace App\GameApps\bba\prefabs;

use App\Models\Prefab\Prefab;

class Background extends Prefab
{
    public static function structure(): array
    {
        return [
            'components' => [
                'sprite-renderer' => [
                    'texture' => 'background.jpeg',
                    'layer' => 0,
                ],
            ],
        ];
    }
}
