<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class EnrollmentDecisionService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly NotificationOrchestrator $notifications,
    ) {
    }

    /**
     * Approve an enrollment — confirm it as Enrolled and notify the student.
     */
    public function approve(User $actor, Enrollment $enrollment): Enrollment
    {
        return DB::transaction(function () use ($actor, $enrollment): Enrollment {
            if ($enrollment->status === EnrollmentStatus::Enrolled) {
                throw new RuntimeException('Enrollment is already in enrolled state.');
            }

            $enrollment->update([
                'status'      => EnrollmentStatus::Enrolled,
                'enrolled_at' => now(),
                'withdrawn_at' => null,
            ]);

            $this->audit->record($actor->id, 'enrollment.approved', 'enrollment', $enrollment->id, [
                'user_id'    => $enrollment->user_id,
                'section_id' => $enrollment->section_id,
            ]);

            $this->notifications->notify('enrollment.approved', [$enrollment->user_id], [
                ':section' => (string) $enrollment->section_id,
            ]);

            return $enrollment->fresh();
        });
    }

    /**
     * Deny an enrollment — transition to Withdrawn and notify the student.
     */
    public function deny(User $actor, Enrollment $enrollment): Enrollment
    {
        return DB::transaction(function () use ($actor, $enrollment): Enrollment {
            if ($enrollment->status === EnrollmentStatus::Withdrawn) {
                throw new RuntimeException('Enrollment is already withdrawn.');
            }

            $enrollment->update([
                'status'       => EnrollmentStatus::Withdrawn,
                'withdrawn_at' => now(),
            ]);

            $this->audit->record($actor->id, 'enrollment.denied', 'enrollment', $enrollment->id, [
                'user_id'    => $enrollment->user_id,
                'section_id' => $enrollment->section_id,
            ]);

            $this->notifications->notify('enrollment.denied', [$enrollment->user_id], [
                ':section' => (string) $enrollment->section_id,
            ]);

            return $enrollment->fresh();
        });
    }
}
