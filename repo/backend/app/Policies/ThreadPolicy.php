<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\EnrollmentStatus;
use App\Enums\RoleName;
use App\Models\Enrollment;
use App\Models\Thread;
use App\Models\User;
use CampusLearn\Auth\ScopeContext;
use CampusLearn\Auth\ScopeResolutionService;
use CampusLearn\Moderation\EditWindowPolicy;

final class ThreadPolicy
{
    public function __construct(
        private readonly EditWindowPolicy $editWindowPolicy,
        private readonly ScopeResolutionService $scopeService,
    ) {
    }

    public function viewAny(User $user): bool
    {
        if ($this->isAdmin($user->id)) {
            return true;
        }
        if ($this->scopeService->hasRole($user->id, RoleName::Registrar)) {
            return true;
        }
        if ($this->scopeService->hasRole($user->id, RoleName::Teacher)) {
            return true;
        }
        return $this->scopeService->hasRole($user->id, RoleName::Student);
    }

    public function view(User $user, Thread $thread): bool
    {
        if ($this->isAdmin($user->id)) {
            return true;
        }
        if ($this->scopeService->hasRole($user->id, RoleName::Registrar)) {
            return true;
        }
        if ($this->scopeService->canPerform(
            $user->id,
            RoleName::Teacher,
            ScopeContext::course($thread->course_id),
        )) {
            return true;
        }
        if ($thread->section_id !== null) {
            return Enrollment::where('user_id', $user->id)
                ->where('section_id', $thread->section_id)
                ->where('status', EnrollmentStatus::Enrolled)
                ->exists();
        }
        return Enrollment::where('user_id', $user->id)
            ->whereHas('section', fn ($q) => $q->where('course_id', $thread->course_id))
            ->where('status', EnrollmentStatus::Enrolled)
            ->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Thread $thread): bool
    {
        if ($thread->author_id === $user->id) {
            return $this->editWindowPolicy->canAuthorEdit(
                $thread->created_at->toDateTimeImmutable(),
                now()->toDateTimeImmutable(),
            );
        }
        return $this->moderate($user, $thread);
    }

    public function moderate(User $user, Thread $thread): bool
    {
        if ($this->isAdmin($user->id)) {
            return true;
        }
        return $this->scopeService->canPerform(
            $user->id,
            RoleName::Teacher,
            ScopeContext::course($thread->course_id),
        );
    }

    public function delete(User $user, Thread $thread): bool
    {
        return $this->moderate($user, $thread);
    }

    private function isAdmin(int $userId): bool
    {
        return $this->scopeService->canPerform($userId, RoleName::Administrator, ScopeContext::global());
    }
}
