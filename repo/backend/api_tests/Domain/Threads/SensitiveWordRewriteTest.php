<?php

use App\Models\SensitiveWordRule;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Sensitive-word filtering — submit-time enforcement.

it('thread creation with blocked terms returns 422 with highlighted positions', function () {
    $student = User::factory()->asStudent()->create();
    $section = Section::factory()->create();

    $student->enrollments()->create(['section_id' => $section->id, 'status' => 'enrolled']);

    SensitiveWordRule::factory()->exact()->create(['pattern' => 'forbidden_test_term']);

    $response = $this->actingAs($student)->postJson('/api/v1/threads', [
        'section_id' => $section->id,
        'type'       => 'discussion',
        'title'      => 'Fine title',
        'body'       => 'This post contains forbidden_test_term inside it.',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('error.code', 'SENSITIVE_WORDS_BLOCKED')
        ->assertJsonStructure(['error' => ['blocked_terms' => [['term', 'start', 'end']]]]);
});

it('post with no blocked terms is created successfully', function () {
    $student = User::factory()->asStudent()->create();
    $section = Section::factory()->create();
    $student->enrollments()->create(['section_id' => $section->id, 'status' => 'enrolled']);

    $response = $this->actingAs($student)->postJson('/api/v1/threads', [
        'section_id' => $section->id,
        'type'       => 'discussion',
        'title'      => 'Clean title',
        'body'       => 'This is a perfectly acceptable discussion post.',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.state', 'visible');
});

it('sensitive-word check endpoint returns blocked terms for given body', function () {
    SensitiveWordRule::factory()->exact()->create(['pattern' => 'check_blocked']);
    $user = User::factory()->asStudent()->create();

    $response = $this->actingAs($user)->postJson('/api/v1/sensitive-words/check', [
        'body' => 'This body contains check_blocked somewhere.',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.blocked', true)
        ->assertJsonCount(1, 'data.blocked_terms');
});
