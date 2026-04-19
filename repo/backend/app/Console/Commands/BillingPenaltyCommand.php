<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\PenaltyJob;
use Illuminate\Console\Command;

final class BillingPenaltyCommand extends Command
{
    protected $signature = 'campuslearn:billing:penalty';
    protected $description = 'Apply penalty charges to past-due bills (idempotent per bill per day)';

    public function handle(): int
    {
        PenaltyJob::dispatchSync();
        $this->info('Penalty billing run complete.');
        return self::SUCCESS;
    }
}
