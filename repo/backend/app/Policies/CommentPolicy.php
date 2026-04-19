<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\Comment;
use App\Models\User;
use CampusLearn\Auth\ScopeContext;
use CampusLearn\Auth\ScopeResolutionService;
use CampusLearn\Moderation\EditWindowPolicy;

final class CommentPolicy
{
    public function __construct(
        private readonly EditWindowPolicy $editWindowPolicy,
        private readonly ScopeResolutionService $scopeService,
    ) {
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Comment $comment): bool
    {
        if ($comment->author_id !== $user->id) {
            return false;
        }
        return $this->editWindowPolicy->canAuthorEdit(
            $comment->created_at->toDateTimeImmutable(),
            now()->toDateTimeImmutable(),
        );
    }

    public function moderate(User $user, Comment $comment): bool
    {
        if ($this->scopeService->canPerform($user->id, RoleName::Administrator, ScopeContext::global())) {
            return true;
        }
        $courseId = $comment->post?->thread?->course_id;
        if ($courseId === null) {
            return false;
        }
        return $this->scopeService->canPerform($user->id, RoleName::Teacher, ScopeContext::course($courseId));
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $this->moderate($user, $comment);
    }
}
