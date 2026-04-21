<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\BillSchedule;
use App\Models\User;
use CampusLearn\Auth\ScopeContext;
use CampusLearn\Auth\ScopeResolutionService;

final class BillSchedulePolicy
{
    public function __construct(
        private readonly ScopeResolutionService $scopeService,
    ) {
    }

    public function viewAny(User $user): bool
    {
        // Listing is additionally scoped at the query layer to the current user.
        return true;
    }

    public function view(User $user, BillSchedule $schedule): bool
    {
        return $schedule->user_id === $user->id || $this->isFinanceStaff($user->id);
    }

    public function update(User $user, BillSchedule $schedule): bool
    {
        return $schedule->user_id === $user->id || $this->isFinanceStaff($user->id);
    }

    private function isFinanceStaff(int $userId): bool
    {
        return $this->scopeService->canPerform($userId, RoleName::Administrator, ScopeContext::global())
            || $this->scopeService->canPerform($userId, RoleName::Registrar, ScopeContext::global());
    }
}

