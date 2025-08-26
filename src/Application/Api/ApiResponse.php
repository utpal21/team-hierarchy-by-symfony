<?php
declare(strict_types=1);

namespace App\Application\Api;

final class ApiResponse
{
    private function __construct() {}

    /**
     * Generates a success response structure.
     *
     * @param mixed $data The data to include in the response.
     * @param string $message The success message.
     * @return array The structured success response.
     */
    public static function success(mixed $data, string $message = 'OK'): array
    {
        return [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ];
    }
    
    /**
     * Generates an error response structure.
     *
     * @param string $message The error message.
     * @param int $status The HTTP status code.
     * @param array|null $errors Optional array of detailed error information.
     * @param int $code An application-specific error code.
     * @return array The structured error response.
     */
    public static function error(string $message, int $status, ?array $errors = null, int $code = 0): array 
    {
        return [
            'status'  => 'error',
            'message' => $message,
            'errors'  => $errors ?? [],
            'code'    => $code
        ];
    }
}
