<?php

declare(strict_types=1);

namespace CampusLearn\Observability;

use InvalidArgumentException;

final class ErrorRateWindow
{
    public function __construct(
        public readonly int $totalRequests,
        public readonly int $errorRequests,
        public readonly int $windowSeconds,
    ) {
        if ($totalRequests < 0 || $errorRequests < 0 || $windowSeconds <= 0) {
            throw new InvalidArgumentException('ErrorRateWindow inputs must be non-negative and window > 0.');
        }
        if ($errorRequests > $totalRequests) {
            throw new InvalidArgumentException('Error count cannot exceed total requests.');
        }
    }

    public function errorRateBps(): int
    {
        if ($this->totalRequests === 0) {
            return 0;
        }
        return (int) floor(($this->errorRequests * 10000) / $this->totalRequests);
    }
}
