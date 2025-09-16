<?php

use Illuminate\Support\Facades\Artisan;

test("1. Debugger returns all installed Game Apps", function () {
    Artisan::call('games'); // Call the games command to load game apps
    
    $response = $this->get("/api/debug/game-apps?active=true");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        '*' => [
            'prefix',
            'name',
            'active',
        ]
    ]);
    write($response);
});

test("2. Debugger returns detailed info for a specific Game App", function () {
    // Create test data using the helper function
    $game = getUserPlayingGame('bba'); // This creates user, gameapp, and game instance
    
    $gameAppPrefix = 'bba';
    
    $response = $this->get("/api/debug/game-apps/{$gameAppPrefix}");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'prefix',
        'name',
        'description',
        'min_age',
        'card_image',
        'prefab_name',
        'prefab_attributes',
        'client',
        'width',
        'height',
        'version',
        'max_instances_per_user',
        'min_users_per_instance',
        'max_users_per_instance',
        'allow_late_join',
        'active',
        'service_registry',
        'detailed_info' => [
            '*' => [
                'id',
                'name',
                'state',
                'invitation_code',
                'created_at',
                'updated_at',
                'users' => [
                    '*' => [
                        'id',
                        'name',
                        'role',
                        'status',
                        'joined_at'
                    ]
                ]
            ]
        ]
    ]);
    write($response);
});
