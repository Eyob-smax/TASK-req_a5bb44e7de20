<?php

declare(strict_types=1);

namespace CampusLearn\Moderation;

final class FilterResult
{
    /**
     * @param array<int, array{term: string, start: int, end: int}> $matches
     */
    public function __construct(
        public readonly array $matches,
    ) {
    }

    public function isBlocked(): bool
    {
        return $this->matches !== [];
    }

    public static function empty(): self
    {
        return new self([]);
    }
}
