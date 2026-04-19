<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\AlertThresholdEvaluationJob;
use Illuminate\Console\Command;

final class HealthEvaluateCircuitCommand extends Command
{
    protected $signature = 'campuslearn:health:evaluate-circuit';
    protected $description = 'Evaluate circuit breaker thresholds and update system mode if needed';

    public function handle(): int
    {
        AlertThresholdEvaluationJob::dispatchSync();
        $this->info('Circuit evaluation complete.');
        return self::SUCCESS;
    }
}
