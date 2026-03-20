<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    /**
     * Success response
     */
    public function successResponse(
        string $message = 'Success',
        mixed $data = null,
        int $statusCode = 200,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            if ($data instanceof LengthAwarePaginator) {
                $response['data'] = $data->items();
                $response['meta'] = [
                    'current_page' => $data->currentPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'last_page' => $data->lastPage(),
                ];
            } else {
                $response['data'] = $data;
                if (!empty($meta)) {
                    $response['meta'] = $meta;
                }
            }
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Error response
     */
    public function errorResponse(
        string $message = 'Error',
        int $statusCode = 400,
        array $errors = []
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Validation error response
     */
    public function validationErrorResponse(array $errors): JsonResponse
    {
        return $this->errorResponse('Validation failed', 422, $errors);
    }

    /**
     * Unauthorized response
     */
    public function unauthorizedResponse(string $message = 'Unauthenticated'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Forbidden response
     */
    public function forbiddenResponse(string $message = 'You are not authorized to perform this action.'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Not found response
     */
    public function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }
}
