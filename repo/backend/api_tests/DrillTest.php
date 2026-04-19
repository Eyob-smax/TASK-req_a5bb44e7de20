<?php

declare(strict_types=1);

use App\Enums\DrDrillOutcome;
use App\Models\DrDrillRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// GET /api/v1/admin/dr-drills
test('admin can list DR drill records', function () {
    $admin = User::factory()->asAdmin()->create();

    DrDrillRecord::create([
        'drill_date'       => '2025-01-15',
        'operator_user_id' => $admin->id,
        'outcome'          => DrDrillOutcome::Passed,
        'notes'            => 'All systems restored in under 2 hours.',
    ]);

    $response = $this->actingAs($admin)->getJson('/api/v1/admin/dr-drills');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'data' => [['id', 'drill_date', 'outcome']],
            ],
        ]);
});

test('non-admin cannot list DR drills', function () {
    $teacher = User::factory()->asTeacher()->create();

    $this->actingAs($teacher)
        ->getJson('/api/v1/admin/dr-drills')
        ->assertForbidden();
});

// POST /api/v1/admin/dr-drills
test('admin can record a passed drill', function () {
    $admin = User::factory()->asAdmin()->create();

    $response = $this->actingAs($admin)->postJson('/api/v1/admin/dr-drills', [
        'drill_date' => '2025-04-01',
        'outcome'    => DrDrillOutcome::Passed->value,
        'notes'      => 'Recovery completed in 90 minutes.',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.outcome', DrDrillOutcome::Passed->value);

    $this->assertDatabaseHas('dr_drill_records', [
        'operator_user_id' => $admin->id,
        'outcome'          => DrDrillOutcome::Passed->value,
    ]);
});

test('admin can record a partial drill outcome', function () {
    $admin = User::factory()->asAdmin()->create();

    $this->actingAs($admin)->postJson('/api/v1/admin/dr-drills', [
        'drill_date' => '2025-04-15',
        'outcome'    => DrDrillOutcome::Partial->value,
        'notes'      => 'Queue recovery failed, DB restored successfully.',
    ])->assertStatus(201)
        ->assertJsonPath('data.outcome', DrDrillOutcome::Partial->value);
});

test('drill record requires drill_date and outcome', function () {
    $admin = User::factory()->asAdmin()->create();

    $this->actingAs($admin)->postJson('/api/v1/admin/dr-drills', [])
        ->assertUnprocessable();
});

test('invalid outcome value is rejected', function () {
    $admin = User::factory()->asAdmin()->create();

    $this->actingAs($admin)->postJson('/api/v1/admin/dr-drills', [
        'drill_date' => '2025-04-01',
        'outcome'    => 'catastrophic',
    ])->assertUnprocessable();
});

test('drill record creates audit log entry', function () {
    $admin = User::factory()->asAdmin()->create();

    $this->actingAs($admin)->postJson('/api/v1/admin/dr-drills', [
        'drill_date' => '2025-04-01',
        'outcome'    => DrDrillOutcome::Passed->value,
    ])->assertStatus(201);

    $this->assertDatabaseHas('audit_log_entries', [
        'actor_user_id' => $admin->id,
        'action'        => 'dr_drill.recorded',
        'target_type'   => 'dr_drill_record',
    ]);
});

test('unauthenticated request is rejected', function () {
    $this->postJson('/api/v1/admin/dr-drills', [
        'drill_date' => '2025-04-01',
        'outcome'    => DrDrillOutcome::Passed->value,
    ])->assertUnauthorized();
});
