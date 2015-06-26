<?php

namespace Sesile\MainBundle\EventListener;

use Sesile\MainBundle\Entity\Collectivite;
use Sesile\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\SecurityContext;

class RouteListener {
    private $em = null;
    private $context = null;
    private $container = null;

    public function __construct(\Doctrine\ORM\EntityManager $em, SecurityContext $context, ContainerInterface $container) {
        $this->em = $em;
        $this->context = $context;
        $this->container = $container;
    }

    public function onDomainParse(Event $event) {
        if($this->context->getToken() == null) {
            return;
        }

        $request = $event->getRequest();

        $sousdom = explode(".", $request->getHost());
        $conf = $this->container->getParameter("domain_parse");
        $sousdom = $sousdom[0] != $conf["default"] ? $sousdom[0] : $conf["dbname"];

        $collectivite = $this->em->getRepository('SesileMainBundle:Collectivite')->findOneBy(
            array("domain" => $sousdom, "active" => 1)
        );

        $session = $request->getSession();
        if($collectivite instanceof Collectivite) {
            $session->set('collectivite', $collectivite->getId());
            $session->set('logo', $collectivite->getImage());

            // check si le user est connecté, si oui il faut vérif s'il appartient à la collectivité (sinon on le dégage à coups de pied au cul)
            if ($this->context->isGranted('ROLE_USER')) {
                // la restriction ne concerne pas les super_admin
                if (!$this->context->isGranted('ROLE_SUPER_ADMIN')) {
                    $user = $this->context->getToken()->getUser();
                    if($user->getCollectivite() != $collectivite) {
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