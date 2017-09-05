<?php

namespace Sesile\ApiBundle\EventListener;

use Sesile\ApiBundle\Controller\TokenAuthenticatedController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Doctrine\ORM\EntityManager;

class TokenListener
{

    private $em = null;
    private $sc = null;


    public function __construct(EntityManager $oEntityManager, TokenStorage $oSecurityContext)
    {
        $this->em = $oEntityManager;
        $this->sc = $oSecurityContext;

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

        if ($controller[0] instanceof TokenAuthenticatedController) {
            $headers = $event->getRequest()->headers;
            if ($headers->has("token") && $headers->has("secret")) {
                $entity = $this->em->getRepository('SesileUserBundle:User')->findOneBy(array('apitoken' => $headers->get('token'), 'apisecret' => $headers->get('secret'), 'apiactivated' => true));;

                if (empty($entity)) {
                    throw new AccessDeniedHttpException('Cette action nécessite un couple token - secret valide!');
                }


            } else {
                throw new AccessDeniedHttpException('Cette action nécessite un couple token - secret valide!');
            }


        }
    }
}