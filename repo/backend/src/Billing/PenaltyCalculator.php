<?php

declare(strict_types=1);

namespace CampusLearn\Billing;

use InvalidArgumentException;

final class PenaltyCalculator
{
    public function __construct(
        private readonly int $graceDays = 10,
        private readonly int $rateBps = 500,
    ) {
        if ($graceDays < 0) {
            throw new InvalidArgumentException('Grace days cannot be negative.');
        }
        if ($rateBps < 0) {
            throw new InvalidArgumentException('Penalty rate bps cannot be negative.');
        }
    }

    public function compute(Money $outstanding, int $daysPastDue): Money
    {
        if ($daysPastDue < 0) {
            throw new InvalidArgumentException('Days past due cannot be negative.');
        }
        if ($outstanding->isNegative()) {
            throw new InvalidArgumentException('Outstanding amount cannot be negative.');
        }
        if ($daysPastDue < $this->graceDays) {
            return Money::zero();
        }
        if ($outstanding->isZero()) {
            return Money::zero();
        }
        return $outstanding->multiplyBps($this->rateBps);
    }
}
