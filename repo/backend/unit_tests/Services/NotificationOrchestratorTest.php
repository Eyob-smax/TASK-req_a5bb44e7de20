<?php

declare(strict_types=1);

use App\Models\NotificationSubscription;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Services\NotificationOrchestrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();
});

test('notify dispatches jobs to eligible recipients', function () {
    NotificationTemplate::create([
        'type'           => 'test.event',
        'category'       => 'system',
        'title_template' => 'Hello {: name}',
        'body_template'  => 'Body text',
    ]);

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $orchestrator = app(NotificationOrchestrator::class);
    $result = $orchestrator->notify('test.event', [$user1->id, $user2->id], []);

    expect($result['queued'])->toBe(2)
        ->and($result['skipped_no_template'])->toBeFalse();
});

test('notify skips opted-out recipients', function () {
    NotificationTemplate::create([
        'type'           => 'test.event',
        'category'       => 'system',
        'title_template' => 'Hello',
        'body_template'  => 'Body',
    ]);

    $user = User::factory()->create();

    NotificationSubscription::create([
        'user_id'  => $user->id,
        'category' => 'system',
        'enabled'  => false,
    ]);

    $orchestrator = app(NotificationOrchestrator::class);
    $result = $orchestrator->notify('test.event', [$user->id], []);

    expect($result['queued'])->toBe(0)
        ->and($result['skipped_subscription'])->toBe(1);
});

test('notify returns skipped_no_template when template missing', function () {
    $orchestrator = app(NotificationOrchestrator::class);
    $result = $orchestrator->notify('nonexistent.event', [1], []);

    expect($result['skipped_no_template'])->toBeTrue()
        ->and($result['queued'])->toBe(0);
});
