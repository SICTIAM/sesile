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
 *
 */
class TypeClasseurController extends Controller
{
    /**
     * Liste des types
     *
     * @Route("/types", name="liste_type_classeur")
     * @Method("GET")
     * @Template("SesileClasseurBundle:TypeClasseur:index.html.twig")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('SesileClasseurBundle:TypeClasseur')->findAll();
        return array(
            'types' => $entities,
        );
    }

    /**
     * Creates a new type
     *
     * @Route("/types/new", name="type_classeur_create")
     * @Method("POST")
     *
     */
    public function createAction(Request $request)
    {
        /*
                if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
                    // Sinon on déclenche une exception « Accès interdit »
                    return $this->render('SesileMainBundle:Default:errorrestricted.html.twig');
                }*/
        $entity = new TypeClasseur();
        $form->handleRequest($request);


        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('liste_type_classeur'));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Classeur entity.
     *
     * @Route("/{id}", name="ajout_type_classeur")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SesileClasseurBundle:Classeur')->find($id);
        $isSignable = $entity->isSignable($em);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Classeur entity.');
        }

        $d = $em->getRepository('SesileUserBundle:User')->find($entity->getUser());
        $deposant = array("id" => $d->getId(), "nom" => $d->getPrenom() . " " . $d->getNom(), "path" => $d->getPath());
        $validant = $entity->getvalidant();

        return array(
            'deposant' => $deposant,
            'validant' => $validant,
            'classeur' => $entity,
            'retractable' => $entity->isRetractable($this->getUser()->getId(), $em),
            'signable' => $isSignable
        );
    }

    /**
     * Edits an existing Classeur entity.
     *
     * @Route("/update_classeur", name="classeur_update")
     * @Method("POST")
     */
    public function updateAction(Request $request)
    {

        var_dump($request->request->get('circuit'));
        $em = $this->getDoctrine()->getManager();

        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->find($request->request->get('id'));


        if (!$classeur) {
            throw $this->createNotFoundException('Classeur introuvable.');
        }

        $classeur->setNom($request->request->get('name'));
        $classeur->setDescription($request->request->get('desc'));

        list($d, $m, $a) = explode("/", $request->request->get('validation'));
        $valid = new \DateTime($m . "/" . $d . "/" . $a);
        $classeur->setValidation($valid);
        $circuit = $request->request->get('circuit');
        $classeur->setCircuit($circuit);
        $classeur->setUser($this->getUser()->getId());
        $classeur->setVisibilite(1);
        $em->persist($classeur);
        $em->flush();

        $action = new Action();
        $action->setClasseur($classeur);
        $action->setUser($this->getUser());
        $action->setAction("Modification du classeur");
        $em->persist($action);
        $em->flush();

        /**
         * TODO modifier le fonctionnement : on doit updater les users par la collection et non par suppression / rajout (un peu de propreté qd même!!!)
         */
        // gestion du circuit
        $users = explode(',', $circuit);
        $classeurUserObj = $em->getRepository("SesileClasseurBundle:ClasseursUsers");

        for ($i = 0; $i < count($users); $i++) {
            $userObj = $em->getRepository("SesileUserBundle:User")->findOneById($users[$i]);
            $cu = $classeurUserObj->findOneBy(array("user" => $userObj, "classeur" => $classeur));

            $classeurUser = new ClasseursUsers();
            $classeurUser->setClasseur($classeur);
            $classeurUser->setUser($userObj);
            $classeurUser->setOrdre($i + 1);
            $em->persist($classeurUser);
        }

        $em->flush();
        $classeurUserObj->deleteClasseurUser($classeur, $circuit);
        $error = false;
        if (!$error) {
            $this->get('session')->getFlashBag()->add(
                'success',
                'Classeur modifié avec succès !'
            );
        }

        return $this->redirect($this->generateUrl('classeur'));
    }

    /**
     * Creates a form to create a User entity.
     *
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(User $entity)
    {
        $form = $this->createForm(new TypeClasseurType(), $entity, array(
            'action' => $this->generateUrl('ajout_type_classeur'),
            'method' => 'POST',
        ));
        $form->add('submit', 'submit', array('label' => 'Enregistrer'));
        return $form;
    }
}