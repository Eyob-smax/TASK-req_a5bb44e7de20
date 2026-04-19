<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ContentState;
use App\Enums\PostState;
use App\Enums\ThreadType;
use App\Models\Comment;
use App\Models\Mention;
use App\Models\Post;
use App\Models\SensitiveWordRule as SensitiveWordRuleModel;
use App\Models\Thread;
use App\Models\User;
use App\Support\AuditLogger;
use CampusLearn\Moderation\EditWindowPolicy;
use CampusLearn\Moderation\MentionParser;
use CampusLearn\Moderation\SensitiveWordFilter;
use CampusLearn\Moderation\SensitiveWordRule as DomainRule;
use CampusLearn\Support\Exceptions\EditWindowExpired;
use CampusLearn\Support\Exceptions\SensitiveWordMatched;
use Illuminate\Support\Facades\DB;

final class ContentSubmissionService
{
    public function __construct(
        private readonly SensitiveWordFilter $filter,
        private readonly MentionParser $mentionParser,
        private readonly EditWindowPolicy $editWindowPolicy,
        private readonly AuditLogger $audit,
        private readonly NotificationOrchestrator $notifier,
    ) {
    }

    /** @param array<string, mixed> $data */
    public function createThread(User $author, array $data): Thread
    {
        $this->rejectOnSensitiveWords(($data['title'] ?? '') . "\n" . ($data['body'] ?? ''));

        return DB::transaction(function () use ($author, $data): Thread {
            $sectionId = isset($data['section_id']) ? (int) $data['section_id'] : null;
            $courseId  = isset($data['course_id'])
                ? (int) $data['course_id']
                : ($sectionId !== null ? \App\Models\Section::findOrFail($sectionId)->course_id : 0);

            $thread = Thread::create([
                'course_id'   => $courseId,
                'section_id'  => $sectionId,
                'author_id'   => $author->id,
                'thread_type' => ThreadType::from($data['type'] ?? $data['thread_type'] ?? ThreadType::Discussion->value),
                'qa_enabled'  => (bool) ($data['qa_enabled'] ?? false),
                'title'       => (string) $data['title'],
                'body'        => (string) $data['body'],
                'state'       => ContentState::Visible,
            ]);

            $this->audit->record($author->id, 'thread.created', 'thread', $thread->id, [
                'course_id'   => $thread->course_id,
                'thread_type' => $thread->thread_type?->value,
            ]);

            $mentioned = $this->processMentions('thread', $thread->id, $thread->body);
            if ($mentioned !== []) {
                $this->notifier->notify('discussion.mention', $mentioned, [
                    'actor' => $author->name,
                    'title' => $thread->title,
                ]);
            }

            if ($thread->thread_type === ThreadType::Announcement) {
                $this->notifier->notify('announcement.posted', $this->courseAudience($thread->course_id, $author->id), [
                    'actor'       => $author->name,
                    'title'       => $thread->title,
                    'course_code' => $thread->course?->code ?? (string) $thread->course_id,
                ]);
            }

            return $thread->fresh();
        });
    }

    /** @param array<string, mixed> $data */
    public function updateThread(User $actor, Thread $thread, array $data, bool $asModerator = false): Thread
    {
        $this->rejectOnSensitiveWords(
            ($data['title'] ?? $thread->title) . "\n" . ($data['body'] ?? $thread->body),
        );

        return DB::transaction(function () use ($actor, $thread, $data, $asModerator): Thread {
            if (! $asModerator) {
                $this->requireEditWindow($thread->created_at);
            }
            if (array_key_exists('title', $data)) {
                $thread->title = (string) $data['title'];
            }
            if (array_key_exists('body', $data)) {
                $thread->body = (string) $data['body'];
            }
            if (array_key_exists('qa_enabled', $data)) {
                $thread->qa_enabled = (bool) $data['qa_enabled'];
            }
            $thread->edited_at = now();
            $thread->save();

            $this->audit->record($actor->id, $asModerator ? 'thread.moderated_edit' : 'thread.updated', 'thread', $thread->id, [
                'as_moderator' => $asModerator,
            ]);

            Mention::where('source_type', 'thread')->where('source_id', $thread->id)->delete();
            $this->processMentions('thread', $thread->id, $thread->body);

            return $thread->fresh();
        });
    }

    /** @param array<string, mixed> $data */
    public function createPost(User $author, Thread $thread, array $data): Post
    {
        $this->rejectOnSensitiveWords((string) ($data['body'] ?? ''));

        return DB::transaction(function () use ($author, $thread, $data): Post {
            $post = Post::create([
                'thread_id'      => $thread->id,
                'author_id'      => $author->id,
                'parent_post_id' => isset($data['parent_post_id']) ? (int) $data['parent_post_id'] : null,
                'body'           => (string) $data['body'],
                'state'          => PostState::Visible,
            ]);

            $this->audit->record($author->id, 'post.created', 'post', $post->id, [
                'thread_id' => $thread->id,
                'parent'    => $post->parent_post_id,
            ]);

            $mentioned = $this->processMentions('post', $post->id, $post->body);
            if ($mentioned !== []) {
                $this->notifier->notify('discussion.mention', $mentioned, [
                    'actor' => $author->name,
                    'title' => $thread->title,
                ]);
            }

            return $post->fresh();
        });
    }

    /** @param array<string, mixed> $data */
    public function updatePost(User $actor, Post $post, array $data, bool $asModerator = false): Post
    {
        $this->rejectOnSensitiveWords((string) ($data['body'] ?? $post->body));

        return DB::transaction(function () use ($actor, $post, $data, $asModerator): Post {
            if (! $asModerator) {
                $this->requireEditWindow($post->created_at);
            }
            if (array_key_exists('body', $data)) {
                $post->body = (string) $data['body'];
            }
            $post->edited_at = now();
            $post->save();

            $this->audit->record($actor->id, $asModerator ? 'post.moderated_edit' : 'post.updated', 'post', $post->id, [
                'as_moderator' => $asModerator,
            ]);

            Mention::where('source_type', 'post')->where('source_id', $post->id)->delete();
            $this->processMentions('post', $post->id, $post->body);

            return $post->fresh();
        });
    }

    /** @param array<string, mixed> $data */
    public function createComment(User $author, Post $post, array $data): Comment
    {
        $this->rejectOnSensitiveWords((string) ($data['body'] ?? ''));

        return DB::transaction(function () use ($author, $post, $data): Comment {
            $comment = Comment::create([
                'post_id'   => $post->id,
                'author_id' => $author->id,
                'body'      => (string) $data['body'],
                'state'     => PostState::Visible,
            ]);

            $this->audit->record($author->id, 'comment.created', 'comment', $comment->id, [
                'post_id' => $post->id,
            ]);

            $mentioned = $this->processMentions('comment', $comment->id, $comment->body);
            if ($mentioned !== []) {
                $this->notifier->notify('discussion.mention', $mentioned, [
                    'actor' => $author->name,
                    'title' => $post->thread?->title ?? '',
                ]);
            }

            return $comment->fresh();
        });
    }

    /** @param array<string, mixed> $data */
    public function updateComment(User $actor, Comment $comment, array $data, bool $asModerator = false): Comment
    {
        $this->rejectOnSensitiveWords((string) ($data['body'] ?? $comment->body));

        return DB::transaction(function () use ($actor, $comment, $data, $asModerator): Comment {
            if (! $asModerator) {
                $this->requireEditWindow($comment->created_at);
            }
            if (array_key_exists('body', $data)) {
                $comment->body = (string) $data['body'];
            }
            $comment->edited_at = now();
            $comment->save();

            $this->audit->record($actor->id, $asModerator ? 'comment.moderated_edit' : 'comment.updated', 'comment', $comment->id, [
                'as_moderator' => $asModerator,
            ]);

            Mention::where('source_type', 'comment')->where('source_id', $comment->id)->delete();
            $this->processMentions('comment', $comment->id, $comment->body);

            return $comment->fresh();
        });
    }

    public function deleteComment(User $actor, Comment $comment): void
    {
        DB::transaction(function () use ($actor, $comment): void {
            Mention::where('source_type', 'comment')->where('source_id', $comment->id)->delete();
            $this->audit->record($actor->id, 'comment.deleted', 'comment', $comment->id, []);
            $comment->delete();
        });
    }

    public function deletePost(User $actor, Post $post): void
    {
        DB::transaction(function () use ($actor, $post): void {
            Mention::where('source_type', 'post')->where('source_id', $post->id)->delete();
            $this->audit->record($actor->id, 'post.deleted', 'post', $post->id, []);
            $post->delete();
        });
    }

    private function rejectOnSensitiveWords(string $body): void
    {
        $rules = SensitiveWordRuleModel::where('is_active', true)->get();
        $domainRules = $rules->map(fn (SensitiveWordRuleModel $r) => new DomainRule(
            pattern:   $r->pattern,
            matchType: $r->match_type?->value ?? 'substring',
            isActive:  (bool) $r->is_active,
        ))->all();
        $result = $this->filter->inspect($body, $domainRules);
        if ($result->isBlocked()) {
            throw new SensitiveWordMatched($result->matches);
        }
    }

    private function requireEditWindow(\DateTimeInterface $createdAt): void
    {
        $allowed = $this->editWindowPolicy->canAuthorEdit(
            \DateTimeImmutable::createFromInterface($createdAt),
            now()->toDateTimeImmutable(),
        );
        if (! $allowed) {
            throw new EditWindowExpired((int) config('campuslearn.moderation.edit_window_minutes', 15));
        }
    }

    /**
     * @return int[] user ids mentioned
     */
    private function processMentions(string $sourceType, int $sourceId, string $body): array
    {
        $resolver = function (array $handles): array {
            if ($handles === []) {
                return [];
            }
            $rows = User::query()
                ->where(function ($q) use ($handles): void {
                    foreach ($handles as $handle) {
                        $q->orWhere('email', 'like', $handle . '@%');
                    }
                })
                ->get(['id', 'email']);

            $map = [];
            foreach ($rows as $user) {
                $local = mb_strtolower(explode('@', (string) $user->email)[0] ?? '');
                if ($local !== '' && in_array($local, $handles, true) && ! isset($map[$local])) {
                    $map[$local] = (int) $user->id;
                }
            }
            return $map;
        };

        $parsed = $this->mentionParser->parse($body, $resolver);
        $userIds = $parsed['user_ids'] ?? [];
        foreach ($userIds as $uid) {
            Mention::create([
                'mentioned_user_id' => $uid,
                'source_type'       => $sourceType,
                'source_id'         => $sourceId,
                'created_at'        => now(),
            ]);
        }
        return $userIds;
    }

    /**
     * @return int[] user ids subscribed to announcements for the given course (exclusive of the actor).
     */
    private function courseAudience(int $courseId, int $actorId): array
    {
        return \App\Models\Enrollment::query()
            ->whereHas('section', fn ($q) => $q->where('course_id', $courseId))
            ->where('status', \App\Enums\EnrollmentStatus::Enrolled)
            ->pluck('user_id')
            ->map(static fn ($v) => (int) $v)
            ->reject(static fn (int $id) => $id === $actorId)
            ->values()
            ->all();
    }
}
