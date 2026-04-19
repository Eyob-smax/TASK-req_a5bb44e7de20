<?php

declare(strict_types=1);

namespace CampusLearn\Billing\Contracts;

use CampusLearn\Billing\StoredIdempotencyResult;

interface IdempotencyKeyStore
{
    public function find(string $scope, string $keyHash): ?StoredIdempotencyResult;

    public function store(
        string $scope,
        string $keyHash,
        string $requestFingerprint,
        int $resultStatus,
        array $resultBody,
        int $ttlHours,
    ): StoredIdempotencyResult;
}
