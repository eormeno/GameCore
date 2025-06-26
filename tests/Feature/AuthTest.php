<?php

use App\Models\User;

test('Root route returns Ok status', function () {
    $response = $this->get('/api');

    $response->assertOk()->assertJson([
        'status' => 1
    ]);
});

test('usuario puede registrarse', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(201)->assertJsonPath('user.email', 'test@example.com');

    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});

test('usuario puede hacer login y obtener token', function () {
    User::factory()->adminUser()->create();
    $response = $this->postJson('/api/login', adminUserCredentials());
    $response->assertOk()->assertJsonStructure([
        'successful_login' => [
            'token'
        ]
    ]);
});

test('usuario autenticado puede acceder a ruta protegida', function () {
    $user = loginUser();
    $response = $this->getJson('/api/user');
    $response
        ->assertOk()
        ->assertJsonPath('user.email', $user->email);
});

test('usuario no autenticado no puede acceder a ruta protegida', function () {
    $this->getJson('/api/user')
        ->assertUnauthorized();
});
