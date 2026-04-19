<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can list own notifications', function () {
    $user = User::factory()->create(['status' => AccountStatus::Active]);

    Notification::create([
        'user_id'    => $user->id,
        'category'   => 'system',
        'type'       => 'test.event',
        'title'      => 'Test',
        'body'       => 'Test body',
        'payload'    => [],
        'read_at'    => null,
        'created_at' => now(),
    ]);

    $response = $this->actingAs($user)->getJson('/api/v1/notifications');
    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['data']]);
});

test('unread count returns category breakdown', function () {
    $user = User::factory()->create(['status' => AccountStatus::Active]);

    Notification::create([
        'user_id'    => $user->id,
        'category'   => 'billing',
        'type'       => 'billing.paid',
        'title'      => 'Paid',
        'body'       => 'Paid body',
        'payload'    => [],
        'read_at'    => null,
        'created_at' => now(),
    ]);

    $response = $this->actingAs($user)->getJson('/api/v1/notifications/unread-count');
    $response->assertStatus(200)
        ->assertJsonPath('data.billing', 1);
});
