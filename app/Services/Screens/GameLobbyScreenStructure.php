<?php

namespace App\Services\Screens;

/**
 * Structure definitions for GameLobbyScreenService responses
 * 
 * This class defines the expected structure of responses from GameLobbyScreenService
 * for validation, testing, and documentation purposes.
 */
class GameLobbyScreenStructure
{
    /**
     * Get the expected structure for game lobby screen response
     * 
     * @return array
     */
    public static function response(): array
    {
        return [
            'game_lobby_screen' => [
                'game_app' => self::gameApp(),
                'saved_games' => [
                    '*' => self::savedGame()
                ],
                'permissions' => self::permissions()
            ]
        ];
    }

    /**
     * Get the expected structure for game app data
     * 
     * @return array
     */
    public static function gameApp(): array
    {
        return [
            'id',
            'name',
            'prefix',
            'width',
            'height',
            'description',
            'card_image',
            'prefab_name',
            'max_instances_per_user',
            'min_users_per_instance',
            'max_users_per_instance',
            'allow_late_join',
            'resourcesUrl'
        ];
    }

    /**
     * Get the expected structure for saved game data
     * 
     * @return array
     */
    public static function savedGame(): array
    {
        return [
            'id',
            'name',
            'invitation_code',
            'auto_authorize_players',
            'state',
            'events_url',
            'game_user' => [
                'role',
                'status',
                'join_method',
                'joined_at',
                'last_played_at',
                'last_played_at_human',
                'created_at'
            ]
        ];
    }

    /**
     * Get the expected structure for user permissions
     * 
     * @return array
     */
    public static function permissions(): array
    {
        return [
            'can_create_new_game'
        ];
    }
}