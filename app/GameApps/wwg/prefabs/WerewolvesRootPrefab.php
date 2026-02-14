<?php

namespace App\GameApps\wwg\prefabs;

use App\Models\Prefab\Prefab;

class WerewolvesRootPrefab extends Prefab
{

    public static function structure(): array
    {
        return [
            'main:container' => [
                'attributes' => ['layout' => 'horizontal', 'width' => '100%', 'height' => '100%'],
                'title:label' => ['attributes' => ['text' => 'Werewolves Game', 'style' => 'label-center']],
                'start:button' => ['attributes' => ['text' => 'Start', 'event' => 'starting', 'style' => 'primary']],
            ],
            'components' => [
                'wwg.hello-world' => ['text' => "Hello World!"],
            ],
        ];
    }
}
