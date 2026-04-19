<?php

declare(strict_types=1);

namespace CampusLearn\Support\Exceptions;

use RuntimeException;

final class IdempotencyReplay extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $scope,
        public readonly string $keyHash,
    ) {
        parent::__construct($message);
    }
}
