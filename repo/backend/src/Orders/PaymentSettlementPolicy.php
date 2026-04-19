<?php

declare(strict_types=1);

namespace CampusLearn\Orders;

use App\Enums\OrderStatus;
use CampusLearn\Billing\Money;

final class PaymentSettlementPolicy
{
    /**
     * Returns a machine-readable decision for settling a payment attempt.
     *
     * @return array{allowed: bool, code?: string, message?: string}
     */
    public function evaluate(
        OrderStatus $orderStatus,
        Money $orderTotal,
        Money $attemptAmount,
    ): array {
        if ($orderStatus !== OrderStatus::PendingPayment) {
            return [
                'allowed' => false,
                'code' => 'ORDER_NOT_PENDING',
                'message' => 'Order is not in pending_payment status.',
            ];
        }
        if ($attemptAmount->isZero() || $attemptAmount->isNegative()) {
            return [
                'allowed' => false,
                'code' => 'INVALID_AMOUNT',
                'message' => 'Payment amount must be positive.',
            ];
        }
        if (! $attemptAmount->equals($orderTotal)) {
            return [
                'allowed' => false,
                'code' => 'AMOUNT_MISMATCH',
                'message' => 'Payment amount must match the order total exactly.',
            ];
        }
        return ['allowed' => true];
    }
}
