<?php

namespace Sesile\UserBundle\Controller;

use Sesile\UserBundle\Entity\Groupe;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;


class HierarchieController extends Controller
{

    /**
     * @Route("/groupes", name="groupes")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('SesileUserBundle:Groupe');

        return array(
            'groupes' => $entities
        );
    }

    /**
     * @Route("/hierarchy/new", name="create_hierarchy")
     * @Method("GET")
     * @Template()
     */
    public function createAction()
    {
        // recup la liste des users en base
        $userManager = $this->container->get('fos_user.user_manager');
        $users = $userManager->findUsers();
        return array("users" => $users);
    }

    /**
     * @Route("/hierarchy/new", name="new_hierarchy")
     * @Method("POST")
     */
    public function newAction(Request $request)
    {
        $groupe_nom = $request->request->get('nom_groupe');
        $group = new Groupe();
        $group->setNom($groupe_nom);
        $group->setCollectivite(1);
        $group->setJson($request->request->get('tree'));
        $group->setCouleur("white");
        $em = $this->getDoctrine()->getManager();
        $em->persist($group);
        $em->flush();

        return $this->redirect($this->generateUrl('groupes'));
    }


}