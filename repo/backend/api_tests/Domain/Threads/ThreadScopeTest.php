<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\RoleName;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\Term;
use App\Models\Thread;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('student only sees threads from their enrolled sections in list', function () {
    $student = User::factory()->asStudent()->create(['status' => AccountStatus::Active]);
    $term    = Term::factory()->create();
    $course1 = Course::factory()->for($term)->create();
    $course2 = Course::factory()->for($term)->create();
    $section1 = Section::factory()->for($course1)->for($term)->create();
    $section2 = Section::factory()->for($course2)->for($term)->create();

    Enrollment::factory()->create([
        'user_id'    => $student->id,
        'section_id' => $section1->id,
        'status'     => EnrollmentStatus::Enrolled,
    ]);

    $threadInEnrolled    = Thread::factory()->create(['section_id' => $section1->id, 'course_id' => $course1->id]);
    $threadNotEnrolled   = Thread::factory()->create(['section_id' => $section2->id, 'course_id' => $course2->id]);

    $response = $this->actingAs($student)->getJson('/api/v1/threads');

    $response->assertOk();
    $ids = collect($response->json('data.data'))->pluck('id');
    expect($ids)->toContain($threadInEnrolled->id);
    expect($ids)->not->toContain($threadNotEnrolled->id);
});

test('student cannot view thread detail in a section they are not enrolled in', function () {
    $student = User::factory()->asStudent()->create(['status' => AccountStatus::Active]);
    $term    = Term::factory()->create();
    $course  = Course::factory()->for($term)->create();
    $section = Section::factory()->for($course)->for($term)->create();

    // No enrollment created — student has no access
    $thread = Thread::factory()->create(['section_id' => $section->id, 'course_id' => $course->id]);

    $this->actingAs($student)
        ->getJson("/api/v1/threads/{$thread->id}")
        ->assertForbidden();
});

test('student enrolled in a section can view thread detail in that section', function () {
    $student = User::factory()->asStudent()->create(['status' => AccountStatus::Active]);
    $term    = Term::factory()->create();
    $course  = Course::factory()->for($term)->create();
    $section = Section::factory()->for($course)->for($term)->create();

    Enrollment::factory()->create([
        'user_id'    => $student->id,
        'section_id' => $section->id,
        'status'     => EnrollmentStatus::Enrolled,
    ]);

    $thread = Thread::factory()->create(['section_id' => $section->id, 'course_id' => $course->id]);

    $this->actingAs($student)
        ->getJson("/api/v1/threads/{$thread->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $thread->id);
});

test('admin sees threads from all sections without enrollment filter', function () {
    $admin   = User::factory()->asAdmin()->create(['status' => AccountStatus::Active]);
    $term    = Term::factory()->create();
    $course1 = Course::factory()->for($term)->create();
    $course2 = Course::factory()->for($term)->create();
    $section1 = Section::factory()->for($course1)->for($term)->create();
    $section2 = Section::factory()->for($course2)->for($term)->create();

    $thread1 = Thread::factory()->create(['section_id' => $section1->id, 'course_id' => $course1->id]);
    $thread2 = Thread::factory()->create(['section_id' => $section2->id, 'course_id' => $course2->id]);

    $response = $this->actingAs($admin)->getJson('/api/v1/threads');

    $response->assertOk();
    $ids = collect($response->json('data.data'))->pluck('id');
    expect($ids)->toContain($thread1->id);
    expect($ids)->toContain($thread2->id);
});

test('teacher sees threads from all sections without enrollment filter', function () {
    $teacher = User::factory()->asTeacher()->create(['status' => AccountStatus::Active]);
    $term    = Term::factory()->create();
    $course1 = Course::factory()->for($term)->create();
    $course2 = Course::factory()->for($term)->create();
    $section1 = Section::factory()->for($course1)->for($term)->create();
    $section2 = Section::factory()->for($course2)->for($term)->create();

    $thread1 = Thread::factory()->create(['section_id' => $section1->id, 'course_id' => $course1->id]);
    $thread2 = Thread::factory()->create(['section_id' => $section2->id, 'course_id' => $course2->id]);

    $response = $this->actingAs($teacher)->getJson('/api/v1/threads');

    $response->assertOk();
    $ids = collect($response->json('data.data'))->pluck('id');
    expect($ids)->toContain($thread1->id);
    expect($ids)->toContain($thread2->id);
});

test('section-scoped teacher only sees threads in their assigned course', function () {
    $teacher = User::factory()->create(['status' => AccountStatus::Active]);
    $term    = Term::factory()->create();
    $course1 = Course::factory()->for($term)->create();
    $course2 = Course::factory()->for($term)->create();
    $section1 = Section::factory()->for($course1)->for($term)->create();
    $section2 = Section::factory()->for($course2)->for($term)->create();

    // Teacher granted only to course1 (not course2)
    UserRole::create([
        'user_id'    => $teacher->id,
        'role'       => RoleName::Teacher,
        'scope_type' => 'course',
        'scope_id'   => $course1->id,
    ]);

    $threadInScope    = Thread::factory()->create(['section_id' => $section1->id, 'course_id' => $course1->id]);
    $threadOutOfScope = Thread::factory()->create(['section_id' => $section2->id, 'course_id' => $course2->id]);

    $response = $this->actingAs($teacher)->getJson('/api/v1/threads');

    $response->assertOk();
    $ids = collect($response->json('data.data'))->pluck('id');
    expect($ids)->toContain($threadInScope->id);
    expect($ids)->not->toContain($threadOutOfScope->id);
});

test('section-scoped teacher cannot view thread detail outside their scope', function () {
    $teacher = User::factory()->create(['status' => AccountStatus::Active]);
    $term    = Term::factory()->create();
    $course1 = Course::factory()->for($term)->create();
    $course2 = Course::factory()->for($term)->create();
    $section2 = Section::factory()->for($course2)->for($term)->create();

    // Teacher granted only to course1
    UserRole::create([
        'user_id'    => $teacher->id,
        'role'       => RoleName::Teacher,
        'scope_type' => 'course',
        'scope_id'   => $course1->id,
    ]);

    $outOfScopeThread = Thread::factory()->create(['section_id' => $section2->id, 'course_id' => $course2->id]);

    $this->actingAs($teacher)
        ->getJson("/api/v1/threads/{$outOfScopeThread->id}")
        ->assertForbidden();
});
