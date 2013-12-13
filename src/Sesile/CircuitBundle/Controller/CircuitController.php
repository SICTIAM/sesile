<?php

namespace Sesile\CircuitBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class CircuitController extends Controller
{
    /**
     * @Route("/new/", name="new_circuit", options={"expose"=true})
     * @Template()
     */
    public function newAction()
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $users = $userManager->findUsers();
        return array('users' => $users);
    }

    /**
     * @Route("/", name="create_circuit")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {


        var_dump($request);exit();


        return new Response('OK');
    }

}
