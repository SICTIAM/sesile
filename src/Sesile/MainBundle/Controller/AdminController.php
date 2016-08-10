<?php

namespace Sesile\MainBundle\Controller;

use Sesile\CircuitBundle\Controller\CircuitController;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\MainBundle\Form\CollectiviteType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Yaml\Yaml;
use Sesile\MainBundle\Classe\OvhApi;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;

class AdminController extends Controller
{
    /**
     * @Route("/preferences/message", name="message_accueil")
     * @Template("SesileMainBundle:Preferences:message_accueil.html.twig")
     *
     */
    public function messageAccueilAction(Request $request) {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            // Sinon on déclenche une exception « Accès interdit »
            return $this->render('SesileMainBundle:Default:errorrestricted.html.twig');
        }
        $em = $this->getDoctrine()->getManager();
        $coll = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));
        $msg_accueil = $coll->getMessage();


        if ($request->isMethod('POST')) {
            $msg_accueil = $request->request->get('message');
            $coll->setMessage($msg_accueil);
            $em->flush();
        }

        return array('message' => $msg_accueil, "menu_color" => "vert");
    }

    /**
     * @Route("/preferences/notifications", name="notifications")
     * @Template("SesileMainBundle:Preferences:notifications.html.twig")
     */
    public function notificationsAction(Request $request) {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            // Sinon on déclenche une exception « Accès interdit »
            return $this->render('SesileMainBundle:Default:errorrestricted.html.twig');
        }
        $em = $this->getDoctrine()->getManager();
        $coll = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));
        $txtrefuse = $coll->getTextmailrefuse();
        $txtwalid = $coll->getTextmailwalid();
        $txtnew = $coll->getTextmailnew();

        if ($request->isMethod('POST')) {
            $txtrefuse = $request->request->get('textmailrefuse');
            $txtwalid = $request->request->get('textmailwalid');
            $txtnew = $request->request->get('textmailnew');
            $coll->setTextmailrefuse($txtrefuse);
            $coll->setTextmailwalid($txtwalid);
            $coll->setTextmailnew($txtnew);
            $em->flush();
        }

        return array('textmailrefuse' => $txtrefuse, 'textmailwalid' => $txtwalid, 'textmailnew' => $txtnew, "menu_color" => "vert");
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
        return array("collectivites" => $collectivites, "menu_color" => "vert");
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
            $this->get('session')->getFlashBag()->add(
                'success',
                "La collectivité a été créée avec succès, elle sera accessible sous 24 heures"
            );

            /**
             * on crée le CNAME chez OVH en utilisant l'api ovh et la surcouche (clase ovhapi)
             */

            $ovh = (object)$this->container->getParameter('ovh');
            $ovhCredential = (object)$this->container->getParameter('ovh_credential');
            $api = new OvhApi(OVH_API_EU,$ovhCredential->application,$ovhCredential->secret,$ovhCredential->consumer_key);
            $post = new \stdClass();
            $post->fieldType = 'CNAME';
            if($ovh->environnement == "")
            {
                $post->subDomain = $form->get('domain')->getData();
                $environnement = "SICTIAM";
            }
            else{
                $post->subDomain = $form->get('domain')->getData().'.'.$ovh->environnement;
                $environnement = $ovh->environnement;
            }

            $post->target = $ovh->target;
            $post->ttl = 60;
            $api->post('/domain/zone/'.$ovh->zone.'/record',$post);

            $user = $this->container->get('security.context')->getToken()->getUser();

            /**
             * on prévient les devs
             */
            $message = \Swift_Message::newInstance()
                ->setSubject('Nouvelle Collectivité créée')
                ->setFrom('sesile@sictiam.fr')
                ->setTo('internet@sictiam.fr')
                ->setBody("La collectivité ".$form->get('nom')->getData()." vient d'être créée dans SESILE merci d'ajouter l'adresse ".$post->subDomain.".".$ovh->zone." dans vProxymus. \n\n\n" .
                        "La collectivité a été créée par " . $user->getPrenom() . " " . $user->getNom(). " " . $user->getEmail() . " pour l'envirronement : " . $environnement)
                ->setContentType('text/html');
            $this->get('mailer')->send($message);
            return $this->redirect($this->generateUrl('index_collectivite'));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            "menu_color" => "vert"
        );


    }


    /**
     * Displays a form to edit an existing Collectivite entity.
     *
     * @Route("/collectivite/edit/{id}/", name="edit_collectivite", options={"expose"=true})
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
            "menu_color" => "vert"
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
            $entity->setTextmailrefuse($editForm->get('textmailrefuse')->getData());
            $entity->setTextmailwalid($editForm->get('textmailwalid')->getData());
            $entity->setTextmailnew($editForm->get('textmailnew')->getData());
            $entity->setMessage($editForm->get('message')->getData());

            if ($editForm->get('file')->getData()) {
                if ($entity->getFile()) {
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
            "menu_color" => "vert"
        );
    }

    /**
     * Delete a Collectivite entity.
     *
     * @Route("/collectivite/{id}", name="delete_collectivite")
     * @Method("GET")
     */
    public function deleteCollectiviteAction($id)
    {

        // Test pour savoir si le user c est pas égaré...
        if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->render('SesileMainBundle:Default:errorrestricted.html.twig');
        }
        /*
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
        */
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($id);

        // Si la collectivité n existe pas
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Collectivite entity.');
        }

        // Si il y a des utilisateurs dans la collectivité alors on retourne les utilisateurs
        if (count($entity->getUsers()) != 0) {

            $this->get('session')->getFlashBag()->add('error', 'Suppression impossible : des utilisateurs sont liés à la colectivité');
            foreach ($entity->getUsers() as $user) {
                $this->get('session')->getFlashBag()->add('warning', "L'utilisateur " . $user->getPrenom() . $user->getNom() . " est présent dans la colectivité");
            }
            return $this->redirect($this->generateUrl('index_collectivite'));
        }

        if ($entity->getImage()) {
            $entity->removeUpload();
        }

        $em->remove($entity);
        $em->flush();
        //}
        $this->get('session')->getFlashBag()->add(
            'success',
            'La collectivité a bien été supprimée'
        );

        return $this->redirect($this->generateUrl('index_collectivite'));
    }


    private function createCreateForm(Collectivite $entity)
    {
        $form = $this->createForm(new CollectiviteType(), $entity, array(
            'action' => $this->generateUrl('new_collectivite'),
            'method' => 'POST',
        ));
        $form->add('domain', 'text', array("label" => "Domaine"));
        $form->add('submit', 'submit', array('label' => 'Enregistrer'));
        return $form;
    }


    /**
     * Creates a form to edit a Collectivite entity.
     * @param Collectivite $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Collectivite $entity) {
        $form = $this->createForm(new CollectiviteType(), $entity, array(
            'action' => $this->generateUrl('update_collectivite', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        $form->add('domain', 'text', array("label" => "Domaine","disabled"=>true));
        $form->add('submit', 'submit', array('label' => 'Enregistrer'));
        return $form;
    }

    /**
     * Creates a form to delete a Collectivite entity by id.
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('delete_collectivite', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Supprimer'))
            ->getForm();
    }


    /**
     * Liste des collectivités
     *
     * @Route("/mailing", name="index_emailing")
     * @Template("")
     */
    public function indexMailingAction(Request $request) {

        if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->render('SesileMainBundle:Default:errorrestricted.html.twig');
        }

        // Creation du formulaire pour la saisie des informations
        $defaultData = array('message' => 'Taper votre message');
        $form = $this->createFormBuilder($defaultData)
            ->add('sujet', 'text')
            ->add('mailMessage', 'textarea', array('label' => "Corps du message", 'required' => true))
            ->add('submit', 'submit', array('label' => 'Envoyer à tous les utilisateurs de l\'instance'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            // Les données sont un tableau avec les clés "sujet", et "mailMessage"
            $em = $this->getDoctrine()->getManager();
            // On recupere tous les utilisateurs
            $users = $em->getRepository('SesileUserBundle:User')->findByEnabled(true);
            // On recupère les données du formulaire
            $data = $form->getData();

            // Création du mail
            $message = \Swift_Message::newInstance()
                ->setSubject($data['sujet'])
                //->setFrom('sesile@sictiam.fr')
                ->setFrom($this->container->getParameter('email_sender_address'))
                ->setBody($data['mailMessage'])
                ->setContentType('text/html');

            // Init du message d info
            $errorsString = "";
            // Envoie du mail pour chaque utilisateur
            foreach ($users as $key => $user) {

                // On recupere l email de l utilisateur
                $email = $user->getEmail();
                $emailConstraint = new EmailConstraint();
                $emailConstraint->message = "L'adresse email " . $email . " n'est pas valide.";

                // On teste si l email est valide
                $errors = $this->get('validator')->validateValue(
                    $email,
                    $emailConstraint
                );

                // Si on a des erreurs paf le chien !
                if (count($errors) > 0) {
                    /*
                     * Uses a __toString method on the $errors variable which is a
                     * ConstraintViolationList object. This gives us a nice string
                     * for debugging
                     */
                    $errorsString .= (string) $errors;
                    // Message d info pour l'utilisateur
                    $request->getSession()->getFlashBag()->add('error',$errorsString);

                }
                // Sinon on envoie le mail
                else {
                    $message->setTo($email);
                    $this->get('mailer')->send($message);
                }
            }

            // Message de confirmation pour l'utilisateur
            $request->getSession()->getFlashBag()->add('success', "L'emailing a été envoyé avec succès.");

        }

        return array('form' => $form, "menu_color" => "vert");


    }
}