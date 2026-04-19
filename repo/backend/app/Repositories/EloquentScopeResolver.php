<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\RoleAssignment;
use CampusLearn\Auth\Contracts\ScopeResolver;
use CampusLearn\Auth\Grant;

final class EloquentScopeResolver implements ScopeResolver
{
    /**
     * @return Grant[]
     */
    public function activeGrantsFor(int $userId): array
    {
        return RoleAssignment::with('role')
            ->where('user_id', $userId)
            ->whereNull('revoked_at')
            ->get()
            ->map(fn (RoleAssignment $ra) => new Grant(
                role:      $ra->role->name,
                scopeType: $ra->scope_type,
                scopeId:   $ra->scope_id,
            ))
            ->all();
    }
}
