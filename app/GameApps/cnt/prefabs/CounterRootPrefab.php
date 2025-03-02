<?php

namespace App\GameApps\cnt\prefabs;

use App\Models\Prefab\Prefab;

class CounterRootPrefab extends Prefab
{

    public static function structure(): array
    {
        return [
            'main:container' => [
                'attributes' => [
                    'layout' => 'horizontal',
                    'image' => 'counter_background.png',
                    'width' => '100%',
                    'height' => '100%'
                ],
                'dec_button:button' => ['attributes' => ['text' => '-', 'event' => 'decrement', 'style' => 'primary']],
                'number:label' => ['attributes' => ['text' => '0', 'style' => 'label-center']],
                'inc_button:button' => ['attributes' => ['text' => '+', 'event' => 'increment', 'style' => 'primary']],
            ]
        ];
    }
}
