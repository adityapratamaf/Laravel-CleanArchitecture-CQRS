<?php

namespace App\Support\Helpers;

class ApiResponse
{
    public static function error(
        string $message = 'server error',
        int $code = 500,
        mixed $errors = null
    ): array {
        return [
            'result' => false,
            'code' => $code,
            'message' => $message,
            'errors' => $errors,
        ];
    }

    public static function success(
        mixed $data = null,
        string $message = 'success',
        int $code = 200
    ): array {
        return [
            'result' => true,
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ];
    }
}