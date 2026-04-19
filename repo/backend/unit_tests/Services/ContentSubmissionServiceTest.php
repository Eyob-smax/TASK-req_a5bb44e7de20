<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\Post;
use App\Models\Section;
use App\Models\SensitiveWordRule;
use App\Models\Term;
use App\Models\Thread;
use App\Models\User;
use App\Services\ContentSubmissionService;
use CampusLearn\Support\Exceptions\EditWindowExpired;
use CampusLearn\Support\Exceptions\SensitiveWordMatched;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();
});

function makeSection(): Section
{
    $term    = Term::factory()->create();
    $course  = Course::factory()->for($term)->create();
    return Section::factory()->for($course)->create(['term_id' => $term->id]);
}

test('createThread throws SensitiveWordMatched when pattern found in body', function () {
    SensitiveWordRule::factory()->exact()->create(['pattern' => 'blocked_term']);
    $user    = User::factory()->create();
    $section = makeSection();

    $service = app(ContentSubmissionService::class);

    expect(fn () => $service->createThread($user, [
        'course_id'    => $section->course_id,
        'section_id'   => $section->id,
        'thread_type'  => 'discussion',
        'title'        => 'Normal title',
        'body'         => 'This body contains blocked_term here.',
    ]))->toThrow(SensitiveWordMatched::class);
});

test('createThread persists thread in database on clean body', function () {
    $user    = User::factory()->create();
    $section = makeSection();

    $service = app(ContentSubmissionService::class);
    $thread  = $service->createThread($user, [
        'course_id'   => $section->course_id,
        'section_id'  => $section->id,
        'thread_type' => 'discussion',
        'title'       => 'Test Thread',
        'body'        => 'Perfectly clean content.',
    ]);

    expect($thread->id)->toBeInt()
        ->and($thread->title)->toBe('Test Thread');

    $this->assertDatabaseHas('threads', ['id' => $thread->id]);
});

test('createThread extracts at-mention and persists mention row', function () {
    $author    = User::factory()->create();
    $mentioned = User::factory()->create(['email' => 'mentioned.user@example.com']);
    $section   = makeSection();

    $service = app(ContentSubmissionService::class);
    $service->createThread($author, [
        'course_id'   => $section->course_id,
        'section_id'  => $section->id,
        'thread_type' => 'discussion',
        'title'       => 'Mention test',
        'body'        => "Hello @mentioned.user please review.",
    ]);

    $this->assertDatabaseHas('mentions', [
        'mentioned_user_id' => $mentioned->id,
    ]);
});

test('updatePost rejects edit after 15-minute window', function () {
    $user    = User::factory()->create();
    $section = makeSection();

    $post = Post::factory()->create([
        'author_id'  => $user->id,
        'created_at' => now()->subMinutes(20),
    ]);

    $service = app(ContentSubmissionService::class);

    expect(fn () => $service->updatePost($user, $post, ['body' => 'Updated body'], false))
        ->toThrow(EditWindowExpired::class);
});

test('updatePost succeeds within 15-minute window', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'author_id'  => $user->id,
        'created_at' => now()->subMinutes(5),
    ]);

    $service     = app(ContentSubmissionService::class);
    $updatedPost = $service->updatePost($user, $post, ['body' => 'Updated body'], false);

    expect($updatedPost->body)->toBe('Updated body');
});

test('createPost writes audit_log_entries row inside transaction', function () {
    $user   = User::factory()->create();
    $thread = Thread::factory()->create(['author_id' => $user->id]);

    $service = app(ContentSubmissionService::class);
    $service->createPost($user, $thread, ['body' => 'A reply post.']);

    $this->assertDatabaseHas('audit_log_entries', [
        'actor_user_id' => $user->id,
        'action'        => 'post.created',
    ]);
});
