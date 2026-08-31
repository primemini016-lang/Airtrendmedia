<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Shared helper utilities for the API responses and file handling.
 */
trait ApiResponse
{
    protected function ok(string $message = 'OK', mixed $data = null, int $status = 200)
    {
        $payload = ['message' => $message];
        if (! is_null($data)) {
            $payload['data'] = $data;
        }
        return response()->json($payload, $status);
    }

    protected function error(string $message, int $status = 400, mixed $errors = null)
    {
        $payload = ['error' => $message];
        if (! is_null($errors)) {
            $payload['errors'] = $errors;
        }
        return response()->json($payload, $status);
    }
}
