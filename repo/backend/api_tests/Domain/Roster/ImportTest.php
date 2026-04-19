<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Enums\RoleName;
use App\Models\Course;
use App\Models\Section;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('registrar can import roster CSV', function () {
    $user    = User::factory()->create(['status' => AccountStatus::Active]);
    $term    = Term::factory()->create();
    $course  = Course::factory()->for($term)->create();
    $section = Section::factory()->for($course)->create([
        'term_id'      => $term->id,
        'section_code' => 'CS101-A',
    ]);

    \App\Models\UserRole::create([
        'user_id'    => $user->id,
        'role'       => RoleName::Registrar,
        'scope_type' => null,
        'scope_id'   => null,
    ]);

    $csv = "email,name,section_code\nnewstudent@example.com,New Student,CS101-A\n";
    $file = UploadedFile::fake()->createWithContent('roster.csv', $csv);

    $response = $this->actingAs($user)->postJson("/api/v1/terms/{$term->id}/roster-imports", [
        'file' => $file,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.success_count', 1)
        ->assertJsonPath('data.error_count', 0);
});

test('non-registrar cannot import roster', function () {
    $user = User::factory()->create(['status' => AccountStatus::Active]);
    $term = Term::factory()->create();

    $csv  = "email,name,section_code\ntest@example.com,Test,CS101\n";
    $file = UploadedFile::fake()->createWithContent('roster.csv', $csv);

    $response = $this->actingAs($user)->postJson("/api/v1/terms/{$term->id}/roster-imports", [
        'file' => $file,
    ]);

    $response->assertStatus(403);
});
