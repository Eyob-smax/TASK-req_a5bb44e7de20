<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('POST /api/v1/_contract/echo without Idempotency-Key returns 400 envelope', function () {
    $response = $this->postJson('/api/v1/_contract/echo', ['hello' => 'world']);

    $response->assertStatus(400)
        ->assertJsonPath('error.code', 'IDEMPOTENCY_KEY_REQUIRED');
});

test('POST /api/v1/_contract/echo with Idempotency-Key returns 200 envelope', function () {
    $response = $this->postJson(
        '/api/v1/_contract/echo',
        ['hello' => 'world'],
        ['Idempotency-Key' => 'first-request-001'],
    );

    $response->assertStatus(200)
        ->assertJsonPath('data.echoed.hello', 'world');
});
