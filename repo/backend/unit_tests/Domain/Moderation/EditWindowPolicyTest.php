<?php

declare(strict_types=1);

namespace Tests\Domain\Moderation;

use CampusLearn\Moderation\EditWindowPolicy;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class EditWindowPolicyTest extends TestCase
{
    public function testImmediatelyAfterCreationAllowed(): void
    {
        $policy = new EditWindowPolicy(15);
        $created = new DateTimeImmutable('2026-04-18 10:00:00');
        $this->assertTrue($policy->canAuthorEdit($created, $created));
    }

    public function testJustBeforeWindowCloseAllowed(): void
    {
        $policy = new EditWindowPolicy(15);
        $created = new DateTimeImmutable('2026-04-18 10:00:00');
        $now = new DateTimeImmutable('2026-04-18 10:14:59');
        $this->assertTrue($policy->canAuthorEdit($created, $now));
    }

    public function testExactlyAtWindowCloseDenied(): void
    {
        $policy = new EditWindowPolicy(15);
        $created = new DateTimeImmutable('2026-04-18 10:00:00');
        $now = new DateTimeImmutable('2026-04-18 10:15:00');
        $this->assertFalse($policy->canAuthorEdit($created, $now));
    }

    public function testPastWindowDenied(): void
    {
        $policy = new EditWindowPolicy(15);
        $created = new DateTimeImmutable('2026-04-18 10:00:00');
        $now = new DateTimeImmutable('2026-04-18 10:15:01');
        $this->assertFalse($policy->canAuthorEdit($created, $now));
    }

    public function testNowBeforeCreationDenied(): void
    {
        $policy = new EditWindowPolicy(15);
        $created = new DateTimeImmutable('2026-04-18 10:00:00');
        $now = new DateTimeImmutable('2026-04-18 09:59:59');
        $this->assertFalse($policy->canAuthorEdit($created, $now));
    }
}
