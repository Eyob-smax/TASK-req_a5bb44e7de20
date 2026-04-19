<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('GET /auth/me returns current user with roles', function () {
    $user  = User::factory()->create(['status' => AccountStatus::Active]);
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withToken($token)->getJson('/api/v1/auth/me');

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['id', 'email', 'roles']])
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email);
});

test('GET /auth/me without token returns 401 UNAUTHENTICATED', function () {
    $response = $this->getJson('/api/v1/auth/me');

    $response->assertStatus(401)
        ->assertJsonPath('error.code', 'UNAUTHENTICATED');
});

test('GET /auth/me with expired token returns 401', function () {
    $user = User::factory()->create(['status' => AccountStatus::Active]);
    $tokenResult = $user->createToken('test', ['*'], now()->subHour());
    $expiredToken = $tokenResult->plainTextToken;

    $response = $this->withToken($expiredToken)->getJson('/api/v1/auth/me');

    $response->assertStatus(401);
});

test('response does not leak password hash or sensitive fields', function () {
    $user  = User::factory()->create(['status' => AccountStatus::Active]);
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withToken($token)->getJson('/api/v1/auth/me');

    $body = $response->json('data');
    expect($body)->not->toHaveKey('password')
        ->not->toHaveKey('remember_token');
});
