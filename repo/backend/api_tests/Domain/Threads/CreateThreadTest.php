<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Enums\ContentState;
use App\Models\Course;
use App\Models\Section;
use App\Models\SensitiveWordRule;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('authenticated user can create a thread', function () {
    $user    = User::factory()->create(['status' => AccountStatus::Active]);
    $term    = Term::factory()->create();
    $course  = Course::factory()->for($term)->create();
    $section = Section::factory()->for($course)->create(['term_id' => $term->id]);

    $response = $this->actingAs($user)->postJson('/api/v1/threads', [
        'section_id' => $section->id,
        'type'       => 'discussion',
        'title'      => 'Test Thread',
        'body'       => 'Some body content',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.title', 'Test Thread')
        ->assertJsonPath('data.state', ContentState::Visible->value);
});

test('sensitive word in body returns 422', function () {
    SensitiveWordRule::factory()->exact()->create(['pattern' => 'badword', 'is_active' => true]);

    $user    = User::factory()->create(['status' => AccountStatus::Active]);
    $term    = Term::factory()->create();
    $course  = Course::factory()->for($term)->create();
    $section = Section::factory()->for($course)->create(['term_id' => $term->id]);

    $response = $this->actingAs($user)->postJson('/api/v1/threads', [
        'section_id' => $section->id,
        'type'       => 'discussion',
        'title'      => 'Flagged Thread',
        'body'       => 'This contains badword here',
    ]);

    $response->assertStatus(422);
});

test('unauthenticated request returns 401', function () {
    $this->postJson('/api/v1/threads', [
        'section_id' => 1,
        'type'       => 'discussion',
        'title'      => 'Thread',
        'body'       => 'Body',
    ])->assertStatus(401);
});
