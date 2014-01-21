<?php

namespace Sesile\MainBundle\Controller;

use Sesile\CircuitBundle\Controller\CircuitController;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\MainBundle\Form\CollectiviteType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Yaml\Yaml;

class AdminController extends Controller
{
    /**
     * @Route("/admin", name="admin")
     *
     *
     */
    public function PageAdminAction(Request $request)
    {
        /*
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            // Sinon on déclenche une exception « Accès interdit »
            return $this->render('SesileMainBundle:Default:errorrestricted.html.twig');
        }
        */

        $Upload = $this->container->getParameter('upload');
        $DocPath = $Upload["msg_acc"];

        $defaultData = array('msg' => 'Type your message here');
        $form = $this->createFormBuilder($defaultData)
            ->add('msg', 'textarea', array('label' => 'Message d\'accueil',))

            ->add('submit', 'submit', array('label' => 'Mettre à jour', 'attr' => array('class' => 'btn btn-success'),))
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->bind($request);


            // $data is a simply array with your form fields
            // like "query" and "category" as defined above.
            $msg = $request->request->get('msg');
            $em = $this->getDoctrine()->getManager();
            $coll = $em->getRepository('SesileMainBundle:Collectivite')->findOneById(1);
            $coll->setMessage($msg);
            $em->flush();
        }

        return $this->render('SesileMainBundle:Default:admin.html.twig', array('form' => $form->createView(),));

    }

    /**
     * Liste des collectivités
     *
     * @Route("/collectivite", name="index_collectivite")
     * @Method("GET")
     * @Template("SesileMainBundle:Collectivite:index.html.twig")
     */
    public function indexCollectiviteAction()
    {
        $em = $this->getDoctrine()->getManager();
        $collectivites = $em->getRepository('SesileMainBundle:Collectivite')->findAll();
        return array("collectivites" => $collectivites);
    }

    /**
     * Création de collectivité
     *
     * @Route("/collectivite/new", name="new_collectivite")
     * @Template("SesileMainBundle:Collectivite:new.html.twig")
     */
    public function ajoutCollectiviteAction(Request $request)
    {
        $upload = $this->container->getParameter('upload');
        $DirPath = $upload['logo_coll'];


        if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->render('SesileMainBundle:Default:errorrestricted.html.twig');
        }


        $entity = new Collectivite();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('index_collectivite'));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView()
        );


    }

    /**
     * Displays a form to edit an existing Collectivite entity.
     *
     * @Route("/collectivite/edit/{id}/", name="edit_collectivite")
     * @Method("GET")
     * @Template("SesileMainBundle:Collectivite:edit.html.twig")
     */
    public function editCollectiviteAction($id)
    {

        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            // Sinon on déclenche une exception « Accès interdit »
            return $this->render('SesileMainBundle:Default:errorrestricted.html.twig');
        }

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SesileMainBundle:Collectivite')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Collectivite entity');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Update an existing User entity.
     *
     * @Route("/collectivite/{id}", name="update_collectivite")
     * @Method("PUT")
     * @Template("SesileUserBundle:Default:edit.html.twig")
     */
    public function updateCollectiviteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SesileMainBundle:Collectivite')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Collectivite entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $entity->setNom($editForm->get('nom')->getData());
            $entity->setDomain($editForm->get('domain')->getData());
            $entity->setActive($editForm->get('active')->getData());

            if ($editForm->get('file')->getData()) {
                if ($entity->getPath()) {
                    $entity->removeUpload();
                }
                $entity->preUpload();
            }
            $em->flush();

            return $this->redirect($this->generateUrl('index_collectivite', array('id' => $id)));
        }
        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Collectivite entity.
     *
     * @Route("/collectivite/{id}", name="delete_collectivite")
     * @Method("DELETE")
     */
    public function deleteCollectiviteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Collectivite entity.');
            }

            if ($entity->getImage()) {
                $entity->removeUpload();
            }

            $em->remove($entity);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('index_collectivite'));
    }


    private function createCreateForm(Collectivite $entity)
    {
        $form = $this->createForm(new CollectiviteType(), $entity, array(
            'action' => $this->generateUrl('new_collectivite'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Enregistrer'));
        return $form;
    }

    /**
     * Creates a form to edit a Collectivite entity.
     * @param Collectivite $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Collectivite $entity)
    {
        $form = $this->createForm(new CollectiviteType(), $entity, array(
            'action' => $this->generateUrl('update_collectivite', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Enregistrer'));

        return $form;
    }

    /**
     * Creates a form to delete a Collectivite entity by id.
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('delete_collectivite', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Supprimer'))
            ->getForm();
    }
}