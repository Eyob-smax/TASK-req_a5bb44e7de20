<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// These tests verify that the auth:sanctum middleware correctly gates all
// authenticated routes, so unauthorized access returns the expected error envelope.
// Per-resource scope checks land in Prompt 4+ controller tests.

test('unauthenticated request to authenticated route returns 401', function () {
    $response = $this->getJson('/api/v1/auth/me');

    $response->assertStatus(401)
        ->assertJsonPath('error.code', 'UNAUTHENTICATED');
});

test('unauthenticated request to health/circuit returns 401', function () {
    $response = $this->getJson('/api/v1/health/circuit');

    $response->assertStatus(401)
        ->assertJsonPath('error.code', 'UNAUTHENTICATED');
});

test('unauthenticated request to health/metrics returns 401', function () {
    $response = $this->getJson('/api/v1/health/metrics');

    $response->assertStatus(401)
        ->assertJsonPath('error.code', 'UNAUTHENTICATED');
});

test('authenticated request to public health check passes without token', function () {
    $response = $this->getJson('/api/health');

    // Status 200 or 503 depending on DB state; either way no auth error
    expect($response->status())->toBeIn([200, 503]);
    $response->assertJsonStructure(['status', 'service']);
});

test('valid token gives access to me endpoint', function () {
    $user  = User::factory()->create(['status' => AccountStatus::Active]);
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)->getJson('/api/v1/auth/me')
        ->assertStatus(200)
        ->assertJsonPath('data.id', $user->id);
});
