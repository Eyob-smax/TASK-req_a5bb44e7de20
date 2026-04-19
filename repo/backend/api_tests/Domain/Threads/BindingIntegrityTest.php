<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('GET /threads/{thread}/posts/{post} returns 404 when post does not belong to thread', function () {
    $user    = User::factory()->asStudent()->create(['status' => AccountStatus::Active]);
    $thread1 = Thread::factory()->for($user, 'author')->create();
    $thread2 = Thread::factory()->for($user, 'author')->create();
    $post    = Post::factory()->for($thread2)->for($user, 'author')->create();

    $this->actingAs($user)
        ->getJson("/api/v1/threads/{$thread1->id}/posts/{$post->id}")
        ->assertStatus(404);
});

test('PATCH /threads/{thread}/posts/{post} returns 404 when post does not belong to thread', function () {
    $user    = User::factory()->asStudent()->create(['status' => AccountStatus::Active]);
    $thread1 = Thread::factory()->for($user, 'author')->create();
    $thread2 = Thread::factory()->for($user, 'author')->create();
    $post    = Post::factory()->for($thread2)->for($user, 'author')->create();

    $this->actingAs($user)
        ->patchJson("/api/v1/threads/{$thread1->id}/posts/{$post->id}", ['body' => 'updated'])
        ->assertStatus(404);
});

test('DELETE /threads/{thread}/posts/{post} returns 404 when post does not belong to thread', function () {
    $user    = User::factory()->asAdmin()->create(['status' => AccountStatus::Active]);
    $thread1 = Thread::factory()->for($user, 'author')->create();
    $thread2 = Thread::factory()->for($user, 'author')->create();
    $post    = Post::factory()->for($thread2)->for($user, 'author')->create();

    $this->actingAs($user)
        ->deleteJson("/api/v1/threads/{$thread1->id}/posts/{$post->id}")
        ->assertStatus(404);
});

test('PATCH /posts/{post}/comments/{comment} returns 404 when comment does not belong to post', function () {
    $user     = User::factory()->asStudent()->create(['status' => AccountStatus::Active]);
    $thread   = Thread::factory()->for($user, 'author')->create();
    $post1    = Post::factory()->for($thread)->for($user, 'author')->create();
    $post2    = Post::factory()->for($thread)->for($user, 'author')->create();
    $comment  = \App\Models\Comment::factory()->for($post2)->for($user, 'author')->create();

    $this->actingAs($user)
        ->patchJson("/api/v1/posts/{$post1->id}/comments/{$comment->id}", ['body' => 'tampered'])
        ->assertStatus(404);
});

test('DELETE /posts/{post}/comments/{comment} returns 404 when comment does not belong to post', function () {
    $user     = User::factory()->asAdmin()->create(['status' => AccountStatus::Active]);
    $thread   = Thread::factory()->for($user, 'author')->create();
    $post1    = Post::factory()->for($thread)->for($user, 'author')->create();
    $post2    = Post::factory()->for($thread)->for($user, 'author')->create();
    $comment  = \App\Models\Comment::factory()->for($post2)->for($user, 'author')->create();

    $this->actingAs($user)
        ->deleteJson("/api/v1/posts/{$post1->id}/comments/{$comment->id}")
        ->assertStatus(404);
});
