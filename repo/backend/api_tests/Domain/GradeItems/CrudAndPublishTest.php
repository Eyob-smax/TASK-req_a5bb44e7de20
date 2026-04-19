<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Enums\GradeItemState;
use App\Models\Course;
use App\Models\Section;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can create a grade item', function () {
    $user    = User::factory()->create(['status' => AccountStatus::Active]);
    $term    = Term::factory()->create();
    $course  = Course::factory()->for($term)->create();
    $section = Section::factory()->for($course)->create(['term_id' => $term->id]);

    $response = $this->actingAs($user)->postJson("/api/v1/sections/{$section->id}/grade-items", [
        'title'     => 'Midterm Exam',
        'max_score' => 100,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.state', GradeItemState::Draft->value);
});

test('publish changes state to published', function () {
    $user    = User::factory()->create(['status' => AccountStatus::Active]);
    $term    = Term::factory()->create();
    $course  = Course::factory()->for($term)->create();
    $section = Section::factory()->for($course)->create(['term_id' => $term->id]);

    $createResponse = $this->actingAs($user)->postJson("/api/v1/sections/{$section->id}/grade-items", [
        'title'     => 'Final Exam',
        'max_score' => 200,
    ]);

    $gradeItemId = $createResponse->json('data.id');

    $publishResponse = $this->actingAs($user)->postJson(
        "/api/v1/sections/{$section->id}/grade-items/{$gradeItemId}/publish"
    );

    $publishResponse->assertStatus(200)
        ->assertJsonPath('data.state', GradeItemState::Published->value);
});
