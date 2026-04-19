<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\BackupStatus;
use App\Models\BackupJob;
use App\Services\EncryptionHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class BackupMetadataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(private readonly int $backupJobId) {}

    public function handle(EncryptionHelper $encryption): void
    {
        $sourcePath = config('campuslearn.backups.source_path');
        $targetDir  = config('campuslearn.backups.target_dir');
        $hexKey     = config('campuslearn.backups.encryption_key');

        $job = BackupJob::findOrFail($this->backupJobId);
        $job->update(['status' => BackupStatus::Running]);

        try {
            if (! is_string($sourcePath) || ! file_exists($sourcePath)) {
                throw new \RuntimeException("Backup source path not found: {$sourcePath}");
            }

            if (! is_string($targetDir)) {
                throw new \RuntimeException('Backup target directory not configured.');
            }

            if (! is_dir($targetDir)) {
                mkdir($targetDir, 0750, true);
            }

            $destFile = rtrim($targetDir, '/\\') . '/backup_' . now()->format('Ymd_His') . '.enc';
            $checksum = $encryption->encryptFile($sourcePath, $destFile, $hexKey ?? '');
            $size     = (int) filesize($destFile);

            $job->update([
                'file_path'       => $destFile,
                'file_size_bytes' => $size,
                'checksum_sha256' => $checksum,
                'status'          => BackupStatus::Completed,
                'completed_at'    => now(),
            ]);

        } catch (Throwable $e) {
            $job->update(['status' => BackupStatus::Failed]);
            throw $e;
        }

        // Mark old records past retention as pruned
        BackupJob::where('status', BackupStatus::Completed)
            ->where('retention_expires_on', '<', now()->toDateString())
            ->update(['status' => BackupStatus::Pruned]);
    }
}
