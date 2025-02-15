<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('Root route returns Ok status', function () {
    $response = $this->get('/');

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
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertOk()->assertJsonStructure(['token']);
});

test('usuario autenticado puede acceder a ruta protegida', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->getJson('/api/user', [
        'Authorization' => "Bearer $token",
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('email', $user->email);
});

test('usuario no autenticado no puede acceder a ruta protegida', function () {
    $this->getJson('/api/user')
        ->assertUnauthorized();
});
