<?php

declare(strict_types=1);

namespace CampusLearn\Auth\Contracts;

use CampusLearn\Auth\Grant;

interface ScopeResolver
{
    /**
     * Returns the currently-active grants for the given user id.
     *
     * @return Grant[]
     */
    public function activeGrantsFor(int $userId): array;
}
