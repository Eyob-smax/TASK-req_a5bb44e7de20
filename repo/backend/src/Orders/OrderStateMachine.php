<?php

declare(strict_types=1);

namespace CampusLearn\Orders;

use App\Enums\OrderStatus;
use App\Enums\OrderTimelineEvent;
use CampusLearn\Support\Exceptions\InvalidStateTransition;

final class OrderStateMachine
{
    /**
     * Legal (from, event) → to mapping. Missing combinations are rejected.
     *
     * @var array<string, array<string, OrderStatus>>
     */
    private array $transitions;

    public function __construct()
    {
        $this->transitions = [
            OrderStatus::PendingPayment->value => [
                OrderTimelineEvent::PaymentInitiated->value => OrderStatus::PendingPayment,
                OrderTimelineEvent::Paid->value => OrderStatus::Paid,
                OrderTimelineEvent::Canceled->value => OrderStatus::Canceled,
                OrderTimelineEvent::AutoClosed->value => OrderStatus::Canceled,
            ],
            OrderStatus::Paid->value => [
                OrderTimelineEvent::Refunded->value => OrderStatus::Refunded,
                OrderTimelineEvent::Redeemed->value => OrderStatus::Redeemed,
            ],
            OrderStatus::Refunded->value => [],
            OrderStatus::Canceled->value => [],
            OrderStatus::Redeemed->value => [
                OrderTimelineEvent::Refunded->value => OrderStatus::Refunded,
            ],
        ];
    }

    public function transition(OrderStatus $from, OrderTimelineEvent $event): OrderStatus
    {
        $row = $this->transitions[$from->value] ?? [];
        if (! array_key_exists($event->value, $row)) {
            throw new InvalidStateTransition('order', $from->value, $event->value);
        }
        return $row[$event->value];
    }

    public function canTransition(OrderStatus $from, OrderTimelineEvent $event): bool
    {
        return array_key_exists($event->value, $this->transitions[$from->value] ?? []);
    }
}
