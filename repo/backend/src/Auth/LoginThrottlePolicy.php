<?php

declare(strict_types=1);

namespace CampusLearn\Auth;

final class LoginThrottlePolicy
{
    public function __construct(
        private readonly int $threshold = 5,
        private readonly int $windowMinutes = 15,
        private readonly int $lockDurationMinutes = 15,
    ) {
    }

    public function shouldLock(int $recentAttemptsInWindow): bool
    {
        return $recentAttemptsInWindow >= $this->threshold;
    }

    public function windowMinutes(): int
    {
        return $this->windowMinutes;
    }

    public function lockDurationMinutes(): int
    {
        return $this->lockDurationMinutes;
    }

    public function threshold(): int
    {
        return $this->threshold;
    }
}
