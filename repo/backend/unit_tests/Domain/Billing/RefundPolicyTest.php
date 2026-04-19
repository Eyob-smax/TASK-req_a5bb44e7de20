<?php

declare(strict_types=1);

namespace Tests\Domain\Billing;

use CampusLearn\Billing\Money;
use CampusLearn\Billing\RefundPolicy;
use PHPUnit\Framework\TestCase;

final class RefundPolicyTest extends TestCase
{
    public function testAllowedWhenWithinCeiling(): void
    {
        $policy = new RefundPolicy();
        $decision = $policy->evaluate(
            billPaid: Money::ofCents(10000),
            billRefunded: Money::ofCents(2000),
            requested: Money::ofCents(3000),
            reasonCode: 'overpayment',
        );
        $this->assertTrue($decision->allowed);
    }

    public function testRejectedWhenExceedsRefundable(): void
    {
        $policy = new RefundPolicy();
        $decision = $policy->evaluate(
            billPaid: Money::ofCents(10000),
            billRefunded: Money::ofCents(8000),
            requested: Money::ofCents(3000),
            reasonCode: 'overpayment',
        );
        $this->assertFalse($decision->allowed);
        $this->assertSame('EXCEEDS_REFUNDABLE_BALANCE', $decision->rejectionCode);
    }

    public function testMissingReasonCodeRejected(): void
    {
        $policy = new RefundPolicy();
        $decision = $policy->evaluate(
            billPaid: Money::ofCents(1000),
            billRefunded: Money::zero(),
            requested: Money::ofCents(500),
            reasonCode: null,
        );
        $this->assertFalse($decision->allowed);
        $this->assertSame('REASON_CODE_REQUIRED', $decision->rejectionCode);
    }

    public function testZeroAmountRejected(): void
    {
        $policy = new RefundPolicy();
        $decision = $policy->evaluate(
            billPaid: Money::ofCents(5000),
            billRefunded: Money::zero(),
            requested: Money::zero(),
            reasonCode: 'overpayment',
        );
        $this->assertFalse($decision->allowed);
        $this->assertSame('INVALID_AMOUNT', $decision->rejectionCode);
    }

    public function testFullyRefundedBillHasNoBalance(): void
    {
        $policy = new RefundPolicy();
        $decision = $policy->evaluate(
            billPaid: Money::ofCents(5000),
            billRefunded: Money::ofCents(5000),
            requested: Money::ofCents(100),
            reasonCode: 'overpayment',
        );
        $this->assertFalse($decision->allowed);
        $this->assertSame('NO_REFUNDABLE_BALANCE', $decision->rejectionCode);
    }
}
