<?php
namespace App\GameApps\Common\prefabs;

use App\Models\Prefab\Prefab;
class TextInput extends Prefab
{
    public static function structure(): array
    {
        return [
            'components' => ['text_input' => []]
        ];
    }
}