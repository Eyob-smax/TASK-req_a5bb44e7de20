<?php

declare(strict_types=1);

namespace CampusLearn\Moderation;

final class SensitiveWordRule
{
    public function __construct(
        public readonly string $pattern,
        public readonly string $matchType,
        public readonly bool $isActive = true,
    ) {
    }
}
