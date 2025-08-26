<?php
declare(strict_types=1);

namespace App\Presentation\Http\Subscriber;

use App\Domain\Exception\DomainException;
use App\Domain\Exception\TeamNotFound;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApiExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @param ApiResponder $responder The API responder for generating responses
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onException'];
    }

    /**
     * Handles exceptions and converts them to JSON API responses.
     *
     * @param ExceptionEvent $event
     * @return void
     */
    public function onException(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();

        if ($e instanceof TeamNotFound) {
            $event->setResponse(new JsonResponse([
                'type' => 'about:blank','title' => 'Not Found','detail' => $e->getMessage(),'status' => 404,
            ], 404));
            return;
        }

        if ($e instanceof DomainException) {
            $event->setResponse(new JsonResponse([
                'type' => 'about:blank','title' => 'Domain error','detail' => $e->getMessage(),'status' => 422,
            ], 422));
        }
    }
}
