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
            'open_games' => [
                '*' => [
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
                ]
            ]
        ]
    ];
}

/**
 * Assert game app play response structure
 * 
 * @param \Illuminate\Testing\TestResponse $response
 * @return void
 */
function assertGameAppPlayResponseStructure($response): void
{
    $response->assertJsonStructure(getGameAppPlayResponseStructure());
}
