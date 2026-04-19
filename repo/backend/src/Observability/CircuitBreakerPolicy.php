<?php

declare(strict_types=1);

namespace CampusLearn\Observability;

use App\Enums\CircuitBreakerMode;

final class CircuitBreakerPolicy
{
    public function __construct(
        private readonly int $tripThresholdBps = 200,
        private readonly int $resetThresholdBps = 100,
        private readonly int $minimumSampleSize = 20,
    ) {
    }

    public function evaluate(
        CircuitBreakerMode $currentMode,
        ErrorRateWindow $window,
    ): CircuitBreakerMode {
        if ($window->totalRequests < $this->minimumSampleSize) {
            return $currentMode;
        }
        $rateBps = $window->errorRateBps();

        if ($currentMode === CircuitBreakerMode::ReadWrite) {
            return $rateBps > $this->tripThresholdBps
                ? CircuitBreakerMode::ReadOnly
                : CircuitBreakerMode::ReadWrite;
        }

        return $rateBps < $this->resetThresholdBps
            ? CircuitBreakerMode::ReadWrite
            : CircuitBreakerMode::ReadOnly;
    }

    public function tripThresholdBps(): int
    {
        return $this->tripThresholdBps;
    }
}
