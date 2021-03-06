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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

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

        $typeClasseur = new TypeClasseur();

        $form = $this->createForm(TypeClasseurType::class, $typeClasseur);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($typeClasseur);
            $em->flush();

            // Un petit message pour prévenir que tout va bien
            $request->getSession()->getFlashBag()->add('success', 'Le nouveau type de classeur a bien été enregistré.');

            return $this->redirect($this->generateUrl('liste_type_classeur'));
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
     * @ParamConverter("TypeClasseur", options={"mapping": {"id": "id"}})
     * @Template("SesileClasseurBundle:TypeClasseur:newType.html.twig")
     *
     */
    public function updateTypeAction(Request $request, TypeClasseur $typeClasseur) {

        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(\Sesile\ClasseurBundle\Form\TypeClasseurType::class, $typeClasseur);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $em->flush();

            // Un petit message pour prévenir que tout va bien
            $request->getSession()->getFlashBag()->add('success', 'Le type de classeur a bien été modifié.');

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
     * @ParamConverter("TypeClasseur", options={"mapping": {"id": "id"}})
     * @Template("SesileClasseurBundle:TypeClasseur:index.html.twig")
     */
    public function deleteTypeAction(TypeClasseur $typeClasseur) {

        $em = $this->getDoctrine()->getManager();
        $em->remove($typeClasseur);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Le type de classeur a bien été supprimé.');

        return $this->redirect($this->generateUrl('liste_type_classeur'));
    }

    /**
     * Enregistre un nouveau classeur
     *
     * @Route("/new", name="create_type_classeur")
     * @Method("POST")
     *
     */
    public function createAction() {
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