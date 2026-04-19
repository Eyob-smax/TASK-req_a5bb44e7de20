<?php

declare(strict_types=1);

namespace Tests\Domain\Billing;

use CampusLearn\Billing\Contracts\IdempotencyKeyStore;
use CampusLearn\Billing\IdempotencyService;
use CampusLearn\Billing\StoredIdempotencyResult;
use CampusLearn\Support\Exceptions\IdempotencyReplay;
use PHPUnit\Framework\TestCase;

final class IdempotencyServiceTest extends TestCase
{
    public function testFirstCallExecutesAndStores(): void
    {
        $store = $this->fakeStore();
        $service = new IdempotencyService($store);

        $calls = 0;
        $result = $service->execute('orders.pay', 'abc-123', ['amount' => 100], function () use (&$calls) {
            $calls++;
            return ['status' => 201, 'body' => ['id' => 42]];
        });

        $this->assertSame(1, $calls);
        $this->assertFalse($result['replayed']);
        $this->assertSame(201, $result['status']);
        $this->assertSame(['id' => 42], $result['body']);
    }

    public function testReplayReturnsCachedWithoutInvokingClosure(): void
    {
        $store = $this->fakeStore();
        $service = new IdempotencyService($store);

        $calls = 0;
        $closure = function () use (&$calls) {
            $calls++;
            return ['status' => 201, 'body' => ['id' => 42]];
        };

        $service->execute('orders.pay', 'abc-123', ['amount' => 100], $closure);
        $second = $service->execute('orders.pay', 'abc-123', ['amount' => 100], $closure);

        $this->assertSame(1, $calls);
        $this->assertTrue($second['replayed']);
        $this->assertSame(['id' => 42], $second['body']);
    }

    public function testDifferentFingerprintTriggersReplayConflict(): void
    {
        $store = $this->fakeStore();
        $service = new IdempotencyService($store);

        $service->execute('orders.pay', 'abc-123', ['amount' => 100], fn () => ['status' => 201, 'body' => []]);

        $this->expectException(IdempotencyReplay::class);
        $service->execute('orders.pay', 'abc-123', ['amount' => 500], fn () => ['status' => 201, 'body' => []]);
    }

    public function testHashKeyIsSha256(): void
    {
        $service = new IdempotencyService($this->fakeStore());
        $this->assertSame(hash('sha256', 'xyz'), $service->hashKey('xyz'));
    }

    private function fakeStore(): IdempotencyKeyStore
    {
        return new class implements IdempotencyKeyStore {
            /** @var array<string, StoredIdempotencyResult> */
            private array $rows = [];

            public function find(string $scope, string $keyHash): ?StoredIdempotencyResult
            {
                return $this->rows[$scope . '|' . $keyHash] ?? null;
            }

            public function store(
                string $scope,
                string $keyHash,
                string $requestFingerprint,
                int $resultStatus,
                array $resultBody,
                int $ttlHours,
            ): StoredIdempotencyResult {
                $row = new StoredIdempotencyResult($scope, $keyHash, $requestFingerprint, $resultStatus, $resultBody);
                $this->rows[$scope . '|' . $keyHash] = $row;
                return $row;
            }
        };
    }
}
