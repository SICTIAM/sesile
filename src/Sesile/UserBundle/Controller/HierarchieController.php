<?php

namespace Sesile\UserBundle\Controller;

use Sesile\ClasseurBundle\Entity\TypeClasseur;
use Sesile\UserBundle\Entity\Groupe;
use Sesile\UserBundle\Entity\UserGroupe;
use Sesile\UserBundle\Form\GroupeType;
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
            "collectivite" => $this->get("session")->get("collectivite")
        ));

        return array(
            'groupes' => $users,
            "menu_color" => "vert"
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
        // Ajout du formulaire pour les types
        $form = $this->createForm(new GroupeType());

        return array(
            "users" => $users,
            "form"  => $form,
            "menu_color" => "vert"
        );
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
//        $group->setType(0);
        $group->setCouleur("white");
        $em = $this->getDoctrine()->getManager();
        $em->persist($group);

        $users = array(); // globale pour la récursive (c'est une porcherie mais ça marche qd meme)
        $users_du_groupe = $this->getUsersFromJson($group->getJson());

        // Enregistrement des données ManyToMany Groupe <=> TypeClasseur
        $typeGroupe = $request->request->get('sesile_userbundle_groupe');
        if (null !== $typeGroupe){
            foreach ($typeGroupe["types"] as $tg){
                $typeClasseur = $em->getRepository('SesileClasseurBundle:TypeClasseur')->findOneById($tg);
                $group->addType($typeClasseur);
                $typeClasseur->addGroupe($group);
            }
            $em->persist($group);
            $em->persist($typeClasseur);
        }

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
                "collectivite" => $this->get("session")->get("collectivite"), 'enabled' => 1
            ));

            $form = $this->createForm(new GroupeType(), $groupe);

            return array (
                'users' => $users,
                'groupe' => $groupe,
                'form'  => $form,
                'menu_color' => 'vert'
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

            // Enregistrement des données ManyToMany Groupe <=> TypeClasseur
            $typeGroupe = $request->request->get('sesile_userbundle_groupe');
            if (null !== $typeGroupe){

                // A lire reference http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/working-with-associations.html#removing-associations
                // On supprime les types deja enregistrés
                $groupe->getTypes()->clear();

                // On ajoute les nouveaux types en BDD
                foreach ($typeGroupe["types"] as $tg){
                    $typeClasseur = $em->getRepository('SesileClasseurBundle:TypeClasseur')->findOneById($tg);

                    $groupe->addType($typeClasseur);
                    $typeClasseur->addGroupe($groupe);
                }

                $em->persist($typeClasseur);
                $em->persist($groupe);
            }

            // suppression des liaisons pour ce groupe
            $hierarchy = $em->getRepository('SesileUserBundle:UserGroupe')->findByGroupe($groupe);
            foreach($hierarchy as $h) {
                $em->remove($h);
            }

            $users = array(); // globale pour la récursive (c'est une porcherie mais ça marche qd meme)
            $users_du_groupe = $this->getUsersFromJson($groupe->getJson());

            foreach($users_du_groupe as $user) {
                $user_obj = $em->getRepository('SesileUserBundle:User')->find($user["id"]);
                if(is_object($user_obj)) {
                    $usergroup = new UserGroupe();
                    $usergroup->setUser($user_obj);
                    $usergroup->setGroupe($groupe);
                    $usergroup->setParent($user["parent"]);
                    $em->persist($usergroup);
                }
            }

            $em->flush();
            return $this->redirect($this->generateUrl('groupes'));
        }
        else {
            // TODO pétage d'erreur
            //return $this->redirect($this->generateUrl('groupes'));
        }
    }


    /**
     * Deletes a Group entity.
     * @Route("/groupe/delete/{id}", name="group_delete")
     * @Method("POST")
     */
    public function deleteAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SesileUserBundle:Groupe')->findOneById($id);

        if (!$entity) {
            throw $this->createNotFoundException('Groupe introuvable');
        }
        $em->remove($entity);
        $em->flush();


        $this->get('session')->getFlashBag()->add(
            "success",
            "Le groupe vient d'être supprimé"
        );
        return $this->redirect($this->generateUrl('groupes'));
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
        $ret = array("users" => $users, "organigramme" => 1);
        $groupe = $em->getRepository('SesileUserBundle:Groupe')->findOneByType(1);
        if(is_object($groupe)) {
            $ret["groupe"] = $groupe;
        }
        return $ret;
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
            if(isset($tree->children)) {
                $this->getUsersFromJson(json_encode($tree->children));
            }
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