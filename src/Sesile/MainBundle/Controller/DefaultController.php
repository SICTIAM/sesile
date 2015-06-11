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
        $msg_err = 'vous n\'êtes pas connecté à l\'interface SESILE de votre collectivité de rattachement';
        if (is_object($this->getUser()) && $this->get('session')->get('nocoll')) {
            //var_dump($this->get('session')->get('nocoll'));
            return array('msg_err' => $msg_err);
        }


        #TODO tester en prod acces autre collec user
        return array('msg_acc' => $msg_accueil);
        //test

    }

    /**
     * @Route("/apropos",name="apropos")
     * @Template()
     */
    public function aproposAction()
    {
        $tabversion = $this->container->getParameter('build');
        $major = $this->container->getParameter('majorversion');

        return array('majorversion' => $major, 'commit' => $tabversion['commit'], 'buildnumber' => $tabversion['buildnumber']);
    }

}
