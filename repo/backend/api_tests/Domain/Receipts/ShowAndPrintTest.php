<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('GET /orders/{id}/receipt returns receipt for completed order', function () {
    $user  = User::factory()->create(['status' => AccountStatus::Active]);
    $order = Order::factory()->paid()->for($user)->create();

    Receipt::create([
        'order_id'       => $order->id,
        'receipt_number' => 'REC-001',
        'issued_at'      => now(),
    ]);

    $response = $this->actingAs($user)->getJson("/api/v1/orders/{$order->id}/receipt");

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['receipt_number', 'issued_at']]);
});

test('GET /orders/{id}/receipt/print returns printable receipt', function () {
    $user  = User::factory()->create(['status' => AccountStatus::Active]);
    $order = Order::factory()->paid()->for($user)->create();

    Receipt::create([
        'order_id'       => $order->id,
        'receipt_number' => 'REC-002',
        'issued_at'      => now(),
    ]);

    $response = $this->actingAs($user)->getJson("/api/v1/orders/{$order->id}/receipt/print");

    $response->assertStatus(200);
});

test('receipt not found for unpaid order returns 404', function () {
    $user  = User::factory()->create(['status' => AccountStatus::Active]);
    $order = Order::factory()->for($user)->create();

    $this->actingAs($user)->getJson("/api/v1/orders/{$order->id}/receipt")
        ->assertStatus(404);
});

test('other user cannot view receipt for order they do not own', function () {
    $owner = User::factory()->create(['status' => AccountStatus::Active]);
    $other = User::factory()->create(['status' => AccountStatus::Active]);
    $order = Order::factory()->paid()->for($owner)->create();

    Receipt::create([
        'order_id'       => $order->id,
        'receipt_number' => 'REC-403',
        'issued_at'      => now(),
    ]);

    $this->actingAs($other)->getJson("/api/v1/orders/{$order->id}/receipt")
        ->assertStatus(403);
});

test('other user cannot print receipt for order they do not own', function () {
    $owner = User::factory()->create(['status' => AccountStatus::Active]);
    $other = User::factory()->create(['status' => AccountStatus::Active]);
    $order = Order::factory()->paid()->for($owner)->create();

    Receipt::create([
        'order_id'       => $order->id,
        'receipt_number' => 'REC-403-print',
        'issued_at'      => now(),
    ]);

    $this->actingAs($other)->getJson("/api/v1/orders/{$order->id}/receipt/print")
        ->assertStatus(403);
});
