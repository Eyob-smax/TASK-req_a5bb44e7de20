<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\BackupMetadataJob;
use Illuminate\Console\Command;

final class BackupsRecordMetadataCommand extends Command
{
    protected $signature = 'campuslearn:backups:record-metadata';
    protected $description = 'Encrypt the backup source file, record metadata, and prune expired entries';

    public function handle(): int
    {
        BackupMetadataJob::dispatchSync();
        $this->info('Backup metadata job complete.');
        return self::SUCCESS;
    }
}
