<?php

namespace App\GameApps\wwg\prefabs;

use App\Models\Prefab\Prefab;

class ModeratorPrefab extends Prefab
{
    public static function structure(): array
    {

        return [
            'states' => self::states(),
            'initial_view:container' => self::initialView(),
            'create_room_view:container' => self::createRoomView(),
            'moderator_lobby_view:container' => self::lobbyView(),
            'moderator_playing_view:container' => self::playingView(),
            'game_over_view:container' => self::gameOverView(),
        ];
    }
    private static function states(): array
    {
        return [
            'initial' => ['wwg.initial-state' => []],
            'create_room' => ['wwg.create-room-state' => []],
            'lobby' => ['wwg.lobby-state' => []],
            'playing' => ['wwg.playing-state' => []],
            'game_over' => ['wwg.game-over-state' => []],
        ];
    }
    public static function initialView(): array
    {
        return [
            'active' => true,
            'attributes' => [
                'layout' => 'vertical',
                'image' => 'wwg-background.png',
                'width' => '100%',
                'height' => '100%'
            ],
            'create_game_button:button' => ['attributes' => ['text' => 'Create Game', 'event' => 'create_room', 'style' => 'primary']],
            'joint_game_button:button' => ['attributes' => ['text' => 'Join Game', 'event' => 'join_room', 'style' => 'primary']],
            'exit_button:button' => ['attributes' => ['text' => 'Exit', 'event' => 'exit', 'style' => 'danger']],
        ];
    }
    public static function createRoomView(): array
    {
        return [
            'active' => false,
            'attributes' => [
                'layout' => 'vertical',
                'image' => 'background.png',
                'width' => '100%',
                'height' => '100%'
            ],
            'title:label' => ['attributes' => ['text' => 'Create Room', 'style' => 'title']],
        ];
    }
    public static function lobbyView(): array
    {
        return [
            'active' => false,
            'attributes' => [
                'layout' => 'vertical',
                'image' => 'background.png',
                'width' => '100%',
                'height' => '100%'
            ],
            'title:label' => ['attributes' => ['text' => 'Lobby', 'style' => 'title']],
            'start_game_button:button' => ['attributes' => ['text' => 'Start Game', 'event' => 'start_game', 'style' => 'primary']],
        ];
    }
    public static function playingView(): array
    {
        return [
            'active' => false,
            'attributes' => [
                'layout' => 'vertical',
                'image' => 'background.png',
                'width' => '100%',
                'height' => '100%'
            ],
            'title:label' => ['attributes' => ['text' => 'Game in Progress', 'style' => 'title']],
        ];
    }
    public static function gameOverView(): array
    {
        return [
            'active' => false,
            'attributes' => [
                'layout' => 'vertical',
                'image' => 'background.png',
                'width' => '100%',
                'height' => '100%'
            ],
            'title:label' => ['attributes' => ['text' => 'Game Over', 'style' => 'title']],
        ];
    }


}