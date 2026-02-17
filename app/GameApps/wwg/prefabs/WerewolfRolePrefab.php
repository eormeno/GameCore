<?php
namespace App\GameApps\wwg\prefabs;
use App\Models\Prefab\Prefab;

class WerewolfRolePrefab extends Prefab
{
    public static function structure(): array
    {
        return [
            'werewolf_vote_view:container' => self::werewolfVoteView(),
        ];
    }
    public static function werewolfVoteView(): array
    {
        return ['active' => false, 'attributes' => ['text' => 'You are a Werewolf. Choose a victim to eliminate!']];
    }

}