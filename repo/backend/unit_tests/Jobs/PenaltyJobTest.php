<?php

declare(strict_types=1);

use App\Enums\BillStatus;
use App\Jobs\PenaltyJob;
use App\Models\Bill;
use App\Models\PenaltyJob as PenaltyJobModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('applies penalty to past-due bill and creates penalty_jobs row', function () {
    $user = User::factory()->create();
    Bill::factory()->for($user)->create([
        'status'         => BillStatus::Open,
        'total_cents'    => 10000,
        'paid_cents'     => 0,
        'refunded_cents' => 0,
        'due_on'         => now()->subDays(20)->toDateString(),
    ]);

    PenaltyJob::dispatchSync();

    $this->assertDatabaseHas('bills', ['type' => 'penalty']);
    $this->assertDatabaseHas('penalty_jobs', ['status' => 'applied']);
});

test('does not duplicate penalty for same bill same day', function () {
    $user = User::factory()->create();
    $bill = Bill::factory()->for($user)->create([
        'status'         => BillStatus::Open,
        'total_cents'    => 10000,
        'paid_cents'     => 0,
        'refunded_cents' => 0,
        'due_on'         => now()->subDays(20)->toDateString(),
    ]);

    PenaltyJob::dispatchSync();
    PenaltyJob::dispatchSync();

    $count = PenaltyJobModel::where('bill_id', $bill->id)->count();
    expect($count)->toBe(1);
});
