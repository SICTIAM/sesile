<?php

namespace Sesile\MainBundle\EventListener;

use Sesile\MainBundle\Entity\Collectivite;
use Sesile\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\SecurityContext;

class RouteListener {
    private $em = null;
    private $context = null;

    public function __construct(\Doctrine\ORM\EntityManager $em, SecurityContext $context) {
        $this->em = $em;
        $this->context = $context;
    }

    public function onDomainParse(Event $event) {
        if($this->context->getToken() == null) {
            return;
        }

        $request = $event->getRequest();

        $sousdom = explode(".", $request->getHost());
        $sousdom = $sousdom[0] != "sesile"?$sousdom[0]:"sictiam";

        $collectivite = $this->em->getRepository('SesileMainBundle:Collectivite')->findOneBy(array("domain" => $sousdom, "active" => 1));
        if($collectivite instanceof Collectivite) {
            $session = $request->getSession();
            $session->set('collectivite', $collectivite->getId());
            $session->set('logo', $collectivite->getImage());

            // check si le user est connecté, si oui il faut vérif s'il appartient à la collectivité (sinon on le dégage à coups de pied au cul)
            if ($this->context->isGranted('ROLE_USER')) {
                // la restriction ne concerne pas les super_admin
                if (!$this->context->isGranted('ROLE_SUPER_ADMIN')) {
                    $user = $this->context->getToken()->getUser();
                    if($user->getCollectivite() != $collectivite) {
                        throw new NotFoundHttpException("Vous n'appartenez pas à la collectivité sélectionnée");
                    }
                }
            }

        } else {
            throw new NotFoundHttpException("La collectivité sélectionnée n'existe pas");
        }
    }
}