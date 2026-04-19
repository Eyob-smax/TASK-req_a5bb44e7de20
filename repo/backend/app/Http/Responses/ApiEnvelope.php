<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

final class ApiEnvelope
{
    /**
     * @param array<string, mixed>|null $meta
     */
    public static function data(mixed $data, int $status = 200, ?array $meta = null, array $headers = []): JsonResponse
    {
        $body = ['data' => $data];
        if ($meta !== null) {
            $body['meta'] = $meta;
        }
        return new JsonResponse($body, $status, $headers);
    }

    /**
     * @param array<string, mixed>|null $details
     */
    public static function error(
        string $code,
        string $message,
        int $status,
        ?array $details = null,
        array $headers = [],
    ): JsonResponse {
        $error = [
            'code' => $code,
            'message' => $message,
        ];
        if ($details !== null) {
            $error['details'] = $details;
        }
        return new JsonResponse(['error' => $error], $status, $headers);
    }
}
