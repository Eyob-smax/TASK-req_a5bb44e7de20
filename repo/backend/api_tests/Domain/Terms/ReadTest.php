<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('GET /terms returns paginated list', function () {
    $user = User::factory()->create(['status' => AccountStatus::Active]);
    Term::factory()->count(3)->create();

    $response = $this->actingAs($user)->getJson('/api/v1/terms');

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
    expect(count($response->json('data')))->toBeGreaterThanOrEqual(3);
});

test('GET /terms/{id} returns single term', function () {
    $user = User::factory()->create(['status' => AccountStatus::Active]);
    $term = Term::factory()->create(['name' => 'Spring 2025']);

    $response = $this->actingAs($user)->getJson("/api/v1/terms/{$term->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Spring 2025');
});
