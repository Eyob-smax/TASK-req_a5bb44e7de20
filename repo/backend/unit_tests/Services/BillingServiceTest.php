<?php

declare(strict_types=1);

use App\Enums\BillStatus;
use App\Enums\BillType;
use App\Models\BillSchedule;
use App\Models\FeeCategory;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('generateInitialBill creates a bill with correct type', function () {
    $user     = User::factory()->create();
    $category = FeeCategory::factory()->create(['is_taxable' => false]);
    $schedule = BillSchedule::factory()->for($user)->for($category)->create([
        'amount_cents'  => 5000,
        'schedule_type' => 'one_time',
        'status'        => 'active',
        'start_on'      => now()->toDateString(),
    ]);

    $service = app(BillingService::class);
    $bill    = $service->generateInitialBill($user, $schedule);

    expect($bill->type)->toBe(BillType::Initial)
        ->and($bill->total_cents)->toBe(5000)
        ->and($bill->status)->toBe(BillStatus::Open);
});

test('applyPenalty is idempotent on same bill same day', function () {
    $user     = User::factory()->create();
    $bill     = \App\Models\Bill::factory()->for($user)->create([
        'status'      => BillStatus::Open,
        'total_cents' => 10000,
        'paid_cents'  => 0,
        'refunded_cents' => 0,
        'due_on'      => now()->subDays(20)->toDateString(),
    ]);

    $service = app(BillingService::class);
    $today   = new DateTimeImmutable(now()->toDateString());

    $first  = $service->applyPenalty($bill, $today);
    $second = $service->applyPenalty($bill, $today);

    expect($first)->not->toBeNull()
        ->and($second)->toBeNull();
});

test('generateSupplemental creates bill with correct amount', function () {
    $user    = User::factory()->create();
    $service = app(BillingService::class);
    $bill    = $service->generateSupplemental($user, ['amount_cents' => 2500], 'Manual fee');

    expect($bill->type)->toBe(BillType::Supplemental)
        ->and($bill->total_cents)->toBe(2500);
});
