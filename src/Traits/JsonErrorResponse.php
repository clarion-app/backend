<?php

namespace ClarionApp\Backend\Traits;

trait JsonErrorResponse
{
    protected function errorResponse(string $message, string $code, int $status = 500)
    {
        return response()->json([
            'error' => $message,
            'code' => $code,
        ], $status);
    }

    protected function validationErrorResponse(array $details)
    {
        return response()->json([
            'error' => 'Validation failed',
            'code' => 'VALIDATION_ERROR',
            'details' => $details,
        ], 422);
    }

    protected function notImplementedResponse()
    {
        return response()->json([
            'error' => 'Not implemented',
            'code' => 'NOT_IMPLEMENTED',
        ], 501);
    }

    protected function internalErrorResponse()
    {
        return response()->json([
            'error' => 'An internal error occurred',
            'code' => 'INTERNAL_ERROR',
        ], 500);
    }
}
