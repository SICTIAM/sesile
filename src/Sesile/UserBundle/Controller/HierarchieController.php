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
        $entities = $em->getRepository('SesileUserBundle:Groupe')->findByType(0);

        return array(
            'groupes' => $entities
        );
    }

    /**
     * @Route("/groupe/new", name="create_groupe")
     * @Method("GET")
     * @Template("SesileUserBundle:Hierarchie:edit.html.twig")
     */
    public function createAction()
    {
        // recup la liste des users en base
        $userManager = $this->container->get('fos_user.user_manager');
        $users = $userManager->findUsers();
        return array("users" => $users);
    }

    /**
     * @Route("/groupe/new", name="new_groupe")
     * @Method("POST")
     */
    public function newAction(Request $request)
    {
        $group = new Groupe();
        $group->setNom($request->request->get('nom'));
        $group->setCollectivite(1);
        $group->setJson($request->request->get('tree'));
        $group->setCouleur("white");
        //$group->setType(0);
        $em = $this->getDoctrine()->getManager();
        $em->persist($group);
        $em->flush();

        return $this->redirect($this->generateUrl('groupes'));
    }

    /**
     * @Route("/groupe/edit/{id}", name="groupe_edit", options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();
        $groupe = $em->getRepository('SesileUserBundle:Groupe')->find($id);
        if($groupe) {
            // recup la liste des users en base
            $userManager = $this->container->get('fos_user.user_manager');
            $users = $userManager->findUsers();

            return array (
                'users' => $users,
                'groupe' => $groupe
            );
        }
        else {
            return $this->redirect($this->generateUrl('groupes'));
        }
    }

    /**
     * @Route("/groupe/update/", name="update_groupe")
     * @Method("POST")
     * @Template()
     */
    public function updateAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $groupe = $em->getRepository('SesileUserBundle:Groupe')->find($request->request->get('id'));
        if($groupe) {
            $groupe->setNom($request->request->get('nom'));
            $groupe->setJson($request->request->get('tree'));
            $em->flush();
            return $this->redirect($this->generateUrl('groupes'));
        }
        else {
            // TODO pétage d'erreur
            //return $this->redirect($this->generateUrl('groupes'));
        }
    }


    /**
     * @Route("/organigramme", name="organigramme")
     * @Method("GET")
     * @Template("SesileUserBundle:Hierarchie:edit.html.twig")
     */
    public function organigrammeAction() {
        // recup la liste des users en base
        $userManager = $this->container->get('fos_user.user_manager');
        $users = $userManager->findUsers();

        $em = $this->getDoctrine()->getManager();
        $groupe = $em->getRepository('SesileUserBundle:Groupe')->findOneByType(1);
        return array("users" => $users, "organigramme" => 1, "groupe" => $groupe);
    }
}