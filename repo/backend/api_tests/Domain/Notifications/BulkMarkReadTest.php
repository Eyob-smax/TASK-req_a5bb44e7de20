<?php

use App\Models\User;
use App\Models\Notification;

// Bulk mark-as-read and category-scoped mark-as-read flows.

it('user can bulk mark selected notifications as read', function () {
    $user = User::factory()->asStudent()->create();
    $notifs = Notification::factory()->for($user)->count(3)->create(['read_at' => null]);

    $ids = $notifs->pluck('id')->take(2)->toArray();

    $this->actingAs($user)
        ->postJson('/api/v1/notifications/mark-read', ['ids' => $ids])
        ->assertOk()
        ->assertJsonPath('data.marked', true);

    $this->assertDatabaseHas('notifications', ['id' => $ids[0], 'user_id' => $user->id]);
    expect(Notification::find($ids[0])->read_at)->not->toBeNull();
    expect(Notification::find($notifs[2]->id)->read_at)->toBeNull();
});

it('user can mark all notifications in a category as read', function () {
    $user = User::factory()->asStudent()->create();
    Notification::factory()->for($user)->billing()->count(2)->create(['read_at' => null]);
    Notification::factory()->for($user)->system()->count(1)->create(['read_at' => null]);

    $this->actingAs($user)
        ->postJson('/api/v1/notifications/mark-read', ['category' => 'billing'])
        ->assertOk();

    $unread = Notification::where('user_id', $user->id)
        ->whereNull('read_at')
        ->where('category', 'billing')
        ->count();
    expect($unread)->toBe(0);

    // System notification still unread
    $unreadSystem = Notification::where('user_id', $user->id)
        ->whereNull('read_at')
        ->where('category', 'system')
        ->count();
    expect($unreadSystem)->toBe(1);
});

it('cannot mark another user\'s notifications as read', function () {
    $userA = User::factory()->asStudent()->create();
    $userB = User::factory()->asStudent()->create();
    $notif = Notification::factory()->for($userB)->create(['read_at' => null]);

    $this->actingAs($userA)
        ->postJson('/api/v1/notifications/mark-read', ['ids' => [$notif->id]])
        ->assertOk(); // 200 but should not mark B's notification

    expect($notif->fresh()->read_at)->toBeNull();
});

it('unread count endpoint returns per-category counts', function () {
    $user = User::factory()->asStudent()->create();
    Notification::factory()->for($user)->billing()->count(3)->create(['read_at' => null]);
    Notification::factory()->for($user)->announcements()->count(1)->create(['read_at' => null]);

    $response = $this->actingAs($user)->getJson('/api/v1/notifications/unread-count');

    $response->assertOk()
        ->assertJsonPath('data.billing', 3)
        ->assertJsonPath('data.announcements', 1);
});
