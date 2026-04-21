<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\EnrollmentStatus;
use App\Enums\RoleName;
use App\Models\Enrollment;
use App\Models\GradeItem;
use App\Models\Section;
use App\Models\User;
use CampusLearn\Auth\ScopeContext;
use CampusLearn\Auth\ScopeResolutionService;

final class GradeItemPolicy
{
    public function __construct(
        private readonly ScopeResolutionService $scopeService,
    ) {
    }

    public function viewAny(User $user, ?Section $section = null): bool
    {
        if ($section === null) {
            return false;
        }

        if ($this->scopeService->canPerform($user->id, RoleName::Administrator, ScopeContext::global())) {
            return true;
        }
        if ($this->scopeService->canPerform($user->id, RoleName::Registrar, ScopeContext::global())) {
            return true;
        }

        $ancestry = [
            'term'    => $section->term_id,
            'course'  => $section->course_id,
            'section' => $section->id,
        ];

        if ($this->scopeService->canPerform(
            $user->id,
            RoleName::Registrar,
            ScopeContext::term($section->term_id),
            $ancestry,
        )) {
            return true;
        }

        if ($this->scopeService->canPerform(
            $user->id,
            RoleName::Teacher,
            ScopeContext::section($section->id),
            $ancestry,
        )) {
            return true;
        }

        if ($this->scopeService->canPerform(
            $user->id,
            RoleName::Teacher,
            ScopeContext::course($section->course_id),
            $ancestry,
        )) {
            return true;
        }

        return Enrollment::where('user_id', $user->id)
            ->where('section_id', $section->id)
            ->where('status', EnrollmentStatus::Enrolled->value)
            ->exists();
    }

    public function create(User $user, ?Section $section = null): bool
    {
        if ($section === null) {
            return false;
        }
        return $this->isTeacherForSection($user->id, $section->id);
    }

    public function update(User $user, GradeItem $gradeItem): bool
    {
        return $this->isTeacherForSection($user->id, $gradeItem->section_id);
    }

    public function publish(User $user, GradeItem $gradeItem): bool
    {
        if ($this->scopeService->canPerform($user->id, RoleName::Administrator, ScopeContext::global())) {
            return true;
        }

        return $user->roleAssignments()
            ->whereNull('revoked_at')
            ->where('scope_type', 'section')
            ->where('scope_id', $gradeItem->section_id)
            ->whereHas('role', fn ($q) => $q->where('name', RoleName::Teacher->value))
            ->exists();
    }

    public function viewScores(User $user, GradeItem $gradeItem): bool
    {
        // Teachers see all scores; students see only their own (handled at controller level)
        return $this->isTeacherForSection($user->id, $gradeItem->section_id)
            || $this->scopeService->canPerform($user->id, RoleName::Administrator, ScopeContext::global());
    }

    private function isTeacherForSection(int $userId, int $sectionId): bool
    {
        return $this->scopeService->canPerform(
            $userId,
            RoleName::Teacher,
            ScopeContext::section($sectionId),
        ) || $this->scopeService->canPerform(
            $userId,
            RoleName::Administrator,
            ScopeContext::global(),
        );
    }
}
