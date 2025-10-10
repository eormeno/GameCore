<?php

/**
 * API Response Structure Definitions for Tests
 * 
 * This file contains reusable JSON structure definitions for API responses
 * to maintain consistency across tests and avoid duplication.
 */

/**
 * Get the JSON structure for the game app play endpoint response
 * 
 * @return array
 */
function getGameAppPlayResponseStructure(): array
{
    return [
        'first_screen' => [
            'game_app' => [
                'name',
                'width',
                'height',
                'max_instances_per_user',
                'min_users_per_instance',
                'max_users_per_instance',
                'allow_late_join',
                'resourcesUrl'
            ],
            'default_game' => [
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
                    'created_at'
                ]
            ]
        ]
    ];
}

/**
 * Get the JSON structure for open games (optional part of response)
 * 
 * @return array
 */
function getOpenGamesStructure(): array
{
    return [
        'first_screen' => [
            'open_games' => [
                '*' => [
                    'id',
                    'name',
                    'invitationCode',
                    'state',
                    'users',
                    'createdAt'
                ]
            ]
        ]
    ];
}

/**
 * Assert game app play response structure with optional open_games validation
 * 
 * @param \Illuminate\Testing\TestResponse $response
 * @return void
 */
function assertGameAppPlayResponseStructure($response, bool $openGamesIsMandatory = false): void
{
    // Assert main structure
    $response->assertJsonStructure(getGameAppPlayResponseStructure());

    if ($openGamesIsMandatory) {
        $response->assertJsonStructure(getOpenGamesStructure());
    }
}
