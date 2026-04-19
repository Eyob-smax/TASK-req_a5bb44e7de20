<?php

declare(strict_types=1);

namespace CampusLearn\Billing;

final class RefundPolicy
{
    public function evaluate(
        Money $billPaid,
        Money $billRefunded,
        Money $requested,
        ?string $reasonCode,
    ): RefundDecision {
        if ($reasonCode === null || trim($reasonCode) === '') {
            return RefundDecision::rejected('REASON_CODE_REQUIRED', 'Refund reason code is required.');
        }
        if ($requested->isZero() || $requested->isNegative()) {
            return RefundDecision::rejected('INVALID_AMOUNT', 'Refund amount must be positive.');
        }
        $ceiling = $billPaid->subtract($billRefunded);
        if ($ceiling->isNegative() || $ceiling->isZero()) {
            return RefundDecision::rejected('NO_REFUNDABLE_BALANCE', 'No refundable balance remaining on this bill.');
        }
        if ($requested->cents > $ceiling->cents) {
            return RefundDecision::rejected(
                'EXCEEDS_REFUNDABLE_BALANCE',
                sprintf('Requested %s exceeds refundable balance %s.', $requested->format(), $ceiling->format()),
            );
        }
        return RefundDecision::allowed();
    }
}
