<?php

namespace Sesile\ClasseurBundle\Controller;

use Sesile\ClasseurBundle\Entity\TypeClasseur;
use Sesile\ClasseurBundle\Form\TypeClasseurType;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Classeur controller.
 * @Route("/types")
 */
class TypeClasseurController extends Controller
{
    /**
     * Liste des types
     *
     * @Route("/list", name="liste_type_classeur")
     * @Method("GET")
     * @Template("SesileClasseurBundle:TypeClasseur:index.html.twig")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('SesileClasseurBundle:TypeClasseur')->findAll();

        return array(
            'types' => $entities,
            "menu_color" => "vert"
        );
    }

    /**
     * Form de création d'un type
     *
     * @Route("/new", name="new_type_classeur")
     * @Template("SesileClasseurBundle:TypeClasseur:newType.html.twig")
     *
     */
    public function newAction(Request $request) {
        /*$em = $this->getDoctrine()->getManager();
        $groupes = $em->getRepository('SesileUserBundle:Groupe')->findByType(0);
        $templates = array(
            array("id" => 1, "nom" => "elclassico")
        );
        return array(
            'groupes' => $groupes,
            'templates' => $templates,
            "menu_color" => "vert"
        );*/

        $typeClasseur = new TypeClasseur();

        $form = $this->createForm(new TypeClasseurType(), $typeClasseur);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($typeClasseur);
            $em->flush();

            // Un petit message pour prévenir que tout va bien
            $request->getSession()->getFlashBag()->add('notice', 'Le nouveau type de classeur a bien été enregistré.');

            // On vide le formulaire
            $typeClasseur = new TypeClasseur();
            $form = $this->createForm(new TypeClasseurType(), $typeClasseur);
        }

        return array(
            "form"       => $form,
            "menu_color" => "vert"
        );

    }

    /**
     * Form de modification d'un type de classeur
     *
     * @Route("/update/{id}", name="update_type_classeur")
     * @Template("SesileClasseurBundle:TypeClasseur:newType.html.twig")
     *
     */
    public function updateTypeAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();
        $typeClasseur = $em->getRepository('SesileClasseurBundle:TypeClasseur')->find($id);

        if (null === $typeClasseur) {
            throw new NotFoundHttpException('Le type de classeur n\'existe pas');
        }

        $form = $this->createForm(new TypeClasseurType(), $typeClasseur);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            //$em->persist($typeClasseur);
            $em->flush();

            // Un petit message pour prévenir que tout va bien
            $request->getSession()->getFlashBag()->add('notice', 'Le type de classeur a bien été modifié.');

            return $this->redirect($this->generateUrl('liste_type_classeur'));

        }

        return array(
            "form"       => $form,
            "menu_color" => "vert"
        );

    }

    /**
     * Suppression d'un type de classeur
     *
     * @Route("/del/{id}", name="delete_classeur_type")
     * @Method("GET")
     * @Template("SesileClasseurBundle:TypeClasseur:index.html.twig")
     */
    public function deleteTypeAction($id) {

        $em = $this->getDoctrine()->getManager();
        $typeClasseur = $em->getRepository('SesileClasseurBundle:TypeClasseur')->find($id);
        if (null === $typeClasseur) {
            throw new NotFoundHttpException('Le type de classeur n\'existe pas');
        }
        $em->remove($typeClasseur);
        $em->flush();

        $this->get('session')->getFlashBag()->add('notice', 'Le type de classeur a bien été supprimé.');

        return $this->redirect($this->generateUrl('liste_type_classeur'));
    }

    /**
     * Enregistre un nouveau classeur
     *
     * @Route("/new", name="create_type_classeur")
     * @Method("POST")
     *
     */
    public function createAction(Request $request) {
        return array();
    }

    /**
     * Creates an input to userGroupe.
     *
     * @Route("/user_groupe/", name="user_groupe_selected", options={"expose"=true})
     * @Method("POST")
     *
     */
    public function userGroupefactory (Request $request) {

        $userGroupe = $request->request->get('usergroupe');

        // Requete many to many pour recuperer les types a partir d un groupe
        $em = $this->getDoctrine()->getManager();
        $groupe = $em->getRepository('SesileUserBundle:Groupe')->findOneById($userGroupe);
        $Types = $groupe->getTypes();

        return $this->render('SesileClasseurBundle:Formulaires:inputuser.html.twig',
            array(
                'types' => $Types
            ));
    }

}