<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class OrderAutoCloseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function handle(OrderService $orderService): void
    {
        Order::autoCloseDue()->each(function (Order $order) use ($orderService): void {
            try {
                $orderService->autoClose($order);
            } catch (Throwable) {
                // Log but continue — don't let one bad order abort the batch
            }
        });
    }
}
