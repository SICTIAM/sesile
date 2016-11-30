<?php

namespace Sesile\UserBundle\EventListener;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TokenListener
{

    private $route = null;
    private $sc = null;


    public function __construct(Router $oRouter, SecurityContext $oSecurityContext, ContainerInterface $oContainer)
    {
        $this->route = $oRouter;
        $this->sc = $oSecurityContext;
        $this->container = $oContainer;


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

        // On recupere la liste des access_control dans le fichier security.yml
        $security_file = sprintf("%s/config/security.yml", $this->container->get('kernel')->getRootDir());
        $parsed = Yaml::parse(file_get_contents($security_file));
        $security_access_control = $parsed['security']["access_control"];

        // on liste les access_control
        foreach ($security_access_control as $access_control) {

            // Si l URL n a pas bessoin d authentification on laisse l utilisateur continuer
            if ($access_control["roles"] == "IS_AUTHENTICATED_ANONYMOUSLY" && strpos($this->route->getContext()->getPathInfo(), substr($access_control["path"], 2))) {
                return;
            }
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