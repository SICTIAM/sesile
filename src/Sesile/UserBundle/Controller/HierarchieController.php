<?php

namespace Sesile\UserBundle\Controller;

use Sesile\UserBundle\Entity\Groupe;
use Sesile\UserBundle\Entity\UserGroupe;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;


class HierarchieController extends Controller {

    /**
     * @Route("/groupes", name="groupes")
     * @Method("GET")
     * @Template()
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('SesileUserBundle:Groupe')->findBy(array(
            "collectivite" => $this->get("session")->get("collectivite"),
            "type" => 0
        ));

        return array(
            'groupes' => $users
        );
    }

    /**
     * @Route("/groupe/new", name="create_groupe")
     * @Method("GET")
     * @Template("SesileUserBundle:Hierarchie:edit.html.twig")
     */
    public function createAction() {
        // recup la liste des users en base
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('SesileUserBundle:User')->findBy(array(
            "collectivite" => $this->get("session")->get("collectivite")
        ));
        return array("users" => $users);
    }

    /**
     * @Route("/groupe/new", name="new_groupe")
     * @Method("POST")
     */
    public function newAction(Request $request) {
        $group = new Groupe();
        $group->setNom($request->request->get('nom'));
        $group->setCollectivite($this->get("session")->get("collectivite"));
        $group->setJson($request->request->get('tree'));
        $group->setType(0);
        $group->setCouleur("white");
        $em = $this->getDoctrine()->getManager();
        $em->persist($group);

        $users = array(); // globale pour la récursive (c'est une porcherie mais ça marche qd meme)
        $users_du_groupe = $this->getUsersFromJson($group->getJson());


        foreach($users_du_groupe as $user) {
            $user_obj = $em->getRepository('SesileUserBundle:User')->find($user["id"]);
            if(is_object($user_obj)) {
                $usergroup = new UserGroupe();
                $usergroup->setUser($user_obj);
                $usergroup->setGroupe($group);
                $usergroup->setParent($user["parent"]);
                $em->persist($usergroup);
            }
        }

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
            $users = $em->getRepository('SesileUserBundle:User')->findBy(array(
                "collectivite" => $this->get("session")->get("collectivite")
            ));

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
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('SesileUserBundle:User')->findBy(array(
            "collectivite" => $this->get("session")->get("collectivite")
        ));

        $groupe = $em->getRepository('SesileUserBundle:Groupe')->findOneByType(1);
        return array("users" => $users, "organigramme" => 1, "groupe" => $groupe);
    }


    /**
     * @param string objet json qui contient une hiérarchie comme renvoyée par les forms
     * @var array
     * @return array un tableau qui contient une liste des users (tableau id => parentid) contenus dans le json
     */
    private function getUsersFromJson($json) {
        global $prec;
        global $users;
        $tree = json_decode($json);
        if(is_object($tree) && $tree->name) {
            $users[] = array("id" => $tree->id, "parent" => is_object($prec)?$prec->id:0);
            $prec = $tree;
            $this->getUsersFromJson(json_encode($tree->children));
        }
        else {
            foreach($tree as $_user) {
                $users[] = array("id" => $_user->id, "parent" => is_object($prec)?$prec->id:0);
                $p_prec = $prec;
                if(property_exists($_user, "children")) {
                    $prec = $_user;
                    $this->getUsersFromJson(json_encode($_user->children));
                }
                $prec = $p_prec;
            }
        }
        return $users;
    }
}