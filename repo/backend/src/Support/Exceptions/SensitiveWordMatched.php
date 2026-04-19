<?php

declare(strict_types=1);

namespace CampusLearn\Support\Exceptions;

use DomainException;

final class SensitiveWordMatched extends DomainException
{
    /**
     * @param array<int, array{term: string, start: int, end: int}> $matches
     */
    public function __construct(public readonly array $matches)
    {
        parent::__construct('Content contains sensitive words that must be rewritten before publishing.');
    }
}
