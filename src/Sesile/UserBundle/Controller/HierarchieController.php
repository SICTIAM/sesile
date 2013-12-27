<?php

namespace Sesile\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\SecurityContext;


class HierarchieController extends Controller
{

    /**
    * @Route("/hierarchy/new", name="create_hierarchy")
    * @Method("GET")
    * @Template()
    */
    public function createAction() {
        // recup la liste des users en base
        $userManager = $this->container->get('fos_user.user_manager');
        $users = $userManager->findUsers();
        return array("users" => $users);
    }
}