<?php

namespace Sesile\DelegationsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="delegations_list")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->getUser()->getDelegations();
        return array(
            'delegations' => $entities,
        );
    }

    /**
     * @Route("/ajout", name="delegation_ajouter")
     * @Template()
     */
    public function ajoutAction()
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $users = $userManager->findUsers();
        foreach ($users as &$user) {
            if ($user->getId() == $this->getUser()->getId()) {

            }
        }
        return array("users" => $users);
    }
}
