<?php

namespace App\GameApps\wwg\prefabs;

use App\Models\Prefab\Prefab;

class PlayerPrefab extends Prefab
{
    public static function structure(): array
    {
        return [
            'states' => self::states(),
            'initial_view:container' => self::initialView(),
            'game_over_view:container' => self::gameOverView(),
            'join_room_view:container' => self::joinRoomView(),
            'player_lobby_view:container' => self::lobbyView(),
            'player_role_hint_view:container' => [],
            'day_view:container' => self::dayView(),
            'vote_view:container' => self::voteView(),
            'spectator_view:container' => self::spectatorView(),
        ];
    }

    private static function states(): array
    {
        return [
            'initial' => ['wwg.initial-state' => []],
            'join_room' => ['wwg.join-room-state' => []],
            'lobby' => ['wwg.lobby-state' => []],
            'playing' => ['wwg.playing-state' => []],
            'night' => ['wwg.night-state' => []],
            'day' => ['wwg.day-state' => []],
            'vote' => ['wwg.vote-state' => []],
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
    public static function joinRoomView(): array
    {
        return [
            'active' => false,
            'attributes' => [
                'layout' => 'vertical',
                'image' => 'background.png',
                'width' => '100%',
                'height' => '100%'
            ],
            'title:label' => ['attributes' => ['text' => 'Join Room', 'style' => 'title']],
            // Additional UI elements for joining a room can be added here
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
            // Additional UI elements for the lobby can be added here
        ];
    }
    public static function dayView(): array
    {
        return [
            'active' => false,
            'attributes' => [
                'layout' => 'vertical',
                'image' => 'day_background.png',
                'width' => '100%',
                'height' => '100%'
            ],
            'title:label' => ['attributes' => ['text' => 'Day Phase', 'style' => 'title']],
            // Additional UI elements for the day phase can be added here
        ];
    }
    public static function voteView(): array
    {
        return [
            'active' => false,
            'attributes' => [
                'layout' => 'vertical',
                'image' => 'vote_background.png',
                'width' => '100%',
                'height' => '100%'
            ],
            'title:label' => ['attributes' => ['text' => 'Vote Phase', 'style' => 'title']],
            // Additional UI elements for the vote phase can be added here
        ];
    }
    public static function spectatorView(): array
    {
        return [
            'active' => false,
            'attributes' => [
                'layout' => 'vertical',
                'image' => 'spectator_background.png',
                'width' => '100%',
                'height' => '100%'
            ],
            'title:label' => ['attributes' => ['text' => 'Spectator View', 'style' => 'title']],
            // Additional UI elements for spectators can be added here
        ];
    }
    public static function gameOverView(): array
    {
        return [
            'active' => false,
            'attributes' => [
                'layout' => 'vertical',
                'image' => 'game_over_background.png',
                'width' => '100%',
                'height' => '100%'
            ],
            'title:label' => ['attributes' => ['text' => 'Game Over', 'style' => 'title']],
            // Additional UI elements for the game over screen can be added here
        ];
    }
}