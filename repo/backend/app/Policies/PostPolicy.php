<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\Post;
use App\Models\User;
use CampusLearn\Auth\ScopeContext;
use CampusLearn\Auth\ScopeResolutionService;
use CampusLearn\Moderation\EditWindowPolicy;

final class PostPolicy
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

    public function update(User $user, Post $post): bool
    {
        if ($post->author_id !== $user->id) {
            return false;
        }
        return $this->editWindowPolicy->canAuthorEdit(
            $post->created_at->toDateTimeImmutable(),
            now()->toDateTimeImmutable(),
        );
    }

    public function moderate(User $user, Post $post): bool
    {
        return $this->scopeService->canPerform(
            $user->id,
            RoleName::Administrator,
            ScopeContext::global(),
        ) || $this->scopeService->canPerform(
            $user->id,
            RoleName::Registrar,
            ScopeContext::global(),
        ) || $this->scopeService->canPerform(
            $user->id,
            RoleName::Teacher,
            ScopeContext::course($post->thread->course_id),
        );
    }

    public function delete(User $user, Post $post): bool
    {
        return $this->moderate($user, $post);
    }
}
