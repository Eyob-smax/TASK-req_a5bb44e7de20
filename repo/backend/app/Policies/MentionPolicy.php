<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\Mention;
use App\Models\User;
use CampusLearn\Auth\ScopeContext;
use CampusLearn\Auth\ScopeResolutionService;

final class MentionPolicy
{
    public function __construct(
        private readonly ScopeResolutionService $scopeService,
    ) {
    }

    public function viewAny(User $user): bool
    {
        // Mentions are additionally scoped to the current user at query-time.
        return true;
    }

    public function view(User $user, Mention $mention): bool
    {
        if ($mention->mentioned_user_id === $user->id) {
            return true;
        }

        return $this->scopeService->canPerform($user->id, RoleName::Administrator, ScopeContext::global())
            || $this->scopeService->canPerform($user->id, RoleName::Registrar, ScopeContext::global());
    }
}

