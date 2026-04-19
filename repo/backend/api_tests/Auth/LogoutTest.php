<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can logout', function () {
    $user  = User::factory()->create(['status' => AccountStatus::Active]);
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withToken($token)->postJson('/api/v1/auth/logout');

    $response->assertStatus(200)
        ->assertJsonPath('data.message', 'Logged out successfully.');
});

test('logout revokes the token so it cannot be reused', function () {
    $user  = User::factory()->create(['status' => AccountStatus::Active]);
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)->postJson('/api/v1/auth/logout')->assertStatus(200);

    // Second request with the same token should return 401
    $this->withToken($token)->postJson('/api/v1/auth/logout')
        ->assertStatus(401);
});

test('unauthenticated logout returns 401', function () {
    $response = $this->postJson('/api/v1/auth/logout');

    $response->assertStatus(401)
        ->assertJsonPath('error.code', 'UNAUTHENTICATED');
});
