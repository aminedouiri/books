<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    public function sendSuccessResponse($data, $message = "success", $code = 200): JsonResponse
    {
        return response()->json([
            "message" => $message,
            "data" => $data
        ], $code);
    }

    public function sendErrorResponse($message = "error", $code = 400): JsonResponse
    {
        return response()->json([
            "message" => $message,
        ], $code);
    }
}
