<?php

namespace Sesile\MainBundle\Controller;

use Sesile\CircuitBundle\Controller\CircuitController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {

        return array();
    }

    /**
     * @Route("/accueil", name="accueil")
     * @Template()
     */
    public function accueilAction()
    {
        $bienvenue = "a";
        return array("bienvenue" => $bienvenue);
    }
}
