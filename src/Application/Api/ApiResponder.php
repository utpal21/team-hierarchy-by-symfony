<?php
declare(strict_types=1);

namespace App\Application\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ApiResponder
{
    /**
     * Generates a success JSON response.
     *
     * @param mixed $data The data to include in the response.
     * @param string $message The success message (default is 'OK').
     * @param int $status The HTTP status code (default is 200 OK).
     * @return JsonResponse The JSON response containing the success details.
     */
    public function success(
        mixed $data,
        string $message = 'OK',
        int $status = Response::HTTP_OK
    ): JsonResponse {
        return new JsonResponse(
            ApiResponse::success($data, $message),
            $status
        );
    }

    /**
     * Generates an error JSON response.
     *
     * @param string $message The error message.
     * @param int $status The HTTP status code (default is 400 Bad Request).
     * @param array|null $errors Optional array of detailed error information.
     * @param int $code An application-specific error code (default is 0).
     * @return JsonResponse The JSON response containing the error details.
     */
    public function error(
        string $message,
        int $status = Response::HTTP_BAD_REQUEST,
        ?array $errors = null,
        int $code = 0
    ): JsonResponse {
        return new JsonResponse(
            ApiResponse::error($message, $status, $errors, $code),
            $status
        );
    }
}
