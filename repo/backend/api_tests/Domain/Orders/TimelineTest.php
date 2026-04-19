<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Enums\OrderStatus;
use App\Models\CatalogItem;
use App\Models\FeeCategory;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('owner can retrieve order timeline events', function () {
    $user     = User::factory()->create(['status' => AccountStatus::Active]);
    $category = FeeCategory::factory()->create(['is_taxable' => false]);
    $item     = CatalogItem::factory()->for($category)->create(['unit_price_cents' => 1500, 'is_active' => true]);

    $order = app(OrderService::class)->create($user, [['catalog_item_id' => $item->id, 'quantity' => 1]]);

    $response = $this->actingAs($user)->getJson("/api/v1/orders/{$order->id}/timeline");

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => [['event', 'created_at']]]);
});

test('another user cannot retrieve a different user order timeline', function () {
    $owner    = User::factory()->create(['status' => AccountStatus::Active]);
    $other    = User::factory()->create(['status' => AccountStatus::Active]);
    $category = FeeCategory::factory()->create(['is_taxable' => false]);
    $item     = CatalogItem::factory()->for($category)->create(['unit_price_cents' => 500, 'is_active' => true]);

    $order = app(OrderService::class)->create($owner, [['catalog_item_id' => $item->id, 'quantity' => 1]]);

    $this->actingAs($other)->getJson("/api/v1/orders/{$order->id}/timeline")
        ->assertStatus(403);
});
