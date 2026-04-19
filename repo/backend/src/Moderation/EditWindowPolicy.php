<?php

declare(strict_types=1);

namespace CampusLearn\Moderation;

use DateTimeImmutable;

final class EditWindowPolicy
{
    public function __construct(
        private readonly int $windowMinutes = 15,
    ) {
    }

    public function canAuthorEdit(DateTimeImmutable $createdAt, DateTimeImmutable $now): bool
    {
        $elapsed = $now->getTimestamp() - $createdAt->getTimestamp();
        if ($elapsed < 0) {
            return false;
        }
        return $elapsed < $this->windowMinutes * 60;
    }

    public function windowMinutes(): int
    {
        return $this->windowMinutes;
    }
}
