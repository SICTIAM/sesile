<?php

namespace Sesile\UserBundle\Controller;

use Sesile\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\Request;
use Sesile\UserBundle\Form\UserType;
use Symfony\Component\Form\AbstractType;

class DefaultController extends Controller {
    /**
     * @Route("/", name="liste_users")
     * @Template("SesileUserBundle:Default:index.html.twig")
     */
    public function listAction() {


        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();
        return array(
            "users" => $users
        );



    }

    /**
     * @Route("/creation/", name="ajout_user")
     * @Template("SesileUserBundle:Default:ajout.html.twig")
     */
    public function ajoutAction(Request $request) {


        $entity = new User();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            mail($entity->getEmail(),'nouvel utilisateur','bonjour '.$entity->getUsername()."\r\n ".'Bienvenue sur le portail Sesile');
            return $this->redirect($this->generateUrl('liste_users', array('id' => $entity->getId())));
        }


        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );

    }

    /**
     * Displays a form to edit an existing Classeur entity.
     *
     * @Route("/edit/{id}/", name="user_edit", options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SesileUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity');
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
     * Update an existing User entity.
     *
     * @Route("/{id}", name="user_update")
     * @Method("PUT")
     * @Template("SesileUserBundle:Default:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SesileUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        //$deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('user_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a User entity.
     *
     * @Route("/{id}", name="user_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SesileUserBundle:User')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find User entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('classeur'));
    }



    /**
     * Creates a form to create a User entity.
     *
     * @param Classeur $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(User $entity)
    {
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('ajout_user'),
            'method' => 'POST',
        ));

        $form->add('roles', 'choice', array(
            'choices' => array(
                'ROLE_USER' => 'Utilisateurs',
                'ROLE_ADMIN' => 'Admin',
                'ROLE_SUPER_ADMIN' => 'Super admin'
            ),
            'multiple' => true
        ));
        $form->add('submit', 'submit', array('label' => 'Create'));


        return $form;
    }

    /**
     * Creates a form to edit a User entity.
     *
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(User $entity)
    {
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('roles', 'choice', array(
        'choices' => array(
            'ROLE_USER' => 'Utilisateurs',
            'ROLE_ADMIN' => 'Admin',
            'ROLE_SUPER_ADMIN' => 'Super admin'
        ),
        'multiple' => true
    ));
        $form->add('submit', 'submit', array('label' => 'Enregistrer'));

        return $form;
    }

    /**
     * Creates a form to delete a User entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('user_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Supprimer'))
            ->getForm()
            ;
    }


}

