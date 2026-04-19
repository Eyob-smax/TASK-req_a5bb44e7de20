<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('replayed Idempotency-Key returns replay header and original body', function () {
    $key = 'replay-key-' . uniqid();
    $payload = ['k' => 'v'];

    $first = $this->postJson('/api/v1/_contract/echo', $payload, ['Idempotency-Key' => $key]);
    $first->assertStatus(200);
    $firstBody = $first->json();

    $second = $this->postJson('/api/v1/_contract/echo', $payload, ['Idempotency-Key' => $key]);
    $second->assertStatus(200);
    $second->assertHeader('X-Idempotent-Replay', 'true');
    expect($second->json())->toEqual($firstBody);
});

test('differing payload on reused Idempotency-Key returns 409 conflict envelope', function () {
    $key = 'conflict-key-' . uniqid();

    $this->postJson('/api/v1/_contract/echo', ['k' => 'one'], ['Idempotency-Key' => $key])
        ->assertStatus(200);

    $conflict = $this->postJson('/api/v1/_contract/echo', ['k' => 'two'], ['Idempotency-Key' => $key]);
    $conflict->assertStatus(409)
        ->assertJsonPath('error.code', 'IDEMPOTENCY_KEY_CONFLICT');
});
