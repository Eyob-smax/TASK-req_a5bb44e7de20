<?php

declare(strict_types=1);

namespace CampusLearn\Auth;

use App\Enums\RoleName;
use App\Enums\ScopeType;

final class Grant
{
    public function __construct(
        public readonly RoleName $role,
        public readonly ScopeType $scopeType,
        public readonly ?int $scopeId,
    ) {
    }
}
