<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\RosterImport;
use App\Models\User;
use CampusLearn\Auth\ScopeContext;
use CampusLearn\Auth\ScopeResolutionService;

final class RosterImportPolicy
{
    public function __construct(
        private readonly ScopeResolutionService $scopeService,
    ) {
    }

    public function viewAny(User $user): bool
    {
        return $this->isStaff($user->id);
    }

    public function view(User $user, RosterImport $import): bool
    {
        return $this->isStaff($user->id) || $import->initiated_by === $user->id;
    }

    public function create(User $user, int $termId): bool
    {
        if ($this->scopeService->canPerform($user->id, RoleName::Administrator, ScopeContext::global())) {
            return true;
        }
        return $this->scopeService->canPerform($user->id, RoleName::Registrar, ScopeContext::term($termId))
            || $this->scopeService->hasRole($user->id, RoleName::Registrar);
    }

    private function isStaff(int $userId): bool
    {
        return $this->scopeService->canPerform($userId, RoleName::Administrator, ScopeContext::global())
            || $this->scopeService->hasRole($userId, RoleName::Registrar);
    }
}
