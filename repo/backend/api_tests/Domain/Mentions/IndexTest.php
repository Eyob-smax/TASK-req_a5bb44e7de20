<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('GET /mentions returns current user mentions', function () {
    $user = User::factory()->create(['status' => AccountStatus::Active]);

    $response = $this->actingAs($user)->getJson('/api/v1/mentions');

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});

test('unauthenticated request to mentions returns 401', function () {
    $this->getJson('/api/v1/mentions')
        ->assertStatus(401);
});
