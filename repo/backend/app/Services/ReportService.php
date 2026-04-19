<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

final class ReportService
{
    public function __construct(
        private readonly AuditLogger $audit,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function submit(User $reporter, string $targetType, int $targetId, array $data): Report
    {
        return DB::transaction(function () use ($reporter, $targetType, $targetId, $data): Report {
            $report = Report::create([
                'reporter_id' => $reporter->id,
                'target_type' => $targetType,
                'target_id'   => $targetId,
                'reason'      => (string) ($data['reason'] ?? ''),
                'notes'       => $data['notes'] ?? null,
                'status'      => ReportStatus::Open,
                'created_at'  => now(),
            ]);

            $this->audit->record($reporter->id, 'report.submitted', $targetType, $targetId, [
                'report_id' => $report->id,
                'reason'    => $report->reason,
            ]);

            return $report->fresh();
        });
    }

    public function resolve(User $moderator, Report $report, string $status, ?string $notes = null): Report
    {
        $newStatus = ReportStatus::from($status);
        return DB::transaction(function () use ($moderator, $report, $newStatus, $notes): Report {
            $report->status      = $newStatus;
            $report->resolved_by = $moderator->id;
            $report->resolved_at = now();
            if ($notes !== null) {
                $report->notes = trim(($report->notes ?? '') . "\n[resolver] " . $notes);
            }
            $report->save();

            $this->audit->record($moderator->id, 'report.resolved', 'report', $report->id, [
                'status' => $newStatus->value,
            ]);

            return $report->fresh();
        });
    }
}
