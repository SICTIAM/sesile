<?php

namespace Sesile\MainBundle\EventListener;

use Sesile\MainBundle\Entity\Collectivite;
use Sesile\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Doctrine\ORM\EntityManager;

class RouteListener {
    private $em = null;
    private $context = null;
    private $container = null;

    public function __construct(EntityManager $em, TokenStorage $context, ContainerInterface $container, AuthorizationChecker $authorization) {
        $this->em = $em;
        $this->context = $context;
        $this->authorization = $authorization;
        $this->container = $container;
    }

    public function onDomainParse(Event $event) {
        if($this->context->getToken() == null) {
            return;
        }

        $request = $event->getRequest();

        $sousdom = explode(".", $request->getHost());
        $conf = $this->container->getParameter("domain_parse");
        $ssdom = $sousdom[0] != $conf["default"] ? $sousdom[0] : $conf["dbname"];

        $collectivite = $this->em->getRepository('SesileMainBundle:Collectivite')->findOneBy(
            array("domain" => $ssdom, "active" => 1)
        );

        $session = $request->getSession();
        if($collectivite instanceof Collectivite) {
            $session->set('collectivite', $collectivite->getId());
            $session->set('logo', $collectivite->getImage());

            // check si le user est connecté, si oui il faut vérif s'il appartient à la collectivité (sinon on le dégage à coups de pied au cul)
            if ($this->authorization->isGranted('ROLE_USER')) {
                // la restriction ne concerne pas les super_admin
                if (!$this->authorization->isGranted('ROLE_SUPER_ADMIN')) {
                    $user = $this->context->getToken()->getUser();
                    //@todo refactor $user->getCollectivite()
                    if($user->getCollectivite() != $collectivite) {

                        // Construction de la nouvelle URL
                        $new_url = 'http://' . $user->getCollectivite()->getDomain();
                        foreach ($sousdom as $key => $sousdo) {
                            if ($key != 0) { $new_url .=  "." . $sousdo; }
                        }

                        // redirection vers la nouvelle URL
                        $response =  new RedirectResponse($new_url);
                        $event->setResponse($response);

                        $session->set('nocoll', true);
                        /*   $session->getFlashBag()->add(
                               'success',
                               "Merci pour votre connexion. Votre compte sera opérationnel après activation par l'administrateur de SESILE"
                           ); */
                    }
                }
                else {
                    $session->set('nocoll', false);
                }
            }
        } else {
            $session->set('nocoll', false);
            $session->getFlashBag()->add(
                'error',
                "Aucune collectivité ne correspond à votre requête"
            );
        }
    }
}