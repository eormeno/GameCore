<?php

namespace App\GameApps\wwg\prefabs;

use App\Models\Prefab\Prefab;

class WerewolvesRootPrefab extends Prefab
{
    public static function structure(): array
    {
        return [
            'states' => self::states(),
            'initial_view:container' => self::initialView(),
            'create_room_view:container' => self::createRoomView(),
            'join_room_view:container' => self::joinRoomView(),
            'lobby_view:container' => self::lobbyView(),
            'playing_view:container' => self::playingView(),
            'night_view:container' => self::nightView(),
            'day_view:container' => self::dayView(),
            'vote_view:container' => self::voteView(),
            'game_over_view:container' => self::gameOverView(),
        ];
    }
    private static function states(): array
    {
        return [
            'initial' => ['wwg.initial-state' => []],
            'create_room' => ['wwg.create-room-state' => []],
            'join_room' => ['wwg.join-room-state' => []],
            'lobby' => ['wwg.lobby-state' => []],
            'playing' => ['wwg.playing-state' => []],
            'night' => ['wwg.night-state' => []],
            'day' => ['wwg.day-state' => []],
            'vote' => ['wwg.vote-state' => []],
            'game_over' => ['wwg.game-over-state' => []],
        ];
    }
    private static function initialView(): array
    {
        return [
            'active' => false,
            'attributes' => [
                'layout' => 'vertical',
                'image' => 'wwg-background.png',
                'width' => '100%',
                'height' => '100%'
            ],
            'create_game_button:button' => ['attributes' => ['text' => 'Create Game', 'event' => 'create_room', 'style' => 'primary']],
            'joint_game_button:button' => ['attributes' => ['text' => 'Join Game', 'event' => 'join_room', 'style' => 'primary']],
            'initial_sound:sound' => ['attributes' => ['sound' => 'initial-music.wav', 'loop' => true, 'volume' => 0.25]],
        ];
    }

    private static function createRoomView(): array
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
            'create_room_code:button' => ['attributes' => ['text' => 'Create', 'event' => 'create_room_code', 'style' => 'primary']],
            'help:label' => ['active' => false, 'attributes' => ['text' => 'Share this code with other players:', 'style' => 'paragraph']],
            'room_code:label' => ['active' => false, 'attributes' => ['text' => '', 'style' => 'paragraph']],
            'lobby_button:button' => ['active' => false, 'attributes' => ['text' => 'Go to Lobby', 'event' => 'lobby', 'style' => 'primary']],
        ];
    }
    private static function lobbyView(): array
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
            'description:label' => ['attributes' => ['text' => 'Haz clic en Iniciar para comenzar', 'style' => 'paragraph']],
            'start_button:button' => ['attributes' => ['text' => 'Iniciar', 'event' => 'start', 'style' => 'primary']],

        ];
    }
    private static function joinRoomView(): array
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
            'description:label' => ['attributes' => ['text' => 'Insert room code to join', 'style' => 'paragraph']],
            'room_code_input:text-input' => ['attributes' => ['placeholder' => 'Enter Room Code', 'event' => 'input', 'value' => '', 'style' => '']],
            'warning_text:label' => ['active' => false, 'attributes' => ['text' => 'The Room Code has to be 13 characters long', 'style' => 'danger']],
            'join_button:button' => ['attributes' => ['text' => 'Join', 'event' => 'join', 'style' => 'primary']],
        ];
    }


    private static function playingView(): array
    {
        return [
            'active' => false,
            'attributes' => [
                'layout' => 'vertical',
                'image' => 'background.png',
                'width' => '100%',
                'height' => '100%'
            ],
            'title:label' => ['attributes' => ['text' => 'Playing Game', 'style' => 'title']],
            'description:label' => ['attributes' => ['text' => 'Haz clic en Iniciar para comenzar', 'style' => 'paragraph']],
            'start_button:button' => ['attributes' => ['text' => 'Iniciar', 'event' => 'start', 'style' => 'primary']],

        ];
    }
    private static function nightView(): array
    {
        return [
            'active' => false,
            'attributes' => [
                'layout' => 'vertical',
                'image' => 'background.png',
                'width' => '100%',
                'height' => '100%'
            ],
            'title:label' => ['attributes' => ['text' => 'Night', 'style' => 'title']],
            'description:label' => ['attributes' => ['text' => 'Haz clic en Iniciar para comenzar', 'style' => 'paragraph']],
            'start_button:button' => ['attributes' => ['text' => 'Iniciar', 'event' => 'start', 'style' => 'primary']],

        ];
    }
    private static function dayView(): array
    {
        return [
            'active' => false,
            'attributes' => [
                'layout' => 'vertical',
                'image' => 'background.png',
                'width' => '100%',
                'height' => '100%'
            ],
            'title:label' => ['attributes' => ['text' => 'Day', 'style' => 'title']],
            'description:label' => ['attributes' => ['text' => 'Haz clic en Iniciar para comenzar', 'style' => 'paragraph']],
            'start_button:button' => ['attributes' => ['text' => 'Iniciar', 'event' => 'start', 'style' => 'primary']],

        ];
    }
    private static function voteView(): array
    {
        return [
            'active' => false,
            'attributes' => [
                'layout' => 'vertical',
                'image' => 'background.png',
                'width' => '100%',
                'height' => '100%'
            ],
            'title:label' => ['attributes' => ['text' => 'Vote', 'style' => 'title']],
            'description:label' => ['attributes' => ['text' => 'Haz clic en Iniciar para comenzar', 'style' => 'paragraph']],
            'start_button:button' => ['attributes' => ['text' => 'Iniciar', 'event' => 'start', 'style' => 'primary']],

        ];
    }
    private static function gameOverView(): array
    {
        return [
            'active' => false,
            'attributes' => [
                'layout' => 'vertical',
                'image' => 'background.png',
                'width' => '100%',
                'height' => '100%'
            ],
            'title:label' => ['attributes' => ['text' => 'GameOver', 'style' => 'title']],
            //'description:label' => ['attributes' => ['text' => 'Haz clic en Iniciar para comenzar', 'style' => 'paragraph']],
        ];
    }
}

