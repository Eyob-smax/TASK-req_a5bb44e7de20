<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\CatalogItem;
use App\Models\FeeCategory;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('create builds order with correct totals', function () {
    $user     = User::factory()->create();
    $category = FeeCategory::factory()->create(['is_taxable' => false]);
    $item     = CatalogItem::factory()->for($category)->create(['unit_price_cents' => 1000]);

    $service = app(OrderService::class);
    $order   = $service->create($user, [
        ['catalog_item_id' => $item->id, 'quantity' => 2],
    ]);

    expect($order->subtotal_cents)->toBe(2000)
        ->and($order->tax_cents)->toBe(0)
        ->and($order->total_cents)->toBe(2000)
        ->and($order->status)->toBe(OrderStatus::PendingPayment);
});

test('create sets auto_close_at', function () {
    $user     = User::factory()->create();
    $category = FeeCategory::factory()->create(['is_taxable' => false]);
    $item     = CatalogItem::factory()->for($category)->create(['unit_price_cents' => 500]);

    $service = app(OrderService::class);
    $order   = $service->create($user, [
        ['catalog_item_id' => $item->id, 'quantity' => 1],
    ]);

    expect($order->auto_close_at)->not->toBeNull();
});

test('autoClose transitions order to canceled', function () {
    $user     = User::factory()->create();
    $category = FeeCategory::factory()->create(['is_taxable' => false]);
    $item     = CatalogItem::factory()->for($category)->create(['unit_price_cents' => 100]);

    $service = app(OrderService::class);
    $order   = $service->create($user, [
        ['catalog_item_id' => $item->id, 'quantity' => 1],
    ]);

    $service->autoClose($order);

    expect($order->fresh()->status)->toBe(OrderStatus::Canceled);
});

test('timeline returns timeline events', function () {
    $user     = User::factory()->create();
    $category = FeeCategory::factory()->create(['is_taxable' => false]);
    $item     = CatalogItem::factory()->for($category)->create(['unit_price_cents' => 100]);

    $service = app(OrderService::class);
    $order   = $service->create($user, [
        ['catalog_item_id' => $item->id, 'quantity' => 1],
    ]);

    $events = $service->timeline($order);
    expect($events)->not->toBeEmpty();
});
