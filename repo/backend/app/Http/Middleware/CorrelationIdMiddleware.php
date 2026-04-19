<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class CorrelationIdMiddleware
{
    public const HEADER = 'X-Correlation-Id';
    public const CONTEXT_KEY = 'correlation_id';

    public function handle(Request $request, Closure $next): Response
    {
        $inbound = $request->headers->get(self::HEADER);
        $correlationId = $this->isValidUuid($inbound) ? $inbound : (string) Str::uuid();

        $request->headers->set(self::HEADER, $correlationId);
        $request->attributes->set(self::CONTEXT_KEY, $correlationId);
        Log::withContext([self::CONTEXT_KEY => $correlationId]);

        $response = $next($request);
        $response->headers->set(self::HEADER, $correlationId);
        return $response;
    }

    private function isValidUuid(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $value,
        );
    }
}
