<?php

namespace Sesile\ClasseurBundle\Controller;

use Sesile\ClasseurBundle\Entity\TypeClasseur;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Classeur controller.
 * @Route("/types")
 */
class TypeClasseurController extends Controller
{
    /**
     * Liste des types
     *
     * @Route("/test", name="liste_type_classeur")
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
     * @Method("GET")
     * @template()
     *
     */
    public function newAction() {
        $em = $this->getDoctrine()->getManager();
        $groupes = $em->getRepository('SesileUserBundle:Groupe')->findByType(0);
        $templates = array(
            array("id" => 1, "nom" => "elclassico")
        );
        return array(
            'groupes' => $groupes,
            'templates' => $templates,
            "menu_color" => "vert"
        );
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
}