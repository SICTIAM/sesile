<?php

namespace Sesile\ClasseurBundle\Controller;

use Sesile\ClasseurBundle\Entity\ClasseursUsers;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\DocumentBundle\Entity\Document;
use Sesile\ClasseurBundle\Form\ClasseurType;
use Sesile\ClasseurBundle\Entity\Action;

/**
 * Classeur controller.
 *
 */
class ClasseurController extends Controller
{
    /**
     * Page qui affiche la liste des classeurs visibles pour le user connecté.
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
        $entities = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->getClasseursVisibles($this->getUser()->getId());
        foreach ($entities as $key => $value) {
            $user = $em->getRepository('SesileUserBundle:User')->findOneById($value->getValidant());
            $value->validantName = $user->getPrenom() . " " . $user->getNom();
        }

        return array(
            'classeurs' => $entities,
        );
    }

    /**
     * Page qui affiche la liste des classeurs à valider pour le user connecté.
     *
     * @Route("/liste-a-valider", name="index_valider")
     * @Method("GET")
     * @Template("SesileClasseurBundle:Classeur:a_valider.html.twig")
     */
    public function indexAValiderAction()
    {
        return $this->aValiderAction();
    }

    /**
     * Liste des classeurs à valider
     *
     * @Route("/a_valider", name="classeur_a_valider")
     * @Method("GET")
     * @Template("SesileClasseurBundle:Classeur:liste.html.twig")
     */
    public function aValiderAction()
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('SesileClasseurBundle:Classeur')->findBy(
            array(
                "validant" => $this->getUser() ? $this->getUser()->getId() : 0,
                "status" => 1
            ));

        return array(
            'classeurs' => $entities
        );
    }

    /**
     * Page qui affiche la liste des classeurs retractables pour le user connecté.
     *
     * @Route("/liste-retractables", name="index_retractables")
     * @Method("GET")
     * @Template("SesileClasseurBundle:Classeur:a_retracter.html.twig")
     */
    public function indexARetracterAction()
    {
        return $this->retractationAction();
    }

    /**
     * Liste des classeurs retractables
     *
     * @Route("/a_retracter", name="classeur_a_retracter")
     * @Method("GET")
     * @Template("SesileClasseurBundle:Classeur:liste.html.twig")
     */
    public function retractationAction()
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->getClasseursRetractables($this->getUser()->getId());
        return array(
            'classeurs' => $entities,
        );
    }

    /**
     * Creates a new Classeur entity.
     *
     * @Route("/", name="classeur_create")
     * @Method("POST")
     *
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $classeur = new Classeur();
        $classeur->setNom($request->request->get('name'));
        $classeur->setDescription($request->request->get('desc'));
        list($d, $m, $a) = explode("/", $request->request->get('validation'));
        $valid = new \DateTime($m . "/" . $d . "/" . $a);
        $classeur->setValidation($valid);
        $classeur->setType($request->request->get('type'));
        $circuit = $request->request->get('circuit');
        $classeur->setCircuit($circuit);
        $classeur->setUser($this->getUser()->getId());

        $classeur->setVisibilite($request->request->get('visibilite'));
        $em->persist($classeur);


        $em->flush();

        // enregistrer les users du circuit
        $users = explode(',', $circuit);

        for ($i = 0; $i < count($users); $i++) {
            $classeurUser = new ClasseursUsers();
            $classeurUser->setClasseur($classeur);

            $userObj = $em->getRepository("SesileUserBundle:User")->findOneById($users[$i]);
            $classeurUser->setUser($userObj);
            $classeurUser->setOrdre($i + 1);
            $em->persist($classeurUser);
        }
        $em->flush();

        $action = new Action();
        $action->setClasseur($classeur);
        $action->setUser($this->getUser());
        $action->setAction("Dépot du classeur");
        $em->persist($action);
        $em->flush();

        //Sauvegarde des enregistrements
        $manager = $this->container->get('oneup_uploader.orphanage_manager')->get('docs');
        $files = $manager->uploadFiles();

        foreach ($files as $file) {
            //Suppression des fichiers provenant du dossier de session par erreur et ne devant pas être sauvegardés
            if ($request->request->get(str_replace(".", "_", $file->getBaseName())) == null) {
                unlink($file->getPathname());
            } else { // Pas d'erreur, on crée un document correspondant
                $document = new Document();
                $document->setName($request->request->get(str_replace(".", "_", $file->getBaseName())));
                $document->setRepourl($file->getBaseName()); //Temporairement associé au nom du fichier en attendant les repository git
                $document->setType($file->getMimeType());
                $document->setSigned(false);
                $document->setClasseur($classeur);
                $em->persist($document);
                $em->flush();
                $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($document, "Ajout du document au classeur", null);


            }
        }

        // $respDocument = $this->forward( 'sesile.document:createAction', array('request' => $request));

        // envoi d'un mail au premier validant
        $this->sendCreationMail($classeur);

        // TODO envoi du mail au déposant et aux autres personnes du circuit ?


        $error = false; /*
        if($respCircuit->getContent()!='OK') {
            $this->get('session')->getFlashBag()->add(
                'error',
                'Erreur de création du circuit'
            );
            $error = true;
        }

        if($respDocument->getContent()!='OK'){
            $this->get('session')->getFlashBag()->add(
                'error',
                'Erreur de création du circuit'
            );
            $error=true;
        }
*/
        if (!$error) {
            $this->get('session')->getFlashBag()->add(
                'success',
                'Classeur créé avec succès !'
            );
        }

        return $this->redirect($this->generateUrl('classeur'));
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
        return array();
    }

    /**
     * Displays a form to edit an existing Classeur entity.
     *
     * @Route("/{id}", name="classeur_edit", options={"expose"=true})
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
     * Edits an existing Classeur entity.
     *
     * @Route("/valider", name="classeur_valider")
     * @Method("POST")
     *
     */
    public function validerAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->find($request->get("id"));

        if (!$classeur) {
            throw $this->createNotFoundException('Unable to find Classeur entity.');
        }

        $classeur->valider($em);
        $em->persist($classeur);
        $em->flush();

        $action = new Action();
        $action->setClasseur($classeur);
        $action->setUser($this->getUser());
        $action_libelle = ($classeur->getValidant() == 0) ? "Classeur finalisé" : "Validation";
        $action->setAction($action_libelle);
        $em->persist($action);
        $em->flush();

        // envoi d'un mail validant suivant
        $this->sendValidationMail($classeur);

        $this->updateAction($request);

        return $this->redirect($this->generateUrl('classeur_edit', array('id' => $classeur->getId())));
    }

    /**
     * refuser  an existing Classeur entity.
     *
     * @Route("/refuser", name="classeur_refuser")
     * @Method("POST")
     *
     */
    public function refuserAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->find($request->get("id"));
        if (!$classeur) {
            throw $this->createNotFoundException('Unable to find Classeur entity.');
        }
        $classeur->refuser();
        $em->persist($classeur);
        $em->flush();

        $action = new Action();
        $action->setClasseur($classeur);
        $action->setUser($this->getUser());
        $action->setAction("Refus");
        $em->persist($action);
        $em->flush();

        // envoi d'un mail validant suivant
        $this->sendRefusMail($classeur);

        $this->updateAction($request);

        return $this->redirect($this->generateUrl('classeur_edit', array('id' => $classeur->getId())));
    }

    /**
     * Valider_et_signer an existing Classeur entity.
     *
     * @Route("/valider_et_signer", name="classeur_valider_et_signer")
     * @Method("POST")
     *
     */
    public function valider_et_signerAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->find($request->get("id"));
        if (!$classeur) {
            throw $this->createNotFoundException('Unable to find Classeur entity.');
        }
        $classeur->valider($em);
        $em->persist($classeur);
        $em->flush();

        $action = new Action();
        $action->setClasseur($classeur);
        $action->setUser($this->getUser());
        $action->setAction("Signature");
        $em->persist($action);
        $em->flush();

        $this->updateAction($request);
        return $this->redirect($this->generateUrl('classeur_edit', array('id' => $classeur->getId())));
    }

    /**
     * Valider_et_signer an existing Classeur entity.
     *
     * @Route("/signform/{id}", name="signform")
     * @Template()
     *
     */
    public function signAction(Request $request, $id)
    {

        $user = $this->get('security.context')->getToken()->getUser();
        //  var_dump($user);

        $em = $this->getDoctrine()->getManager();
        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->findOneById($id);


        $session = $this->get('session');
        $session->start();

        $tmpdocs = $classeur->getXmlDocuments();

        $docstosign = array();

        foreach ($tmpdocs as $key => $value) {
            $tmpdo = array();
            $tmpdo['name'] = $value->getName();
            $tmpdo['id'] = $value->getId();
            $tmpdo['repourl'] = $value->getRepourl();
            $docstosign[$key] = $tmpdo;
        }

        $servername = $_SERVER['HTTP_HOST'];

        return array('user' => $user, 'classeur' => $classeur, 'session_id' => $session->getId(), 'docstosign' => $docstosign, 'servername' => $servername);

    }

    /**
     * retracter an existing Classeur entity.
     *
     * @Route("/retracter", name="classeur_retracter")
     * @Method("POST")
     *
     */
    public function retracterAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->find($request->get("id"));
        if (!$classeur) {
            throw $this->createNotFoundException('Unable to find Classeur entity.');
        }

        $classeur->retracter($this->getUser()->getId());
        $em->persist($classeur);
        $em->flush();

        $action = new Action();
        $action->setClasseur($classeur);
        $action->setUser($this->getUser());
        $action->setAction("Rétractation");
        $em->persist($action);
        $em->flush();

        return $this->redirect($this->generateUrl('classeur_edit', array('id' => $classeur->getId())));
    }

    /**
     * Deletes a Classeur entity.
     *
     * @Route("/supprimer", name="classeur_supprimer")
     * @Method("POST")
     */
    public function supprimerAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->find($request->get("id"));
        if (!$classeur) {
            throw $this->createNotFoundException('Unable to find Classeur entity.');
        }
        $classeur->supprimer();
        $em->persist($classeur);
        $em->flush();

        $action = new Action();
        $action->setClasseur($classeur);
        $action->setUser($this->getUser());
        $action->setAction("Classeur retiré");
        $em->persist($action);
        $em->flush();

        return $this->redirect($this->generateUrl('classeur_edit', array('id' => $classeur->getId())));
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

        switch ($type) {
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

    /*                MAILS DE NOTIFICATION                      */

    private function sendMail($sujet, $to, $body)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($sujet)
            ->setFrom('sesile@sictiam.fr')
            ->setTo($to)
            ->setBody($body, "text/html");
        $this->get('mailer')->send($message);
    }

    private function sendValidationMail($classeur)
    {
        $body = $this->renderView('SesileClasseurBundle:Mail:valide.html.twig',
            array(
                'validant' => $this->getUser(),
                'titre_classeur' => $classeur->getNom(),
                'date_limite' => $classeur->getValidation(),
                "lien" => $this->generateUrl('classeur_edit', array('id' => $classeur->getId()))
            )
        );

        $em = $this->getDoctrine()->getManager();
        $validant_obj = $em->getRepository('SesileUserBundle:User')->find($classeur->getValidant());

        if ($validant_obj != null) {
            $this->sendMail("SESILE - Nouveau classeur à valider", $validant_obj->getEmail(), $body);
        }
    }

    private function sendCreationMail($classeur)
    {
        $body = $this->renderView('SesileClasseurBundle:Mail:nouveau.html.twig',
            array(
                'deposant' => $classeur->getUser(),
                'titre_classeur' => $classeur->getNom(),
                'date_limite' => $classeur->getValidation(),
                "lien" => $this->generateUrl('classeur_edit', array('id' => $classeur->getId()))
            )
        );

        $em = $this->getDoctrine()->getManager();
        $validant_obj = $em->getRepository('SesileUserBundle:User')->find($classeur->getValidant());

        if ($validant_obj != null) {
            $this->sendMail("SESILE - Nouveau classeur à valider", $validant_obj->getEmail(), $body);
        }
    }

    private function sendRefusMail($classeur)
    {
        $body = $this->renderView('SesileClasseurBundle:Mail:refuse.html.twig',
            array(
                'deposant' => $classeur->getUser(),
                'titre_classeur' => $classeur->getNom(),
                'date_limite' => $classeur->getValidation(),
                "lien" => $this->generateUrl('classeur_edit', array('id' => $classeur->getId()))
            )
        );

        $em = $this->getDoctrine()->getManager();
        $validant_obj = $em->getRepository('SesileUserBundle:User')->find($classeur->getValidant());

        if ($validant_obj != null) {
            $this->sendMail("SESILE - Classeur refusé", $validant_obj->getEmail(), $body);
        }
    }
}