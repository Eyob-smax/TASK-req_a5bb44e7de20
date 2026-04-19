<?php

declare(strict_types=1);

namespace Tests\Domain\Billing;

use CampusLearn\Billing\Money;
use CampusLearn\Billing\TaxRuleCalculator;
use PHPUnit\Framework\TestCase;

final class TaxRuleCalculatorTest extends TestCase
{
    public function testZeroRateReturnsZero(): void
    {
        $calc = new TaxRuleCalculator();
        $this->assertSame(0, $calc->computeTax(Money::ofCents(5000), 0)->cents);
    }

    public function testStandardRateRoundsHalfUp(): void
    {
        $calc = new TaxRuleCalculator();
        // 725 bps of 1000 cents = 72.5 → 73
        $this->assertSame(73, $calc->computeTax(Money::ofCents(1000), 725)->cents);
    }

    public function testZeroBaseReturnsZero(): void
    {
        $calc = new TaxRuleCalculator();
        $this->assertSame(0, $calc->computeTax(Money::zero(), 725)->cents);
    }

    public function testNegativeRateRejected(): void
    {
        $calc = new TaxRuleCalculator();
        $this->expectException(\InvalidArgumentException::class);
        $calc->computeTax(Money::ofCents(100), -1);
    }

    public function testNegativeBaseRejected(): void
    {
        $calc = new TaxRuleCalculator();
        $this->expectException(\InvalidArgumentException::class);
        $calc->computeTax(Money::ofCents(-100), 500);
    }
}
