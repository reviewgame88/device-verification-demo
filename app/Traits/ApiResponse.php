<?php
// app/Traits/ApiResponse.php

namespace App\Traits;

use App\Constants\ApiErrorCode;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Success Response
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function successResponse($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Error Response
     *
     * @param string $errorCode
     * @param string|null $customMessage
     * @param mixed $errors
     * @return JsonResponse
     */
    protected function errorResponse(string $errorCode, ?string $customMessage = null, $errors = null): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'code' => $errorCode,
            'message' => $customMessage ?? ApiErrorCode::getMessage($errorCode),
            'errors' => $errors
        ], ApiErrorCode::getStatusCode($errorCode));
    }

    /**
     * Validation Error Response
     *
     * @param array $errors
     * @return JsonResponse
     */
    protected function validationErrorResponse(array $errors): JsonResponse
    {
        return $this->errorResponse(
            ApiErrorCode::VALIDATION_ERROR,
            'The given data was invalid.',
            $errors
        );
    }
}