<?php

namespace App\Support\Api;

use Illuminate\Http\JsonResponse;

trait RespondsWithApiEnvelope
{
    protected function success(mixed $data = null, string $message = '', int $status = 200, array $meta = []): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'errors' => null,
            ...($meta === [] ? [] : ['meta' => $meta]),
        ], $status);
    }
}
