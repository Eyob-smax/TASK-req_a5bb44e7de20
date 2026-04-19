<?php

declare(strict_types=1);

namespace CampusLearn\Billing;

final class RefundDecision
{
    private function __construct(
        public readonly bool $allowed,
        public readonly ?string $rejectionCode,
        public readonly ?string $rejectionMessage,
    ) {
    }

    public static function allowed(): self
    {
        return new self(true, null, null);
    }

    public static function rejected(string $code, string $message): self
    {
        return new self(false, $code, $message);
    }
}
