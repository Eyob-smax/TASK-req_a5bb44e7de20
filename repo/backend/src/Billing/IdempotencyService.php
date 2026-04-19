<?php

declare(strict_types=1);

namespace CampusLearn\Billing;

use CampusLearn\Billing\Contracts\IdempotencyKeyStore;
use CampusLearn\Support\Exceptions\IdempotencyReplay;
use Closure;

final class IdempotencyService
{
    public function __construct(
        private readonly IdempotencyKeyStore $store,
        private readonly int $ttlHours = 24,
    ) {
    }

    /**
     * Hashes a raw idempotency key for storage. Never persist the raw header value.
     */
    public function hashKey(string $rawKey): string
    {
        return hash('sha256', $rawKey);
    }

    /**
     * Produces a request fingerprint so replays with differing payloads can be detected.
     *
     * @param array<string, mixed> $canonicalPayload
     */
    public function fingerprint(array $canonicalPayload): string
    {
        ksort($canonicalPayload);
        return hash('sha256', json_encode($canonicalPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @param Closure(): array{status: int, body: array<string, mixed>} $executor
     * @return array{status: int, body: array<string, mixed>, replayed: bool}
     */
    public function execute(
        string $scope,
        string $rawKey,
        array $canonicalPayload,
        Closure $executor,
    ): array {
        $keyHash = $this->hashKey($rawKey);
        $fingerprint = $this->fingerprint($canonicalPayload);

        $existing = $this->store->find($scope, $keyHash);
        if ($existing !== null) {
            if ($existing->requestFingerprint !== $fingerprint) {
                throw new IdempotencyReplay(
                    'Idempotency-Key reused with a different request body.',
                    $scope,
                    $keyHash,
                );
            }
            return [
                'status' => $existing->resultStatus,
                'body' => $existing->resultBody,
                'replayed' => true,
            ];
        }

        $result = $executor();
        $this->store->store(
            $scope,
            $keyHash,
            $fingerprint,
            $result['status'],
            $result['body'],
            $this->ttlHours,
        );

        return [
            'status' => $result['status'],
            'body' => $result['body'],
            'replayed' => false,
        ];
    }
}
