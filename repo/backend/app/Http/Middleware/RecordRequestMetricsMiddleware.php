<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\RequestMetric;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class RecordRequestMetricsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $startMs = (int) (microtime(true) * 1000);

        $response = $next($request);

        try {
            $durationMs  = (int) (microtime(true) * 1000) - $startMs;
            $route       = $request->route()?->getName() ?? $request->path();
            $correlationId = (string) $request->attributes->get(
                CorrelationIdMiddleware::CONTEXT_KEY,
                '',
            );

            RequestMetric::create([
                'correlation_id' => $correlationId,
                'route'          => $route,
                'method'         => strtoupper($request->method()),
                'status'         => $response->getStatusCode(),
                'duration_ms'    => $durationMs,
                'user_id'        => $request->user()?->id,
                'created_at'     => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to record request metric', ['error' => $e->getMessage()]);
        }

        return $response;
    }
}
