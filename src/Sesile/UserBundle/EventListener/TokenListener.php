<?php

namespace Sesile\UserBundle\EventListener;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\SecurityContext;

class TokenListener
{

    private $route = null;
    private $sc = null;


    public function __construct(Router $oRouter, SecurityContext $oSecurityContext, Session $oSession)
    {
        $this->route = $oRouter;
        $this->sc = $oSecurityContext;
        $this->session = $oSession;

    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();


        /*
         * $controller peut être une classe ou une closure. Ce n'est pas
         * courant dans Symfony2 mais ça peut arriver.
         * Si c'est une classe, elle est au format array
         */
        if (!is_array($controller)) {
            return;
        }

        // Si l'utilisateur n est pas authentifié et en se trouve pas sur la home alors on le degage
        if(null !== $this->sc->getToken() && $this->route->getContext()->getPathInfo() != "/") {

            // Si l'utilisateur n est pas authentifié et n est pas actif alors on le dagge
            if (false === $this->sc->isGranted('IS_AUTHENTICATED_REMEMBERED') || false === $this->sc->getToken()->getUser()->isEnabled()) {
                throw new AccessDeniedHttpException("Votre compte n'a pas été validé dans SESILE.");
                /*
                $this->session->getFlashBag()->add(
                    "error",
                    "Votre compte n'a pas été validé dans SESILE."
                );
                $this->sc->getToken()->setAuthenticated(false);
                $this->sc->setToken(false);
                */
            }
        }


    }
}