<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\BackupMetadataJob;
use App\Models\BackupJob;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class BackupService
{
    public function __construct(
        private readonly AuditLogger $audit,
    ) {
    }

    public function list(int $perPage = 20): LengthAwarePaginator
    {
        return BackupJob::orderByDesc('id')->paginate($perPage);
    }

    public function trigger(User $actor): BackupJob
    {
        $retainDays = (int) config('campuslearn.backups.retention_days', 30);

        $pending = BackupJob::create([
            'scheduled_for'        => now(),
            'file_path'            => null,
            'file_size_bytes'      => null,
            'checksum_sha256'      => null,
            'status'               => \App\Enums\BackupStatus::Pending,
            'retention_expires_on' => now()->addDays($retainDays)->toDateString(),
            'completed_at'         => null,
        ]);

        BackupMetadataJob::dispatch($pending->id);

        $this->audit->record($actor->id, 'backup.triggered', 'backup', $pending->id, [
            'triggered_by' => $actor->id,
        ]);

        return $pending;
    }

    public function find(int $id): BackupJob
    {
        return BackupJob::findOrFail($id);
    }
}
