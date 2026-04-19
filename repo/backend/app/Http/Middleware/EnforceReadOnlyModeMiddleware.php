<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Responses\ApiEnvelope;
use App\Services\CircuitBreakerService;
use App\Enums\CircuitBreakerMode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnforceReadOnlyModeMiddleware
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function __construct(
        private readonly CircuitBreakerService $circuitBreaker,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (in_array(strtoupper($request->method()), self::SAFE_METHODS, true)) {
            return $next($request);
        }

        if ($this->circuitBreaker->currentMode() === CircuitBreakerMode::ReadOnly) {
            return ApiEnvelope::error(
                'SERVICE_UNAVAILABLE',
                'The system is in read-only mode. Write operations are temporarily disabled.',
                503,
            );
        }

        return $next($request);
    }
}
