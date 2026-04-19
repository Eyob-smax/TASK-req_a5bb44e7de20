<?php

declare(strict_types=1);

namespace Tests\Domain\Observability;

use App\Enums\CircuitBreakerMode;
use CampusLearn\Observability\CircuitBreakerPolicy;
use CampusLearn\Observability\ErrorRateWindow;
use PHPUnit\Framework\TestCase;

final class CircuitBreakerPolicyTest extends TestCase
{
    public function testTripsAboveThreshold(): void
    {
        $policy = new CircuitBreakerPolicy(tripThresholdBps: 200, resetThresholdBps: 100, minimumSampleSize: 20);
        $window = new ErrorRateWindow(totalRequests: 100, errorRequests: 5, windowSeconds: 300);
        $this->assertSame(
            CircuitBreakerMode::ReadOnly,
            $policy->evaluate(CircuitBreakerMode::ReadWrite, $window),
        );
    }

    public function testStaysOpenBelowTripThreshold(): void
    {
        $policy = new CircuitBreakerPolicy(200, 100, 20);
        $window = new ErrorRateWindow(totalRequests: 100, errorRequests: 1, windowSeconds: 300);
        $this->assertSame(
            CircuitBreakerMode::ReadWrite,
            $policy->evaluate(CircuitBreakerMode::ReadWrite, $window),
        );
    }

    public function testResetsBelowResetThreshold(): void
    {
        $policy = new CircuitBreakerPolicy(200, 100, 20);
        $window = new ErrorRateWindow(totalRequests: 1000, errorRequests: 5, windowSeconds: 300);
        $this->assertSame(
            CircuitBreakerMode::ReadWrite,
            $policy->evaluate(CircuitBreakerMode::ReadOnly, $window),
        );
    }

    public function testHoldsHysteresisBand(): void
    {
        $policy = new CircuitBreakerPolicy(200, 100, 20);
        $window = new ErrorRateWindow(totalRequests: 1000, errorRequests: 15, windowSeconds: 300);
        $this->assertSame(
            CircuitBreakerMode::ReadOnly,
            $policy->evaluate(CircuitBreakerMode::ReadOnly, $window),
        );
    }

    public function testSmallSampleHoldsCurrentMode(): void
    {
        $policy = new CircuitBreakerPolicy(200, 100, 20);
        $window = new ErrorRateWindow(totalRequests: 5, errorRequests: 5, windowSeconds: 300);
        $this->assertSame(
            CircuitBreakerMode::ReadWrite,
            $policy->evaluate(CircuitBreakerMode::ReadWrite, $window),
        );
    }
}
