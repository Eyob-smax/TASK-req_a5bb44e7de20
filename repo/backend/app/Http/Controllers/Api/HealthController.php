<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CircuitBreakerService;
use App\Services\RequestMetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

final class HealthController extends Controller
{
    public function __construct(
        private readonly CircuitBreakerService $circuitBreaker,
        private readonly RequestMetricsService $metricsService,
    ) {
    }

    public function index(): JsonResponse
    {
        $dbOk    = $this->checkDatabase();
        $queueOk = $this->checkQueue();
        $overall = $dbOk && $queueOk ? 'ok' : 'degraded';

        return response()->json([
            'status'  => $overall,
            'service' => 'campuslearn',
            'checks'  => [
                'database' => $dbOk ? 'ok' : 'error',
                'queue'    => $queueOk ? 'ok' : 'error',
            ],
        ], $overall === 'ok' ? 200 : 503);
    }

    public function circuit(): JsonResponse
    {
        $snapshot = $this->circuitBreaker->snapshot();
        return response()->json($snapshot);
    }

    public function metrics(): JsonResponse
    {
        $windowSeconds = (int) config('campuslearn.observability.circuit_window_seconds', 300);
        $summary    = $this->metricsService->summary($windowSeconds);
        $latency    = $this->metricsService->latencyPercentiles($windowSeconds);

        return response()->json(array_merge($summary, ['latency_ms' => $latency]));
    }

    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function checkQueue(): bool
    {
        try {
            Queue::size();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
