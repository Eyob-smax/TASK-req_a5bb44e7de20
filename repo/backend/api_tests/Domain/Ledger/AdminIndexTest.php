<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Enums\LedgerEntryType;
use App\Models\LedgerEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('admin can list ledger entries', function () {
    $admin = User::factory()->asAdmin()->create(['status' => AccountStatus::Active]);
    $user  = User::factory()->create(['status' => AccountStatus::Active]);

    LedgerEntry::create([
        'user_id'        => $user->id,
        'entry_type'     => LedgerEntryType::Charge,
        'amount_cents'   => 5000,
        'description'    => 'Test charge',
        'correlation_id' => Str::uuid(),
    ]);

    $response = $this->actingAs($admin)->getJson('/api/v1/admin/ledger');

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['data']]);
});

test('admin can filter ledger entries by user_id', function () {
    $admin = User::factory()->asAdmin()->create(['status' => AccountStatus::Active]);
    $user  = User::factory()->create(['status' => AccountStatus::Active]);

    LedgerEntry::create([
        'user_id'     => $user->id,
        'entry_type'  => LedgerEntryType::Charge,
        'amount_cents' => 1000,
        'description' => 'Test',
        'posted_at'   => now(),
    ]);

    $response = $this->actingAs($admin)->getJson("/api/v1/admin/ledger?user_id={$user->id}");

    $response->assertStatus(200);
    $entries = $response->json('data.data');
    expect($entries)->not->toBeEmpty();
    expect($entries[0]['user_id'])->toBe($user->id);
});

test('non-admin cannot access ledger', function () {
    $user = User::factory()->create(['status' => AccountStatus::Active]);

    $this->actingAs($user)->getJson('/api/v1/admin/ledger')
        ->assertStatus(403);
});
