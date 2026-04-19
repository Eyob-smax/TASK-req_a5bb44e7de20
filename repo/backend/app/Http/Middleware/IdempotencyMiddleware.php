<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Responses\ApiEnvelope;
use CampusLearn\Billing\IdempotencyService;
use CampusLearn\Support\Exceptions\IdempotencyReplay;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class IdempotencyMiddleware
{
    public const HEADER = 'Idempotency-Key';
    public const REPLAY_HEADER = 'X-Idempotent-Replay';

    public function __construct(
        private readonly IdempotencyService $service,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $rawKey = $request->headers->get(self::HEADER);
        if ($rawKey === null || trim($rawKey) === '') {
            return ApiEnvelope::error(
                'IDEMPOTENCY_KEY_REQUIRED',
                'Missing Idempotency-Key header.',
                400,
            );
        }

        $scope = $this->scopeFor($request);
        $canonicalPayload = $this->canonicalPayload($request);

        try {
            $outcome = $this->service->execute(
                $scope,
                $rawKey,
                $canonicalPayload,
                function () use ($request, $next): array {
                    /** @var Response $response */
                    $response = $next($request);
                    $body = $this->decodeBody($response);
                    return [
                        'status' => $response->getStatusCode(),
                        'body' => $body,
                    ];
                },
            );
        } catch (IdempotencyReplay $replay) {
            return ApiEnvelope::error(
                'IDEMPOTENCY_KEY_CONFLICT',
                $replay->getMessage(),
                409,
            );
        }

        return new JsonResponse(
            $outcome['body'],
            $outcome['status'],
            [self::REPLAY_HEADER => $outcome['replayed'] ? 'true' : 'false'],
        );
    }

    private function scopeFor(Request $request): string
    {
        $route = $request->route();
        $base = ($route !== null && $route->getName() !== null)
            ? (string) $route->getName()
            : strtolower($request->method()) . ' ' . $request->path();

        if ($route !== null) {
            $params = collect($route->parameters())
                ->map(fn ($v) => is_object($v) && method_exists($v, 'getKey') ? $v->getKey() : $v)
                ->values()
                ->implode(':');
            if ($params !== '') {
                return "{$base}:{$params}";
            }
        }

        return $base;
    }

    /**
     * @return array<string, mixed>
     */
    private function canonicalPayload(Request $request): array
    {
        return [
            'method' => strtoupper($request->method()),
            'path' => '/' . ltrim($request->path(), '/'),
            'body' => $request->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeBody(Response $response): array
    {
        $content = $response->getContent();
        if ($content === false || $content === '') {
            return [];
        }
        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : ['raw' => $content];
    }
}
