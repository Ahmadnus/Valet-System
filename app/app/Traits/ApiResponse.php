<?php

namespace App\app\Traits;

trait ApiResponse
{
    public function successResponse($message, $data = null, $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'code' => $status,
        ], $status);
    }

    public function errorResponse($message, $status = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'code' => $status,
        ], $status);
    }
}
