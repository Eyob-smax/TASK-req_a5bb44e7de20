<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Enums\OrderStatus;
use App\Models\CatalogItem;
use App\Models\FeeCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can create an order', function () {
    $user     = User::factory()->create(['status' => AccountStatus::Active]);
    $category = FeeCategory::factory()->create(['is_taxable' => false]);
    $item     = CatalogItem::factory()->for($category)->create(['unit_price_cents' => 2500, 'is_active' => true]);

    $response = $this->actingAs($user)->postJson('/api/v1/orders', [
        'lines' => [
            ['catalog_item_id' => $item->id, 'quantity' => 2],
        ],
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.status', OrderStatus::PendingPayment->value)
        ->assertJsonPath('data.subtotal_cents', 5000);
});

test('order requires at least one line', function () {
    $user = User::factory()->create(['status' => AccountStatus::Active]);

    $this->actingAs($user)->postJson('/api/v1/orders', ['lines' => []])
        ->assertStatus(422);
});

test('order auto_close_at is set', function () {
    $user     = User::factory()->create(['status' => AccountStatus::Active]);
    $category = FeeCategory::factory()->create(['is_taxable' => false]);
    $item     = CatalogItem::factory()->for($category)->create(['unit_price_cents' => 100, 'is_active' => true]);

    $response = $this->actingAs($user)->postJson('/api/v1/orders', [
        'lines' => [['catalog_item_id' => $item->id, 'quantity' => 1]],
    ]);

    $response->assertStatus(201);
    expect($response->json('data.auto_close_at'))->not->toBeNull();
});
