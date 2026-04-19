<?php

declare(strict_types=1);

namespace Tests\Domain\Auth;

use CampusLearn\Auth\LoginThrottlePolicy;
use PHPUnit\Framework\TestCase;

final class LoginThrottlePolicyTest extends TestCase
{
    public function testBelowThresholdAllows(): void
    {
        $policy = new LoginThrottlePolicy(5, 15, 15);
        $this->assertFalse($policy->shouldLock(4));
    }

    public function testAtThresholdLocks(): void
    {
        $policy = new LoginThrottlePolicy(5, 15, 15);
        $this->assertTrue($policy->shouldLock(5));
    }

    public function testAboveThresholdLocks(): void
    {
        $policy = new LoginThrottlePolicy(5, 15, 15);
        $this->assertTrue($policy->shouldLock(9));
    }

    public function testMetadataExposed(): void
    {
        $policy = new LoginThrottlePolicy(5, 15, 30);
        $this->assertSame(5, $policy->threshold());
        $this->assertSame(15, $policy->windowMinutes());
        $this->assertSame(30, $policy->lockDurationMinutes());
    }
}
