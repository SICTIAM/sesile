<?php

namespace Sesile\MainBundle\Controller;

use Sesile\CircuitBundle\Controller\CircuitController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller
{
    /**
     * @Route("/",name="index")
     * @Template()
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $CollecEntity = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));
        $msg_accueil = $CollecEntity->getMessage();
        return array('msg_acc' => $msg_accueil);
    }
}
