<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RequestMetric;

final class RequestMetricsService
{
    /**
     * @return array{error_rate: float, request_count: int, error_count: int, period_seconds: int}
     */
    public function summary(int $windowSeconds = 300): array
    {
        $since = now()->subSeconds($windowSeconds);

        $total  = RequestMetric::where('created_at', '>=', $since)->count();
        $errors = RequestMetric::where('created_at', '>=', $since)
            ->where('status', '>=', 500)
            ->count();

        $errorRate = $total > 0 ? round(($errors / $total) * 100, 4) : 0.0;

        return [
            'error_rate'     => $errorRate,
            'request_count'  => $total,
            'error_count'    => $errors,
            'period_seconds' => $windowSeconds,
        ];
    }

    /**
     * @return array{p50: int, p95: int, p99: int}
     */
    public function latencyPercentiles(int $windowSeconds = 300): array
    {
        $since = now()->subSeconds($windowSeconds);
        $rows  = RequestMetric::where('created_at', '>=', $since)
            ->orderBy('duration_ms')
            ->pluck('duration_ms')
            ->all();

        if (empty($rows)) {
            return ['p50' => 0, 'p95' => 0, 'p99' => 0];
        }

        $count = count($rows);

        return [
            'p50' => (int) ($rows[(int) floor($count * 0.50)] ?? 0),
            'p95' => (int) ($rows[(int) floor($count * 0.95)] ?? 0),
            'p99' => (int) ($rows[(int) floor($count * 0.99)] ?? 0),
        ];
    }
}
