<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Models\RosterImport;
use App\Models\Section;
use App\Models\Term;
use App\Models\User;
use App\Services\RosterImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('import rejects non-registrar operator', function () {
    $operator = User::factory()->create();
    $term     = Term::factory()->create();

    $service = app(RosterImportService::class);

    expect(fn () => $service->import($operator, $term, 'test.csv', '/tmp/test.csv'))
        ->toThrow(RuntimeException::class, 'not authorized');
});

test('import records error for unreadable file', function () {
    $operator = User::factory()->create();
    $term     = Term::factory()->create();

    // Grant registrar role globally
    \App\Models\UserRole::create([
        'user_id'    => $operator->id,
        'role'       => RoleName::Registrar,
        'scope_type' => null,
        'scope_id'   => null,
    ]);

    $service = app(RosterImportService::class);
    $import  = $service->import($operator, $term, 'test.csv', '/nonexistent/path.csv');

    expect($import->error_count)->toBe(1)
        ->and($import->errors()->first()->error_code)->toBe('file_unreadable');
});

test('import parses csv and creates enrollments', function () {
    $operator = User::factory()->create();
    $term     = Term::factory()->create();
    $section  = Section::factory()->for(\App\Models\Course::factory()->for($term))->create([
        'term_id'      => $term->id,
        'section_code' => 'CS101-A',
    ]);

    \App\Models\UserRole::create([
        'user_id'    => $operator->id,
        'role'       => RoleName::Registrar,
        'scope_type' => null,
        'scope_id'   => null,
    ]);

    $csv = tempnam(sys_get_temp_dir(), 'roster');
    file_put_contents($csv, "email,name,section_code\nstudent@example.com,Student One,CS101-A\n");

    $service = app(RosterImportService::class);
    $import  = $service->import($operator, $term, 'test.csv', $csv);

    unlink($csv);

    expect($import->success_count)->toBe(1)
        ->and($import->error_count)->toBe(0);

    $this->assertDatabaseHas('users', ['email' => 'student@example.com']);
    $this->assertDatabaseHas('enrollments', ['section_id' => $section->id]);
});
