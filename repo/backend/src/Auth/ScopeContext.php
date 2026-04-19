<?php

declare(strict_types=1);

namespace CampusLearn\Auth;

use App\Enums\ScopeType;

final class ScopeContext
{
    public function __construct(
        public readonly ScopeType $scopeType,
        public readonly ?int $scopeId,
    ) {
    }

    public static function global(): self
    {
        return new self(ScopeType::Global, null);
    }

    public static function term(int $termId): self
    {
        return new self(ScopeType::Term, $termId);
    }

    public static function course(int $courseId): self
    {
        return new self(ScopeType::Course, $courseId);
    }

    public static function section(int $sectionId): self
    {
        return new self(ScopeType::Section, $sectionId);
    }

    public static function gradeItem(int $gradeItemId): self
    {
        return new self(ScopeType::GradeItem, $gradeItemId);
    }
}
