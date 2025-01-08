<?php
// src/EventListener/AccessDeniedListener.php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

class AccessDeniedListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        if ($exception instanceof AccessDeniedException) {
            // Vérifier si la requête est JSON
            if ($request->headers->get('Accept') === 'application/json') {
                $response = new JsonResponse([
                    'error' => 'Access Denied. You do not have permission to access this resource.',
                ], Response::HTTP_FORBIDDEN);

                $event->setResponse($response);
            }
        }
    }
}