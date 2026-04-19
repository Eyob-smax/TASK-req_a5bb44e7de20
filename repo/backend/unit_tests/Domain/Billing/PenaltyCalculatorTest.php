<?php

declare(strict_types=1);

namespace Tests\Domain\Billing;

use CampusLearn\Billing\Money;
use CampusLearn\Billing\PenaltyCalculator;
use PHPUnit\Framework\TestCase;

final class PenaltyCalculatorTest extends TestCase
{
    public function testBelowGraceReturnsZero(): void
    {
        $calc = new PenaltyCalculator(10, 500);
        $this->assertSame(0, $calc->compute(Money::ofCents(10000), 9)->cents);
        $this->assertSame(0, $calc->compute(Money::ofCents(10000), 0)->cents);
    }

    public function testAtGraceDayAppliesPenalty(): void
    {
        $calc = new PenaltyCalculator(10, 500);
        // 5% of 10000 cents = 500
        $this->assertSame(500, $calc->compute(Money::ofCents(10000), 10)->cents);
    }

    public function testHalfUpRounding(): void
    {
        $calc = new PenaltyCalculator(10, 500);
        // 5% of 199 cents = 9.95 → 10
        $this->assertSame(10, $calc->compute(Money::ofCents(199), 15)->cents);
    }

    public function testZeroOutstandingZeroPenalty(): void
    {
        $calc = new PenaltyCalculator();
        $this->assertSame(0, $calc->compute(Money::zero(), 30)->cents);
    }

    public function testNegativeDaysRejected(): void
    {
        $calc = new PenaltyCalculator();
        $this->expectException(\InvalidArgumentException::class);
        $calc->compute(Money::ofCents(1000), -1);
    }
}
