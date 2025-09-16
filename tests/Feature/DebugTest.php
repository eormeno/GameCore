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
    Artisan::call('games'); // Call the games command to load game apps
    $gameAppPrefix = 'cnt'; // Example prefix, change as needed
    
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
            'total_instances',
            'active_instances',
            'total_users',
            'active_users',
            'instances' => [
                '*' => [
                    'id',
                    'name',
                    'state',
                    'invitationCode',
                    'createdAt',
                    'users' => [
                        '*' => [
                            'id',
                            'name',
                            'is_owner',
                            'access_approved',
                            'invitation_approved'
                        ]
                    ]
                ]
            ]
        ]
    ]);
    write($response);
});
