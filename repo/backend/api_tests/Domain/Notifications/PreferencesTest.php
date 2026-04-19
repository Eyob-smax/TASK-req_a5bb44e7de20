<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can get their notification preferences', function () {
    $user = User::factory()->create(['status' => AccountStatus::Active]);

    $response = $this->actingAs($user)->getJson('/api/v1/notifications/preferences');

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});

test('authenticated user can update notification preferences', function () {
    $user = User::factory()->create(['status' => AccountStatus::Active]);

    $response = $this->actingAs($user)->patchJson('/api/v1/notifications/preferences', [
        'preferences' => [
            'announcements' => false,
            'mentions'      => true,
            'billing'       => true,
            'system'        => false,
        ],
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.announcements', false)
        ->assertJsonPath('data.mentions', true);
});

test('unauthenticated request to preferences returns 401', function () {
    $this->getJson('/api/v1/notifications/preferences')
        ->assertStatus(401);
});
