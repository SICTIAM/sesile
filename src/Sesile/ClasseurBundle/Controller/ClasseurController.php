<?php

namespace Sesile\ClasseurBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\ClasseurBundle\Form\ClasseurType;

/**
 * Classeur controller.
 *
 */
class ClasseurController extends Controller {
    /**
     * Lists all Classeur entities.
     *
     * @Route("/", name="classeur")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        return $this->listeAction();
    }

    /**
     * Liste des classeurs en cours
     *
     * @Route("/liste", name="liste_classeurs")
     * @Method("GET")
     * @Template("SesileClasseurBundle:Classeur:liste.html.twig")
     */
    public function listeAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SesileClasseurBundle:Classeur')->findAll();

        return array(
            'classeurs' => $entities,
        );
    }

    /**
     * Liste des classeurs à valider
     *
     * @Route("/a_valider", name="classeur_a_valider")
     * @Method("GET")
     * @Template("SesileClasseurBundle:Classeur:valider_liste.html.twig")
     */
    public function aValiderAction()
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('SesileClasseurBundle:Classeur')->findBy(
            array(
                "validant" => $this->getUser()?$this->getUser()->getId():0,
                "status" => 1

            ));

        return array(
            'entities' => $entities
        );
    }

    /**
     * Creates a new Classeur entity.
     *
     * @Route("/", name="classeur_create")
     * @Method("POST")
     *
     */
    public function createAction(Request $request) {
        $classeur = new Classeur();
        $classeur->setNom($request->request->get('name'));
        $classeur->setDescription($request->request->get('desc'));
        $classeur->setType($request->request->get('type'));
        $classeur->setCircuit($request->request->get('circuit'));
        // enregistrer les users du circuit
        $users = explode();
        $classeur->addUser();

        $classeur->setUser($this->getUser()->getId());
        $em = $this->getDoctrine()->getManager();
        $em->persist($classeur);
        $em->flush();
        //$respDocument = $this->forward( 'sesile.document:createAction', array('request' => $request));

        $error = false;
        if($respCircuit->getContent()!='OK'){
            $this->get('session')->getFlashBag()->add(
                'error',
                'Erreur de création du circuit'
            );
            $error=true;
        }
        if($respDocument->getContent()!='OK'){
            $this->get('session')->getFlashBag()->add(
                'error',
                'Erreur de création du circuit'
            );
            $error=true;
        }

        if(!$error){
            $this->get('session')->getFlashBag()->add(
                'success',
                'Classeur créé'
            );
        }


        return $this->redirect($this->generateUrl('liste_classeurs'));
    }

    /**
    * Creates a form to create a Classeur entity.
    *
    * @param Classeur $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Classeur $entity)
    {
        $form = $this->createForm(new ClasseurType(), $entity, array(
            'action' => $this->generateUrl('classeur_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Classeur entity.
     *
     * @Route("/new", name="classeur_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Classeur();
        //$form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
           // 'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Classeur entity.
     *
     * @Route("/{id}", name="classeur_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SesileClasseurBundle:Classeur')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Classeur entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Classeur entity.
     *
     * @Route("/{id}/edit", name="classeur_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SesileClasseurBundle:Classeur')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Classeur entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Classeur entity.
    *
    * @param Classeur $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Classeur $entity)
    {
        $form = $this->createForm(new ClasseurType(), $entity, array(
            'action' => $this->generateUrl('classeur_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing Classeur entity.
     *
     * @Route("/{id}", name="classeur_update")
     * @Method("PUT")
     * @Template("SesileClasseurBundle:Classeur:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SesileClasseurBundle:Classeur')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Classeur entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('classeur_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Classeur entity.
     *
     * @Route("/{id}/delete", name="classeur_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SesileClasseurBundle:Classeur')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Classeur entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('classeur'));
    }

    /**
     * Creates a form to delete a Classeur entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('classeur_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    /**
     * Creates a form to edit a Classeur entity.
     *
     * @Route("/new_factory/", name="classeur_new_type", options={"expose"=true})
     * @Method("POST")
     *
     * @return \Symfony\Component\Form\Form The form
     */
    public function formulaireFactory(Request $request)
    {

        $type = $request->request->get('type', 'elclassico');
      //  var_dump($type);


        switch($type){
            case "elclassico":
                return $this->render(
                    'SesileClasseurBundle:Formulaires:elclassico.html.twig'
                );
                break;
            case "elpez":
                return $this->render(
                    'SesileClasseurBundle:Formulaires:elpez.html.twig'
                );
                break;
        }
    }
}