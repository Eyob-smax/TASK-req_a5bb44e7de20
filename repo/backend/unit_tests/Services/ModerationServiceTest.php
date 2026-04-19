<?php

declare(strict_types=1);

use App\Enums\ContentState;
use App\Enums\ModerationActionType;
use App\Models\Thread;
use App\Models\User;
use App\Services\ModerationService;
use CampusLearn\Support\Exceptions\InvalidStateTransition;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('apply hide changes thread state to hidden', function () {
    $actor  = User::factory()->create();
    $thread = Thread::factory()->create(['state' => ContentState::Visible]);

    $service = app(ModerationService::class);
    $service->apply($actor, $thread, ModerationActionType::Hide, 'violates rules');

    expect($thread->fresh()->state)->toBe(ContentState::Hidden);
    $this->assertDatabaseHas('audit_log_entries', ['action' => 'moderation.hide']);
    $this->assertDatabaseHas('moderation_actions', ['target_type' => 'thread', 'action' => 'hide']);
});

test('apply creates moderation action row', function () {
    $actor  = User::factory()->create();
    $thread = Thread::factory()->create(['state' => ContentState::Visible]);

    $service = app(ModerationService::class);
    $service->apply($actor, $thread, ModerationActionType::Hide, 'test notes');

    $this->assertDatabaseHas('moderation_actions', [
        'moderator_id' => $actor->id,
        'target_type'  => 'thread',
        'target_id'    => $thread->id,
        'action'       => 'hide',
    ]);
});

test('apply restore unhides a hidden thread', function () {
    $actor  = User::factory()->create();
    $thread = Thread::factory()->create(['state' => ContentState::Hidden]);

    $service = app(ModerationService::class);
    $service->apply($actor, $thread, ModerationActionType::Restore, '');

    expect($thread->fresh()->state)->toBe(ContentState::Visible);
});

test('apply lock on already locked thread throws invalid transition', function () {
    $actor  = User::factory()->create();
    $thread = Thread::factory()->create(['state' => ContentState::Locked]);

    $service = app(ModerationService::class);

    expect(fn () => $service->apply($actor, $thread, ModerationActionType::Lock, ''))
        ->toThrow(\RuntimeException::class);
});
