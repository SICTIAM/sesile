<?php

namespace Sesile\CircuitBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/new/", name="new_circuit", options={"expose"=true})
     * @Template()
     */
    public function newAction()
    {
        return array();
    }
}
