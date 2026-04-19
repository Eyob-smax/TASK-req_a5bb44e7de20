<?php

declare(strict_types=1);

namespace Tests\Domain\Billing;

use CampusLearn\Billing\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testAddAndSubtract(): void
    {
        $a = Money::ofCents(100);
        $b = Money::ofCents(250);
        $this->assertSame(350, $a->add($b)->cents);
        $this->assertSame(-150, $a->subtract($b)->cents);
    }

    public function testMultiplyBpsHalfUp(): void
    {
        // 1000 cents * 725 bps = 72.5 → rounds to 73
        $this->assertSame(73, Money::ofCents(1000)->multiplyBps(725)->cents);
        // 199 cents * 500 bps = 9.95 → rounds to 10
        $this->assertSame(10, Money::ofCents(199)->multiplyBps(500)->cents);
        // 200 cents * 500 bps = 10.00 → stays 10
        $this->assertSame(10, Money::ofCents(200)->multiplyBps(500)->cents);
    }

    public function testMultiplyIntAndZero(): void
    {
        $this->assertSame(0, Money::zero()->multiplyInt(5)->cents);
        $this->assertSame(1500, Money::ofCents(500)->multiplyInt(3)->cents);
    }

    public function testAllocateDistributesRemainder(): void
    {
        $parts = Money::ofCents(100)->allocate([1, 1, 1]);
        $this->assertSame([34, 33, 33], [$parts[0]->cents, $parts[1]->cents, $parts[2]->cents]);
    }

    public function testAllocateWeighted(): void
    {
        $parts = Money::ofCents(1000)->allocate([2, 3]);
        $this->assertSame(400, $parts[0]->cents);
        $this->assertSame(600, $parts[1]->cents);
    }

    public function testEqualityAndFormat(): void
    {
        $this->assertTrue(Money::ofCents(250)->equals(Money::ofCents(250)));
        $this->assertSame('12.34', Money::ofCents(1234)->format());
        $this->assertSame('-0.05', Money::ofCents(-5)->format());
    }

    public function testRoundHalfUpHelperDirection(): void
    {
        $this->assertSame(1, Money::roundHalfUpDiv(1, 2));   // 0.5 → 1
        $this->assertSame(2, Money::roundHalfUpDiv(3, 2));   // 1.5 → 2
        $this->assertSame(1, Money::roundHalfUpDiv(3, 4));   // 0.75 → 1
    }

    public function testNegativeBpsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Money::ofCents(100)->multiplyBps(-1);
    }
}
