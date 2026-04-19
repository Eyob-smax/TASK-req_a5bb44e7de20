<?php

declare(strict_types=1);

namespace Tests\Domain\Orders;

use App\Enums\OrderStatus;
use App\Enums\OrderTimelineEvent;
use CampusLearn\Orders\OrderStateMachine;
use CampusLearn\Support\Exceptions\InvalidStateTransition;
use PHPUnit\Framework\TestCase;

final class OrderStateMachineTest extends TestCase
{
    public function testPendingToPaid(): void
    {
        $fsm = new OrderStateMachine();
        $this->assertSame(
            OrderStatus::Paid,
            $fsm->transition(OrderStatus::PendingPayment, OrderTimelineEvent::Paid),
        );
    }

    public function testPendingToCanceled(): void
    {
        $fsm = new OrderStateMachine();
        $this->assertSame(
            OrderStatus::Canceled,
            $fsm->transition(OrderStatus::PendingPayment, OrderTimelineEvent::Canceled),
        );
    }

    public function testPendingToAutoClosedProducesCanceled(): void
    {
        $fsm = new OrderStateMachine();
        $this->assertSame(
            OrderStatus::Canceled,
            $fsm->transition(OrderStatus::PendingPayment, OrderTimelineEvent::AutoClosed),
        );
    }

    public function testPaidToRedeemedAndRefunded(): void
    {
        $fsm = new OrderStateMachine();
        $this->assertSame(OrderStatus::Redeemed, $fsm->transition(OrderStatus::Paid, OrderTimelineEvent::Redeemed));
        $this->assertSame(OrderStatus::Refunded, $fsm->transition(OrderStatus::Paid, OrderTimelineEvent::Refunded));
    }

    public function testCanceledIsTerminal(): void
    {
        $fsm = new OrderStateMachine();
        $this->expectException(InvalidStateTransition::class);
        $fsm->transition(OrderStatus::Canceled, OrderTimelineEvent::Paid);
    }

    public function testIllegalPendingToRedeemed(): void
    {
        $fsm = new OrderStateMachine();
        $this->expectException(InvalidStateTransition::class);
        $fsm->transition(OrderStatus::PendingPayment, OrderTimelineEvent::Redeemed);
    }
}
