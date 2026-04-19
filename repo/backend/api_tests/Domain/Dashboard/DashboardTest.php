<?php

use App\Models\User;

// Dashboard endpoint returns role-appropriate summary data.

it('returns student dashboard summary with enrolled sections and open bills', function () {
    $student = User::factory()->asStudent()->create();

    $response = $this->actingAs($student)->getJson('/api/v1/dashboard');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => ['enrolled_sections', 'open_bills', 'unread_notifications', 'pending_orders'],
        ]);
});

it('returns teacher dashboard summary with assigned sections and draft grade items', function () {
    $teacher = User::factory()->asTeacher()->create();

    $response = $this->actingAs($teacher)->getJson('/api/v1/dashboard');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => ['assigned_sections', 'draft_grade_items', 'unread_notifications'],
        ]);
});

it('returns registrar dashboard summary with pending enrollments', function () {
    $registrar = User::factory()->asRegistrar()->create();

    $response = $this->actingAs($registrar)->getJson('/api/v1/dashboard');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => ['pending_enrollments', 'pending_roster_imports', 'unread_notifications'],
        ]);
});

it('returns admin dashboard summary with moderation queue size', function () {
    $admin = User::factory()->asAdmin()->create();

    $response = $this->actingAs($admin)->getJson('/api/v1/dashboard');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => ['moderation_queue_size', 'reconciliation_flags', 'circuit_status'],
        ]);
});

it('rejects unauthenticated dashboard request', function () {
    $this->getJson('/api/v1/dashboard')->assertUnauthorized();
});
