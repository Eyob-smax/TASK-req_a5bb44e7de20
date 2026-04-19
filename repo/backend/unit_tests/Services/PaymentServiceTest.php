<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\CatalogItem;
use App\Models\FeeCategory;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();
});

function makeOrderWithItem(User $user): \App\Models\Order
{
    $category = FeeCategory::factory()->create(['is_taxable' => false]);
    $item     = CatalogItem::factory()->for($category)->create(['unit_price_cents' => 2000, 'is_active' => true]);

    return app(OrderService::class)->create($user, [['catalog_item_id' => $item->id, 'quantity' => 1]]);
}

test('initiate creates PaymentAttempt with status pending', function () {
    $user    = User::factory()->create();
    $order   = makeOrderWithItem($user);
    $service = app(PaymentService::class);

    $attempt = $service->initiate($order, 'cash', $user);

    expect($attempt->status)->toBe(PaymentStatus::Pending)
        ->and($attempt->order_id)->toBe($order->id);

    $this->assertDatabaseHas('payment_attempts', ['id' => $attempt->id, 'status' => 'pending']);
});

test('complete transitions order to Paid and creates Receipt and writes ledger entry', function () {
    $user    = User::factory()->create();
    $order   = makeOrderWithItem($user);
    $service = app(PaymentService::class);

    $attempt  = $service->initiate($order, 'cash', $user);
    $paidOrder = $service->complete($order, $attempt, $user);

    expect($paidOrder->status)->toBe(OrderStatus::Paid);

    $this->assertDatabaseHas('receipts', ['order_id' => $order->id]);
    $this->assertDatabaseHas('ledger_entries', ['order_id' => $order->id, 'entry_type' => 'payment']);
});

test('complete on already-paid order throws RuntimeException', function () {
    $user    = User::factory()->create();
    $order   = makeOrderWithItem($user);
    $service = app(PaymentService::class);

    $attempt = $service->initiate($order, 'cash', $user);
    $service->complete($order, $attempt, $user);

    expect(fn () => $service->complete($order->fresh(), $attempt->fresh(), $user))
        ->toThrow(RuntimeException::class);
});

test('complete writes audit_log_entries row with action=order.paid', function () {
    $user    = User::factory()->create();
    $order   = makeOrderWithItem($user);
    $service = app(PaymentService::class);

    $attempt = $service->initiate($order, 'cash', $user);
    $service->complete($order, $attempt, $user);

    $this->assertDatabaseHas('audit_log_entries', [
        'action'    => 'order.paid',
        'target_id' => $order->id,
    ]);
});
