<?php

declare(strict_types=1);

namespace CampusLearn\Billing;

final class StoredIdempotencyResult
{
    public function __construct(
        public readonly string $scope,
        public readonly string $keyHash,
        public readonly string $requestFingerprint,
        public readonly int $resultStatus,
        public readonly array $resultBody,
    ) {
    }
}
