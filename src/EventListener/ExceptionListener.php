<?php

namespace App\EventListener;

use App\Exception\ApiException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final class ExceptionListener
{
    #[AsEventListener]
    public function onExceptionEvent(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if ($exception instanceof ApiException)
        {
            $response = new JsonResponse([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], $exception->getCode() ?: 400);
            $event->setResponse($response);
        }
    }
}
