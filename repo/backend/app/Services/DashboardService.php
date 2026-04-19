<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BillStatus;
use App\Enums\CircuitBreakerMode;
use App\Enums\ContentState;
use App\Enums\EnrollmentStatus;
use App\Enums\GradeItemState;
use App\Enums\OrderStatus;
use App\Enums\ReconciliationStatus;
use App\Enums\RoleName;
use App\Enums\RosterImportStatus;
use App\Enums\ScopeType;
use App\Models\Enrollment;
use App\Models\GradeItem;
use App\Models\Notification;
use App\Models\Order;
use App\Models\ReconciliationFlag;
use App\Models\RosterImport;
use App\Models\Thread;
use App\Models\User;
use CampusLearn\Auth\ScopeResolutionService;

final class DashboardService
{
    public function __construct(
        private readonly ScopeResolutionService $scopeService,
        private readonly CircuitBreakerService $circuitBreaker,
    ) {
    }

    /** @return array<string, mixed> */
    public function summaryFor(User $user): array
    {
        if ($this->scopeService->hasRole($user->id, RoleName::Administrator)) {
            return $this->adminSummary();
        }

        if ($this->scopeService->hasRole($user->id, RoleName::Registrar)) {
            return $this->registrarSummary($user);
        }

        if ($this->scopeService->hasRole($user->id, RoleName::Teacher)) {
            return $this->teacherSummary($user);
        }

        return $this->studentSummary($user);
    }

    /** @return array<string, mixed> */
    private function studentSummary(User $user): array
    {
        return [
            'enrolled_sections'    => Enrollment::where('user_id', $user->id)
                ->where('status', EnrollmentStatus::Enrolled)
                ->count(),
            'open_bills'           => $user->bills()
                ->whereIn('status', [BillStatus::Open, BillStatus::Partial, BillStatus::PastDue])
                ->count(),
            'unread_notifications' => Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->count(),
            'pending_orders'       => Order::where('user_id', $user->id)
                ->where('status', OrderStatus::PendingPayment)
                ->count(),
        ];
    }

    /** @return array<string, mixed> */
    private function teacherSummary(User $user): array
    {
        $sectionIds = $user->roleAssignments()
            ->whereHas('role', fn ($q) => $q->where('name', RoleName::Teacher->value))
            ->where('scope_type', ScopeType::Section->value)
            ->whereNull('revoked_at')
            ->pluck('scope_id')
            ->filter()
            ->all();

        $draftCount = count($sectionIds) > 0
            ? GradeItem::whereIn('section_id', $sectionIds)
                ->where('state', GradeItemState::Draft)
                ->count()
            : 0;

        return [
            'assigned_sections'    => count($sectionIds),
            'draft_grade_items'    => $draftCount,
            'unread_notifications' => Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->count(),
        ];
    }

    /** @return array<string, mixed> */
    private function registrarSummary(User $user): array
    {
        return [
            'pending_enrollments'    => Enrollment::where('status', EnrollmentStatus::Enrolled)
                ->count(),
            'pending_roster_imports' => RosterImport::whereIn('status', [
                RosterImportStatus::Pending,
                RosterImportStatus::Running,
            ])->count(),
            'unread_notifications'   => Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->count(),
        ];
    }

    /** @return array<string, mixed> */
    private function adminSummary(): array
    {
        $queueSize = Thread::where('state', ContentState::Hidden)->count();
        $flags     = ReconciliationFlag::where('status', ReconciliationStatus::Open)->count();
        $circuit   = $this->circuitBreaker->currentMode() === CircuitBreakerMode::ReadOnly ? 'open' : 'closed';

        return [
            'moderation_queue_size' => $queueSize,
            'reconciliation_flags'  => $flags,
            'circuit_status'        => $circuit,
        ];
    }
}
