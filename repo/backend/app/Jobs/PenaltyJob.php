<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Bill;
use App\Services\BillingService;
use DateTimeImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class PenaltyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function handle(BillingService $billingService): void
    {
        $today = new DateTimeImmutable(now()->toDateString());

        Bill::pastDue()->each(function (Bill $bill) use ($billingService, $today): void {
            try {
                $billingService->applyPenalty($bill, $today);
            } catch (Throwable) {
                // Continue — one failed bill should not abort the batch
            }
        });
    }
}
