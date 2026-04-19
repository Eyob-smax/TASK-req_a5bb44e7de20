<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class AppointmentService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly NotificationOrchestrator $notifications,
    ) {
    }

    public function list(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return Appointment::where('owner_user_id', $user->id)
            ->orderBy('scheduled_start')
            ->paginate($perPage);
    }

    public function create(User $actor, User $owner, array $data): Appointment
    {
        return DB::transaction(function () use ($actor, $owner, $data): Appointment {
            $appointment = Appointment::create([
                'owner_user_id'   => $owner->id,
                'resource_type'   => $data['resource_type'],
                'resource_ref'    => $data['resource_ref'] ?? null,
                'scheduled_start' => $data['scheduled_start'],
                'scheduled_end'   => $data['scheduled_end'],
                'status'          => AppointmentStatus::Scheduled,
                'notes'           => $data['notes'] ?? null,
                'created_by'      => $actor->id,
            ]);

            $this->audit->record($actor->id, 'appointment.created', 'appointment', $appointment->id, [
                'owner_user_id' => $owner->id,
            ]);

            return $appointment;
        });
    }

    public function update(User $actor, Appointment $appointment, array $data): Appointment
    {
        return DB::transaction(function () use ($actor, $appointment, $data): Appointment {
            if ($appointment->status === AppointmentStatus::Canceled) {
                throw new RuntimeException('Cannot update a canceled appointment.');
            }

            $wasRescheduled = isset($data['scheduled_start']) || isset($data['scheduled_end']);

            $appointment->update(array_intersect_key($data, array_flip([
                'resource_type', 'resource_ref', 'scheduled_start', 'scheduled_end', 'notes', 'status',
            ])));

            $this->audit->record($actor->id, 'appointment.updated', 'appointment', $appointment->id, [
                'changes' => $data,
            ]);

            if ($wasRescheduled) {
                $this->notifications->notify('appointment.rescheduled', [$appointment->owner_user_id], [
                    ':start' => (string) ($data['scheduled_start'] ?? $appointment->scheduled_start),
                ]);
            }

            return $appointment->fresh();
        });
    }

    public function cancel(User $actor, Appointment $appointment): Appointment
    {
        return DB::transaction(function () use ($actor, $appointment): Appointment {
            if ($appointment->status === AppointmentStatus::Canceled) {
                throw new RuntimeException('Appointment is already canceled.');
            }

            $appointment->update(['status' => AppointmentStatus::Canceled]);

            $this->audit->record($actor->id, 'appointment.canceled', 'appointment', $appointment->id, []);

            $this->notifications->notify('appointment.canceled', [$appointment->owner_user_id], [
                ':start' => $appointment->scheduled_start->format('Y-m-d H:i'),
            ]);

            return $appointment->fresh();
        });
    }
}
