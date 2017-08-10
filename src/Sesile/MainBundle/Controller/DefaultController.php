<?php

namespace Sesile\MainBundle\Controller;

use Sesile\CircuitBundle\Controller\CircuitController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DefaultController extends Controller
{

    /**
     * @Route("/",name="index")
     * @Template()
     */
    public function appAction()
    {
        return $this->render('app.html.twig');
    }

    /**
     * @Route("/dashboard", options={"expose"=true},name="dashboard")
     * @Template()
     */
    public function dashboardAction()
    {
        return $this->render('app.html.twig');
    }
}
