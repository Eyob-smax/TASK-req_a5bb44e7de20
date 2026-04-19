<?php

use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Payment completion idempotency — no double-posting allowed.

it('completing payment twice with same idempotency key is idempotent', function () {
    $operator = User::factory()->asAdmin()->create();
    $order    = Order::factory()->pendingPayment()->create();
    $attempt  = PaymentAttempt::factory()->for($order)->create(['status' => 'pending']);
    $key      = 'idem-test-' . uniqid();

    // First completion
    $this->actingAs($operator)
        ->postJson("/api/v1/orders/{$order->id}/payment/complete", ['attempt_id' => $attempt->id], [
            'Idempotency-Key' => $key,
        ])
        ->assertOk()
        ->assertJsonPath('data.status', 'paid');

    // Second identical request — must be idempotent (either 200 same result or 409 conflict)
    $response = $this->actingAs($operator)
        ->postJson("/api/v1/orders/{$order->id}/payment/complete", ['attempt_id' => $attempt->id], [
            'Idempotency-Key' => $key,
        ]);

    // Accept either idempotent 200 or explicit 409 conflict
    expect($response->status())->toBeIn([200, 409]);
    if ($response->status() === 409) {
        $response->assertJsonPath('error.code', 'IDEMPOTENCY_KEY_CONFLICT');
    }
});

it('completing payment without idempotency key returns 400', function () {
    $operator = User::factory()->asAdmin()->create();
    $order    = Order::factory()->pendingPayment()->create();
    $attempt  = PaymentAttempt::factory()->for($order)->create(['status' => 'pending']);

    $this->actingAs($operator)
        ->postJson("/api/v1/orders/{$order->id}/payment/complete", ['attempt_id' => $attempt->id])
        ->assertStatus(400)
        ->assertJsonPath('error.code', 'IDEMPOTENCY_KEY_REQUIRED');
});

it('same idempotency key on different order resource does not replay across orders', function () {
    $operator = User::factory()->asAdmin()->create();
    $order1   = Order::factory()->pendingPayment()->create();
    $attempt1 = PaymentAttempt::factory()->for($order1)->create(['status' => 'pending']);
    $order2   = Order::factory()->pendingPayment()->create();
    $attempt2 = PaymentAttempt::factory()->for($order2)->create(['status' => 'pending']);
    $key      = 'cross-resource-' . uniqid();

    // Complete payment for order1 — first use of key
    $this->actingAs($operator)
        ->postJson("/api/v1/orders/{$order1->id}/payment/complete", ['attempt_id' => $attempt1->id], [
            'Idempotency-Key' => $key,
        ])
        ->assertHeader('X-Idempotent-Replay', 'false');

    // Same key but different order resource — different scope, must NOT replay
    $this->actingAs($operator)
        ->postJson("/api/v1/orders/{$order2->id}/payment/complete", ['attempt_id' => $attempt2->id], [
            'Idempotency-Key' => $key,
        ])
        ->assertHeader('X-Idempotent-Replay', 'false');
});

it('cannot complete payment for a canceled order', function () {
    $operator = User::factory()->asAdmin()->create();
    $order    = Order::factory()->canceled()->create();
    $attempt  = PaymentAttempt::factory()->for($order)->create(['status' => 'pending']);

    $this->actingAs($operator)
        ->postJson("/api/v1/orders/{$order->id}/payment/complete", ['attempt_id' => $attempt->id], [
            'Idempotency-Key' => 'idem-cancel-test',
        ])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'INVALID_STATE_TRANSITION');
});
