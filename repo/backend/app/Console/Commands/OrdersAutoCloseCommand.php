<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\OrderAutoCloseJob;
use Illuminate\Console\Command;

final class OrdersAutoCloseCommand extends Command
{
    protected $signature = 'campuslearn:orders:auto-close';
    protected $description = 'Cancel orders that have been in pending_payment past their auto_close_at deadline';

    public function handle(): int
    {
        OrderAutoCloseJob::dispatchSync();
        $this->info('Order auto-close run complete.');
        return self::SUCCESS;
    }
}
