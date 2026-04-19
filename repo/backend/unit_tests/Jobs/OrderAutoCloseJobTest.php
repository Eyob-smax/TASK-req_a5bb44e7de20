<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Jobs\OrderAutoCloseJob;
use App\Models\CatalogItem;
use App\Models\FeeCategory;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('closes overdue orders', function () {
    $user     = User::factory()->create();
    $category = FeeCategory::factory()->create(['is_taxable' => false]);
    $item     = CatalogItem::factory()->for($category)->create(['unit_price_cents' => 100]);

    $service = app(OrderService::class);
    $order   = $service->create($user, [['catalog_item_id' => $item->id, 'quantity' => 1]]);

    // Force auto_close_at into the past
    $order->update(['auto_close_at' => now()->subMinutes(35)]);

    OrderAutoCloseJob::dispatchSync();

    expect($order->fresh()->status)->toBe(OrderStatus::Canceled);
});

test('does not close orders not yet past auto_close_at', function () {
    $user     = User::factory()->create();
    $category = FeeCategory::factory()->create(['is_taxable' => false]);
    $item     = CatalogItem::factory()->for($category)->create(['unit_price_cents' => 100]);

    $service = app(OrderService::class);
    $order   = $service->create($user, [['catalog_item_id' => $item->id, 'quantity' => 1]]);

    // auto_close_at is in the future by default
    OrderAutoCloseJob::dispatchSync();

    expect($order->fresh()->status)->toBe(OrderStatus::PendingPayment);
});
