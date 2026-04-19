<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('GET /sections/{id} returns section', function () {
    $user    = User::factory()->create(['status' => AccountStatus::Active]);
    $section = Section::factory()->create();

    $response = $this->actingAs($user)->getJson("/api/v1/sections/{$section->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $section->id);
});

test('GET /sections/{id}/roster returns section roster', function () {
    $admin   = User::factory()->asAdmin()->create(['status' => AccountStatus::Active]);
    $section = Section::factory()->create();
    $student = User::factory()->create(['status' => AccountStatus::Active]);

    Enrollment::factory()->for($student)->for($section)->create();

    $response = $this->actingAs($admin)->getJson("/api/v1/sections/{$section->id}/roster");

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});
