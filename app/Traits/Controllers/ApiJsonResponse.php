<?php

namespace App\Traits\Controllers;

use Illuminate\Http\JsonResponse;

/**
 * Standard JSON envelope for API responses.
 */
trait ApiJsonResponse
{
    /**
     * Return a successful JSON response.
     */
    public function success(mixed $data = null, ?string $message = null): JsonResponse
    {
        $payload = ['status' => 'success'];

        if ($message !== null) {
            $payload['message'] = $message;
        }
        if ($data !== null) {
            $payload['data'] = $data;
        }

        return response()->json($payload);
    }

    /**
     * Return an error JSON response.
     */
    public function error(string $message, int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $statusCode);
    }
}
