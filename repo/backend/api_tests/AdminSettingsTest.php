<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// GET /api/v1/admin/settings
test('admin can read system settings', function () {
    $admin = User::factory()->asAdmin()->create();

    $response = $this->actingAs($admin)->getJson('/api/v1/admin/settings');

    $response->assertOk()
        ->assertJsonStructure(['data']);
});

test('non-admin cannot read settings', function () {
    $teacher = User::factory()->asTeacher()->create();

    $this->actingAs($teacher)
        ->getJson('/api/v1/admin/settings')
        ->assertForbidden();
});

// PATCH /api/v1/admin/settings
test('admin can update edit window minutes', function () {
    $admin = User::factory()->asAdmin()->create();

    $response = $this->actingAs($admin)->patchJson('/api/v1/admin/settings', [
        'settings' => ['edit_window_minutes' => 20],
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('system_settings', [
        'key'        => 'edit_window_minutes',
        'updated_by' => $admin->id,
    ]);
});

test('admin can update multiple settings at once', function () {
    $admin = User::factory()->asAdmin()->create();

    $this->actingAs($admin)->patchJson('/api/v1/admin/settings', [
        'settings' => [
            'penalty_grace_days' => 14,
            'fanout_batch_size'  => 100,
        ],
    ])->assertOk();

    $this->assertDatabaseHas('system_settings', ['key' => 'penalty_grace_days']);
    $this->assertDatabaseHas('system_settings', ['key' => 'fanout_batch_size']);
});

test('unknown keys are silently ignored', function () {
    $admin = User::factory()->asAdmin()->create();

    $this->actingAs($admin)->patchJson('/api/v1/admin/settings', [
        'settings' => [
            'edit_window_minutes' => 15,
            'unknown_key'         => 'bad-value',
        ],
    ])->assertOk();

    $this->assertDatabaseMissing('system_settings', ['key' => 'unknown_key']);
});

test('invalid value type is rejected', function () {
    $admin = User::factory()->asAdmin()->create();

    $this->actingAs($admin)->patchJson('/api/v1/admin/settings', [
        'settings' => ['edit_window_minutes' => 'not-a-number'],
    ])->assertUnprocessable();
});

test('non-admin cannot update settings', function () {
    $student = User::factory()->asStudent()->create();

    $this->actingAs($student)->patchJson('/api/v1/admin/settings', [
        'settings' => ['edit_window_minutes' => 5],
    ])->assertForbidden();
});

test('settings update creates audit log entry', function () {
    $admin = User::factory()->asAdmin()->create();

    $this->actingAs($admin)->patchJson('/api/v1/admin/settings', [
        'settings' => ['edit_window_minutes' => 10],
    ])->assertOk();

    $this->assertDatabaseHas('audit_log_entries', [
        'actor_user_id' => $admin->id,
        'action'        => 'admin_settings.updated',
    ]);
});

// GET /api/v1/admin/audit-log
test('admin can read audit log', function () {
    $admin = User::factory()->asAdmin()->create();

    $response = $this->actingAs($admin)->getJson('/api/v1/admin/audit-log');

    $response->assertOk()
        ->assertJsonStructure(['data' => ['data']]);
});

test('audit log is filterable by action', function () {
    $admin = User::factory()->asAdmin()->create();

    $this->actingAs($admin)->patchJson('/api/v1/admin/settings', [
        'settings' => ['edit_window_minutes' => 15],
    ]);

    $response = $this->actingAs($admin)->getJson('/api/v1/admin/audit-log?action=admin_settings');

    $entries = collect($response->json('data.data'));
    expect($entries->every(fn ($e) => str_contains($e['action'], 'admin_settings')))->toBeTrue();
});

test('non-admin cannot read audit log', function () {
    $student = User::factory()->asStudent()->create();

    $this->actingAs($student)
        ->getJson('/api/v1/admin/audit-log')
        ->assertForbidden();
});
