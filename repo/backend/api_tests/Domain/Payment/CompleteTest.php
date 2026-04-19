<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Enums\OrderStatus;
use App\Models\CatalogItem;
use App\Models\FeeCategory;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('completing payment transitions order to paid and creates receipt', function () {
    $user     = User::factory()->create(['status' => AccountStatus::Active]);
    $category = FeeCategory::factory()->create(['is_taxable' => false]);
    $item     = CatalogItem::factory()->for($category)->create(['unit_price_cents' => 1000, 'is_active' => true]);

    $orderService   = app(OrderService::class);
    $paymentService = app(PaymentService::class);

    $order   = $orderService->create($user, [['catalog_item_id' => $item->id, 'quantity' => 1]]);
    $attempt = $paymentService->initiate($order, 'cash', $user);
    $order   = $paymentService->complete($order, $attempt, $user);

    expect($order->status)->toBe(OrderStatus::Paid);
    $this->assertDatabaseHas('receipts', ['order_id' => $order->id]);
    $this->assertDatabaseHas('ledger_entries', ['order_id' => $order->id, 'entry_type' => 'payment']);
    $this->assertDatabaseHas('audit_log_entries', ['action' => 'order.paid']);
});

test('completing payment on paid order throws RuntimeException', function () {
    $user     = User::factory()->create(['status' => AccountStatus::Active]);
    $category = FeeCategory::factory()->create(['is_taxable' => false]);
    $item     = CatalogItem::factory()->for($category)->create(['unit_price_cents' => 500, 'is_active' => true]);

    $orderService   = app(OrderService::class);
    $paymentService = app(PaymentService::class);

    $order   = $orderService->create($user, [['catalog_item_id' => $item->id, 'quantity' => 1]]);
    $attempt = $paymentService->initiate($order, 'cash', $user);
    $paymentService->complete($order, $attempt, $user);

    expect(fn () => $paymentService->complete($order->fresh(), $attempt->fresh(), $user))
        ->toThrow(RuntimeException::class);
});
