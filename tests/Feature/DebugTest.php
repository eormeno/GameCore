<?php

test("1. Debugger returns all installed Game Apps", function () {
    reloadGameApps();
    $response = $this->get("/api/debug/game-apps?active=true");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        '*' => [
            'prefix',
            'name',
            'active',
        ]
    ]);
    // write($response);
});

test("2. Debugger returns detailed info for a specific Game App", function () {
    $gameAppPrefix = 'bba';

    reloadGameApps();
    $gameApp = findGameApp($gameAppPrefix);

    loginTestUser(0);

    userWantsToPlayTheGameApplication($gameApp);
    $game = findUserGameInstance($gameApp);

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
    // write($response);
});
