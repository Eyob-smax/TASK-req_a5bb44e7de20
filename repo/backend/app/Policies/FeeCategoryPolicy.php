<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\FeeCategory;
use App\Models\User;
use CampusLearn\Auth\ScopeContext;
use CampusLearn\Auth\ScopeResolutionService;

final class FeeCategoryPolicy
{
    public function __construct(
        private readonly ScopeResolutionService $scopeService,
    ) {
    }

    public function viewAny(User $user): bool
    {
        return $this->manage($user);
    }

    public function manage(User $user): bool
    {
        return $this->scopeService->canPerform($user->id, RoleName::Administrator, ScopeContext::global());
    }

    public function create(User $user): bool
    {
        return $this->manage($user);
    }

    public function update(User $user, FeeCategory $category): bool
    {
        return $this->manage($user);
    }

    public function delete(User $user, FeeCategory $category): bool
    {
        return $this->manage($user);
    }
}
