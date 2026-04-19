<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\RecurringBillingJob;
use Illuminate\Console\Command;

final class BillingRecurringCommand extends Command
{
    protected $signature = 'campuslearn:billing:recurring';
    protected $description = 'Generate recurring monthly bills for all active schedules due today';

    public function handle(): int
    {
        RecurringBillingJob::dispatchSync();
        $this->info('Recurring billing run complete.');
        return self::SUCCESS;
    }
}
