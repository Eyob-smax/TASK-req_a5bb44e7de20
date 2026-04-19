<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Models\FeeCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can create fee category', function () {
    $admin = User::factory()->asAdmin()->create(['status' => AccountStatus::Active]);

    $response = $this->actingAs($admin)->postJson('/api/v1/admin/fee-categories', [
        'code'       => 'tuition',
        'label'      => 'Tuition Fees',
        'is_taxable' => false,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.code', 'tuition');
});

test('admin can update fee category', function () {
    $admin    = User::factory()->asAdmin()->create(['status' => AccountStatus::Active]);
    $category = FeeCategory::factory()->create(['label' => 'Old Label', 'is_taxable' => false]);

    $response = $this->actingAs($admin)->patchJson("/api/v1/admin/fee-categories/{$category->id}", [
        'label' => 'Updated Label',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.label', 'Updated Label');
});
