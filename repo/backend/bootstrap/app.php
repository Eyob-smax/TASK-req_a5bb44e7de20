<?php

use App\Exceptions\ApiExceptionRenderer;
use App\Http\Middleware\CorrelationIdMiddleware;
use App\Http\Middleware\EnforceReadOnlyModeMiddleware;
use App\Http\Middleware\IdempotencyMiddleware;
use App\Http\Middleware\RecordRequestMetricsMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
        $middleware->append(CorrelationIdMiddleware::class);
        $middleware->append(RecordRequestMetricsMiddleware::class);
        $middleware->alias([
            'idempotent'  => IdempotencyMiddleware::class,
            'read-only'   => EnforceReadOnlyModeMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            return app(ApiExceptionRenderer::class)->render($e, $request);
        });
    })
    ->create();
