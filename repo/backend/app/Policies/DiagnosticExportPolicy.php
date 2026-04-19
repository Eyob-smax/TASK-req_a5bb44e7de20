<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\User;
use CampusLearn\Auth\ScopeContext;
use CampusLearn\Auth\ScopeResolutionService;

final class DiagnosticExportPolicy
{
    public function __construct(
        private readonly ScopeResolutionService $scopeService,
    ) {
    }

    public function create(User $user): bool
    {
        return $this->scopeService->canPerform(
            $user->id,
            RoleName::Administrator,
            ScopeContext::global(),
        );
    }

    public function download(User $user): bool
    {
        return $this->create($user);
    }
}
