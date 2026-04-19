<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\BillSchedule;
use App\Services\BillingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class RecurringBillingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function handle(BillingService $billingService): void
    {
        $dayOfMonth = (int) config('campuslearn.billing.recurring_day_of_month', 1);
        if ((int) now()->format('j') !== $dayOfMonth) {
            return;
        }

        BillSchedule::activeSchedulesDueToday()
            ->with(['user', 'feeCategory.taxRules'])
            ->each(function (BillSchedule $schedule) use ($billingService): void {
                try {
                    $billingService->generateRecurring($schedule->user, $schedule);
                } catch (Throwable) {
                    // Continue — one failed schedule should not abort the batch
                }
            });
    }
}
