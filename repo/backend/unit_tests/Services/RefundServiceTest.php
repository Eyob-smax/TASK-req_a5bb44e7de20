<?php

declare(strict_types=1);

use App\Enums\BillStatus;
use App\Enums\RefundStatus;
use App\Models\Bill;
use App\Models\RefundReasonCode;
use App\Models\User;
use App\Services\RefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('request creates pending refund', function () {
    $user   = User::factory()->create();
    $bill   = Bill::factory()->for($user)->create([
        'status'         => BillStatus::Partial,
        'total_cents'    => 10000,
        'paid_cents'     => 10000,
        'refunded_cents' => 0,
    ]);

    RefundReasonCode::create(['code' => 'duplicate', 'label' => 'Duplicate']);

    $service = app(RefundService::class);
    $refund  = $service->request($bill, 5000, 'duplicate', $user);

    expect($refund->status)->toBe(RefundStatus::Pending)
        ->and($refund->amount_cents)->toBe(5000);
});

test('request rejects amount exceeding paid balance', function () {
    $user = User::factory()->create();
    $bill = Bill::factory()->for($user)->create([
        'status'         => BillStatus::Open,
        'total_cents'    => 10000,
        'paid_cents'     => 3000,
        'refunded_cents' => 0,
    ]);

    RefundReasonCode::create(['code' => 'duplicate', 'label' => 'Duplicate']);

    $service = app(RefundService::class);

    expect(fn () => $service->request($bill, 5000, 'duplicate', $user))
        ->toThrow(RuntimeException::class);
});

test('approve transitions refund to completed and posts ledger entries', function () {
    $user = User::factory()->create();
    $bill = Bill::factory()->for($user)->create([
        'total_cents'    => 10000,
        'paid_cents'     => 10000,
        'refunded_cents' => 0,
        'status'         => BillStatus::Paid,
    ]);

    RefundReasonCode::create(['code' => 'waiver_issued', 'label' => 'Waiver']);

    $service = app(RefundService::class);
    $refund  = $service->request($bill, 2000, 'waiver_issued', $user);
    $approved = $service->approve($refund, $user);

    expect($approved->status)->toBe(RefundStatus::Completed);
    $this->assertDatabaseHas('ledger_entries', ['bill_id' => $bill->id, 'entry_type' => 'reversal']);
    $this->assertDatabaseHas('reconciliation_flags', ['source_type' => 'refund', 'status' => 'open']);
});

test('reason code required', function () {
    $user = User::factory()->create();
    $bill = Bill::factory()->for($user)->create([
        'total_cents'    => 10000,
        'paid_cents'     => 10000,
        'refunded_cents' => 0,
    ]);

    $service = app(RefundService::class);

    expect(fn () => $service->request($bill, 1000, '', $user))
        ->toThrow(RuntimeException::class);
});
