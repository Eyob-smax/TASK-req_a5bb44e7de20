<?php

declare(strict_types=1);

namespace CampusLearn\Auth;

use App\Enums\RoleName;
use App\Enums\ScopeType;
use CampusLearn\Auth\Contracts\ScopeResolver;

final class ScopeResolutionService
{
    public function __construct(
        private readonly ScopeResolver $resolver,
    ) {
    }

    /**
     * Tests whether a user holds any role that satisfies the capability in the given context.
     *
     * Capability rules (least privilege — administrators override all others):
     *   - administrator @ global: true for any context.
     *   - registrar @ term: true for contexts matching the same term scope or narrower term-descended scopes.
     *   - teacher @ section: true for the same section or narrower grade_item scope under that section.
     *   - teacher @ course: true for any section/grade_item under that course (resolution of section→course
     *     is the caller's responsibility; we treat course grants as ancestors to section/grade_item only when
     *     the caller supplies a matching scope_id).
     *   - student: has no capability in this service; policies that require "student" role simply check role
     *     presence via `hasRole()`.
     *
     * @param array<string, int|null> $ancestry Optional chain providing parent scope ids
     *                                          (e.g. ['term' => 5, 'course' => 22, 'section' => 101]).
     */
    public function canPerform(
        int $userId,
        RoleName $requiredRole,
        ScopeContext $context,
        array $ancestry = [],
    ): bool {
        foreach ($this->resolver->activeGrantsFor($userId) as $grant) {
            if ($this->grantSatisfies($grant, $requiredRole, $context, $ancestry)) {
                return true;
            }
        }
        return false;
    }

    public function hasRole(int $userId, RoleName $role): bool
    {
        foreach ($this->resolver->activeGrantsFor($userId) as $grant) {
            if ($grant->role === $role) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the course and section IDs that a teacher user is explicitly granted.
     * If the teacher holds a global-scope grant, `global` is true and the id arrays are empty.
     *
     * @return array{global: bool, course_ids: int[], section_ids: int[]}
     */
    public function teacherScopeIds(int $userId): array
    {
        $courseIds  = [];
        $sectionIds = [];

        foreach ($this->resolver->activeGrantsFor($userId) as $grant) {
            if ($grant->role !== RoleName::Teacher) {
                continue;
            }
            if ($grant->scopeType === ScopeType::Global) {
                return ['global' => true, 'course_ids' => [], 'section_ids' => []];
            }
            if ($grant->scopeType === ScopeType::Course && $grant->scopeId !== null) {
                $courseIds[] = $grant->scopeId;
            }
            if ($grant->scopeType === ScopeType::Section && $grant->scopeId !== null) {
                $sectionIds[] = $grant->scopeId;
            }
        }

        return ['global' => false, 'course_ids' => $courseIds, 'section_ids' => $sectionIds];
    }

    private function grantSatisfies(
        Grant $grant,
        RoleName $requiredRole,
        ScopeContext $context,
        array $ancestry,
    ): bool {
        if ($grant->role === RoleName::Administrator && $grant->scopeType === ScopeType::Global) {
            return true;
        }
        if ($grant->role !== $requiredRole) {
            return false;
        }
        if ($grant->scopeType === $context->scopeType && $grant->scopeId === $context->scopeId) {
            return true;
        }
        // A broader grant covers narrower contexts if the ancestry chain matches the grant scope.
        $ancestorKey = match ($grant->scopeType) {
            ScopeType::Term => 'term',
            ScopeType::Course => 'course',
            ScopeType::Section => 'section',
            ScopeType::GradeItem => 'grade_item',
            ScopeType::Global => null,
        };
        if ($grant->scopeType === ScopeType::Global && $grant->role === $requiredRole) {
            return true;
        }
        if ($ancestorKey !== null && array_key_exists($ancestorKey, $ancestry)) {
            return $ancestry[$ancestorKey] === $grant->scopeId;
        }
        return false;
    }
}
