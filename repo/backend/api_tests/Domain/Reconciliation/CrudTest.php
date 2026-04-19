<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Enums\ReconciliationSourceType;
use App\Enums\ReconciliationStatus;
use App\Models\ReconciliationFlag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can list open reconciliation flags', function () {
    $admin = User::factory()->asAdmin()->create(['status' => AccountStatus::Active]);

    ReconciliationFlag::create([
        'source_type' => ReconciliationSourceType::LedgerMismatch,
        'source_id'   => 1,
        'status'      => ReconciliationStatus::Open,
        'notes'       => 'Mismatch detected',
    ]);

    $response = $this->actingAs($admin)->getJson('/api/v1/admin/reconciliation');

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});

test('admin can resolve a reconciliation flag', function () {
    $admin = User::factory()->asAdmin()->create(['status' => AccountStatus::Active]);

    $flag = ReconciliationFlag::create([
        'source_type' => ReconciliationSourceType::Refund,
        'source_id'   => 1,
        'status'      => ReconciliationStatus::Open,
        'notes'       => 'Refund discrepancy',
    ]);

    $response = $this->actingAs($admin)->postJson("/api/v1/admin/reconciliation/{$flag->id}/resolve", [
        'notes' => 'Reviewed and confirmed correct.',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('reconciliation_flags', [
        'id'     => $flag->id,
        'status' => ReconciliationStatus::Resolved->value,
    ]);
});

test('non-admin cannot access reconciliation', function () {
    $user = User::factory()->create(['status' => AccountStatus::Active]);

    $this->actingAs($user)->getJson('/api/v1/admin/reconciliation')
        ->assertStatus(403);
});
