<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\OrderTimelineEvent as OrderTimelineEventEnum;
use App\Models\CatalogItem;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\OrderTimelineEvent;
use App\Models\User;
use App\Support\AuditLogger;
use CampusLearn\Billing\Money;
use CampusLearn\Billing\TaxRuleCalculator;
use CampusLearn\Orders\OrderStateMachine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class OrderService
{
    public function __construct(
        private readonly TaxRuleCalculator $taxCalculator,
        private readonly OrderStateMachine $stateMachine,
        private readonly AuditLogger $audit,
    ) {
    }

    /**
     * @param array<int, array{catalog_item_id: int, quantity: int}> $lines
     */
    public function create(User $user, array $lines): Order
    {
        return DB::transaction(function () use ($user, $lines): Order {
            $subtotal = Money::zero();
            $tax      = Money::zero();

            $resolvedLines = [];
            foreach ($lines as $line) {
                /** @var CatalogItem $item */
                $item = CatalogItem::with(['feeCategory.taxRules'])->findOrFail($line['catalog_item_id']);
                $qty  = max(1, (int) $line['quantity']);

                $unitPrice = Money::ofCents($item->unit_price_cents);
                $lineTotal = $unitPrice->multiplyInt($qty);

                $snapshot  = null;
                $lineTax   = Money::zero();

                if ($item->feeCategory && $item->feeCategory->is_taxable) {
                    $activeTaxRule = $item->feeCategory->taxRules
                        ->first(fn ($r) => $r->effective_from <= now()->toDateString()
                            && ($r->effective_to === null || $r->effective_to >= now()->toDateString()));

                    if ($activeTaxRule) {
                        $lineTax = $this->taxCalculator->computeTax($lineTotal, $activeTaxRule->rate_bps);
                        $snapshot = [
                            'rate_bps'       => $activeTaxRule->rate_bps,
                            'effective_from' => $activeTaxRule->effective_from->toDateString(),
                            'effective_to'   => $activeTaxRule->effective_to?->toDateString(),
                        ];
                    }
                }

                $subtotal = $subtotal->add($lineTotal);
                $tax      = $tax->add($lineTax);

                $resolvedLines[] = [
                    'catalog_item_id'   => $item->id,
                    'quantity'          => $qty,
                    'unit_price_cents'  => $unitPrice->cents,
                    'tax_rule_snapshot' => $snapshot,
                    'line_total_cents'  => $lineTotal->cents,
                ];
            }

            $total = $subtotal->add($tax);

            $order = Order::create([
                'user_id'         => $user->id,
                'status'          => OrderStatus::PendingPayment,
                'subtotal_cents'  => $subtotal->cents,
                'tax_cents'       => $tax->cents,
                'total_cents'     => $total->cents,
                'auto_close_at'   => now()->addMinutes(config('campuslearn.orders.auto_close_minutes', 30)),
            ]);

            foreach ($resolvedLines as $lineData) {
                OrderLine::create(['order_id' => $order->id] + $lineData);
            }

            $this->recordTimeline($order->id, OrderTimelineEventEnum::Created, $user->id);

            return $order->load('lines');
        });
    }

    public function list(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return Order::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function cancel(User $actor, Order $order): Order
    {
        return DB::transaction(function () use ($actor, $order): Order {
            $newStatus = $this->stateMachine->transition($order->status, OrderTimelineEventEnum::Canceled);

            $order->update([
                'status'      => $newStatus,
                'canceled_at' => now(),
            ]);

            $this->recordTimeline($order->id, OrderTimelineEventEnum::Canceled, $actor->id);
            $this->audit->record($actor->id, 'order.canceled', 'order', $order->id, []);

            return $order->fresh();
        });
    }

    public function autoClose(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $newStatus = $this->stateMachine->transition($order->status, OrderTimelineEventEnum::AutoClosed);

            $order->update([
                'status'      => $newStatus,
                'canceled_at' => now(),
            ]);

            $this->recordTimeline($order->id, OrderTimelineEventEnum::AutoClosed, null);
            $this->audit->record(null, 'order.auto_closed', 'order', $order->id, []);
        });
    }

    public function timeline(Order $order): \Illuminate\Database\Eloquent\Collection
    {
        return OrderTimelineEvent::where('order_id', $order->id)
            ->orderBy('created_at')
            ->get();
    }

    private function recordTimeline(int $orderId, OrderTimelineEventEnum $event, ?int $actorId): void
    {
        OrderTimelineEvent::create([
            'order_id'      => $orderId,
            'event'         => $event,
            'actor_user_id' => $actorId,
            'payload'       => [],
            'created_at'    => now(),
        ]);
    }
}
