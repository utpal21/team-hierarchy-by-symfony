<?php
declare(strict_types=1);

namespace App\Infrastructure\Exception;

use App\Domain\Exception\DomainValidationException;
use App\Application\Api\ApiResponder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class ApiExceptionSubscriber implements EventSubscriberInterface
{
    private ApiResponder $responder;

    /**
     * Constructor.
     *
     * @param ApiResponder $responder The API responder for generating responses
     */
    public function __construct(ApiResponder $responder)
    {
        $this->responder = $responder;
    }

    /**
     * Specifies the events to subscribe to.
     *
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [ 'kernel.exception' => 'onKernelException' ];
    }

    /**
     * Handles exceptions and converts them to JSON API responses.
     *
     * @param ExceptionEvent $event
     * @return void
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception  = $event->getThrowable();
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $message    = 'Internal Server Error';
        $errorCode  = 'INTERNAL_ERROR';
        $errors     = null;

        match (true) {
            $exception instanceof DomainValidationException => [
                $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY,
                $message    = 'Validation Failed',
                $errorCode  = 'VALIDATION_FAILED',
                $errors     = $exception->getErrors(),
            ],

            $exception instanceof AuthenticationException => [
                $statusCode = Response::HTTP_UNAUTHORIZED,
                $message    = 'Authentication Failed',
                $errorCode  = 'AUTHENTICATION_FAILED',
                $errors     = [$exception->getMessage()],
            ],

            $exception instanceof AccessDeniedException => [
                $statusCode = Response::HTTP_FORBIDDEN,
                $message    = 'Access Denied',
                $errorCode  = 'ACCESS_DENIED',
                $errors     = [$exception->getMessage()],
            ],

            $exception instanceof HttpExceptionInterface => [
                $statusCode = $exception->getStatusCode(),
                $message    = $exception->getMessage() ?: $this->mapStatusCodeToMessage($statusCode),
                $errorCode  = strtoupper(str_replace(' ', '_', $message)),
            ],

            default => [
                $errors = [$exception->getMessage()],
            ]
        };

        $event->setResponse(
            $this->responder->error(
                message: $message,
                status: $statusCode,
                errors: $errors,
                code: $statusCode
            )
        );
    }
    
    /**
     * Maps HTTP status codes to default messages.
     *
     * @param int $statusCode
     * @return string
     */
    private function mapStatusCodeToMessage(int $statusCode): string
    {
        return match ($statusCode) {
            Response::HTTP_BAD_REQUEST       => 'Bad Request',
            Response::HTTP_UNAUTHORIZED      => 'Unauthorized',
            Response::HTTP_FORBIDDEN         => 'Forbidden',
            Response::HTTP_NOT_FOUND         => 'Not Found',
            Response::HTTP_METHOD_NOT_ALLOWED=> 'Method Not Allowed',
            Response::HTTP_UNPROCESSABLE_ENTITY => 'Validation Failed',
            default => 'Unexpected Error',
        };
    }
}

