<?php

namespace Sesile\MainBundle\Controller;

use Sesile\CircuitBundle\Controller\CircuitController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;


class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        $bienvenue = "a";
       $this->redirect('http://sesile.dev.jm/app_dev.php/user/',$status = 302);
        return array("bienvenue" => $bienvenue);
    }


}
