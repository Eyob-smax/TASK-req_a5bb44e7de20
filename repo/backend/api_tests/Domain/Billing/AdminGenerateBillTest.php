<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Enums\BillType;
use App\Enums\RoleName;
use App\Models\BillSchedule;
use App\Models\FeeCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can generate initial bill', function () {
    $admin    = User::factory()->create(['status' => AccountStatus::Active]);
    $student  = User::factory()->create(['status' => AccountStatus::Active]);
    $category = FeeCategory::factory()->create(['is_taxable' => false]);
    $schedule = BillSchedule::factory()->for($student)->for($category)->create([
        'amount_cents'  => 10000,
        'schedule_type' => 'one_time',
        'status'        => 'active',
        'start_on'      => now()->toDateString(),
    ]);

    \App\Models\UserRole::create([
        'user_id'    => $admin->id,
        'role'       => RoleName::Administrator,
        'scope_type' => null,
        'scope_id'   => null,
    ]);

    $response = $this->actingAs($admin)->postJson('/api/v1/admin/bills/generate', [
        'user_id'          => $student->id,
        'type'             => 'initial',
        'bill_schedule_id' => $schedule->id,
    ], ['Idempotency-Key' => 'test-key-' . uniqid()]);

    $response->assertStatus(201)
        ->assertJsonPath('data.type', BillType::Initial->value);

    $this->assertDatabaseHas('ledger_entries', ['entry_type' => 'charge', 'user_id' => $student->id]);
});

test('non-admin user cannot generate bills', function () {
    $student = User::factory()->create(['status' => AccountStatus::Active]);

    $response = $this->actingAs($student)->postJson('/api/v1/admin/bills/generate', [
        'user_id' => $student->id,
        'type'    => 'supplemental',
        'amount_cents' => 1000,
        'reason'  => 'Unauthorized attempt',
    ], ['Idempotency-Key' => 'unauth-key-' . uniqid()]);

    $response->assertStatus(403);
});

test('non-admin cannot list all bills via admin endpoint', function () {
    $student = User::factory()->asStudent()->create();

    $this->actingAs($student)
        ->getJson('/api/v1/admin/bills')
        ->assertForbidden();
});

test('teacher cannot list all bills via admin endpoint', function () {
    $teacher = User::factory()->asTeacher()->create();

    $this->actingAs($teacher)
        ->getJson('/api/v1/admin/bills')
        ->assertForbidden();
});

test('admin can list all bills via admin endpoint', function () {
    $admin = User::factory()->asAdmin()->create();

    $this->actingAs($admin)
        ->getJson('/api/v1/admin/bills')
        ->assertOk()
        ->assertJsonStructure(['data']);
});

test('admin can generate supplemental bill', function () {
    $admin   = User::factory()->create(['status' => AccountStatus::Active]);
    $student = User::factory()->create(['status' => AccountStatus::Active]);

    \App\Models\UserRole::create([
        'user_id'    => $admin->id,
        'role'       => RoleName::Administrator,
        'scope_type' => null,
        'scope_id'   => null,
    ]);

    $response = $this->actingAs($admin)->postJson('/api/v1/admin/bills/generate', [
        'user_id'      => $student->id,
        'type'         => 'supplemental',
        'amount_cents' => 5000,
        'reason'       => 'Lab fee',
    ], ['Idempotency-Key' => 'test-key-supp-' . uniqid()]);

    $response->assertStatus(201)
        ->assertJsonPath('data.total_cents', 5000);
});
