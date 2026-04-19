<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('unknown endpoint returns NOT_FOUND envelope', function () {
    $response = $this->getJson('/api/v1/does-not-exist');

    $response->assertStatus(404)
        ->assertJsonStructure(['error' => ['code', 'message']])
        ->assertJsonPath('error.code', 'NOT_FOUND');
});

test('wrong method on /api/health returns METHOD_NOT_ALLOWED envelope', function () {
    $response = $this->postJson('/api/health');

    $response->assertStatus(405)
        ->assertJsonPath('error.code', 'METHOD_NOT_ALLOWED');
});

test('unauthenticated /api/v1/health/circuit returns UNAUTHENTICATED envelope', function () {
    $response = $this->getJson('/api/v1/health/circuit');

    $response->assertStatus(401)
        ->assertJsonPath('error.code', 'UNAUTHENTICATED');
});
