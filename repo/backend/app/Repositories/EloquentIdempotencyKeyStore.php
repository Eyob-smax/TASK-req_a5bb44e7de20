<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\IdempotencyKey;
use CampusLearn\Billing\Contracts\IdempotencyKeyStore;
use CampusLearn\Billing\StoredIdempotencyResult;
use Illuminate\Support\Carbon;

final class EloquentIdempotencyKeyStore implements IdempotencyKeyStore
{
    public function find(string $scope, string $keyHash): ?StoredIdempotencyResult
    {
        $row = IdempotencyKey::query()
            ->where('scope', $scope)
            ->where('key_hash', $keyHash)
            ->where('expires_at', '>', now())
            ->first();

        if ($row === null) {
            return null;
        }

        return new StoredIdempotencyResult(
            scope: (string) $row->scope,
            keyHash: (string) $row->key_hash,
            requestFingerprint: (string) $row->request_fingerprint,
            resultStatus: (int) $row->result_status,
            resultBody: is_array($row->result_body) ? $row->result_body : [],
        );
    }

    public function store(
        string $scope,
        string $keyHash,
        string $requestFingerprint,
        int $resultStatus,
        array $resultBody,
        int $ttlHours,
    ): StoredIdempotencyResult {
        IdempotencyKey::query()->create([
            'scope' => $scope,
            'key_hash' => $keyHash,
            'request_fingerprint' => $requestFingerprint,
            'result_status' => $resultStatus,
            'result_body' => $resultBody,
            'expires_at' => Carbon::now()->addHours($ttlHours),
        ]);

        return new StoredIdempotencyResult(
            scope: $scope,
            keyHash: $keyHash,
            requestFingerprint: $requestFingerprint,
            resultStatus: $resultStatus,
            resultBody: $resultBody,
        );
    }
}
