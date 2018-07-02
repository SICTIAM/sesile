<?php


namespace Sesile\MainBundle\EventListener;


use Sesile\UserBundle\Exceptions\OzwilloAccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class OzwilloAccessDeniedListener
{
    public function onCoreException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
            if ($exception instanceof OzwilloAccessDeniedException) {
                $event->setResponse(new Response('', Response::HTTP_UNAUTHORIZED));
            }
    }
}