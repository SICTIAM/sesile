<?php

namespace Sesile\ClasseurBundle\Controller;

use Sesile\ClasseurBundle\Entity\ClasseursUsers;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\DocumentBundle\Entity\Document;
use Sesile\UserBundle\Entity\EtapeGroupe;
use Sesile\UserBundle\Entity\EtapeClasseur;
use Sesile\ClasseurBundle\Form\ClasseurType;
use Sesile\ClasseurBundle\Entity\Action;
use Sesile\DelegationsBundle\Entity\Delegations;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

/**
 * Classeur controller.
 * @Route("/api/v3/classeur")
 */
class ClasseurController extends Controller {


    /**
     * Page qui affiche le dashboard.
     *
     * @Route("/dashborad", name="classeur_dashboard")
     * @Method("GET")
     * @Template()
     */
    public function dashboardAction()
    {
    }

    /**
     * Displays a form to edit an existing Classeur entity.
     *
     * @Route("/{id}", name="classeur_edit", options={"expose"=true}, requirements={"id" = "\d+"})
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction($id)
    {
    }

    /**
     * @Route("/updateUserSesileVersion", name="classeur_updateUserSesile")
     * @Method("POST")
     */
    public function updateUserSesileVersionAction(Request $request) {
        $user = $this->getUser();
        $version = $request->request->get('current_sesile_version');
        $user->setSesileVersion($version);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return new JsonResponse(array('user' => $user));
    }

    /**
     * Page qui affiche la liste des classeurs visibles pour le user connecté.
     *
     * @Route("/", name="classeur")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        return array(
            "menu_color" => "bleu"
        );
    }


    /**
     * Liste des classeurs en cours
     *
     * @Route("/liste", name="liste_classeurs", options={"expose"=true})
     * @Template("SesileClasseurBundle:Classeur:liste.html.twig")
     */
    public function listeAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $get = $request->query;


        // Liste des classeurs visible pour l'utilisateur
        $entities = $em->getRepository('SesileClasseurBundle:Classeur')->getClasseursVisiblesForDTablesV3($this->getUser()->getId(), $get);
        $recordsFiltered = count($em->getRepository('SesileClasseurBundle:Classeur')->countClasseursVisiblesForDTablesV3($this->getUser()->getId()));


        $output = array(
            "draw" => $get->get("draw"),
            "recordsTotal" => count($entities),
            "recordsFiltered" => $recordsFiltered,
            "data" => array()
        );

        foreach($entities as $classeur)
        {
            $validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($classeur);
            $doc = array();
            foreach ($classeur->getDocuments() as $document) {
                $doc[] = $document->getId();
            }

            $val = array();
            foreach ($validants as $validant) {
                if(count($val))
                {
                    $val[] = " / ".$validant->getPrenom() . " " . $validant->getNom();
                }
                else{
                    $val[] = $validant->getPrenom() . " " . $validant->getNom();
                }
            }

            $tabClasseurs = array(

                $classeur->getShortNom(),
                $classeur->getCreation()->format('d/m/Y H:i'),
                $classeur->getValidation()->format('d/m/Y'),
                implode($val),
                $classeur->getType()->getNom(),
                $classeur->getStatus(),
                $classeur->getId(),
                implode($doc),
                $classeur->getType()->getId(),

            );
            $output['data'][] = $tabClasseurs;
        }

        return new Response(json_encode($output));
    }


    /**
     * Page qui affiche la liste des classeurs pour le user id
     *
     * @Route("/liste-classeurs-admin/{id}", name="classeur_admin")
     * @Method("GET")
     * @Template("SesileClasseurBundle:Classeur:liste_admin.html.twig")
     */
    public function indexListeAdminAction($id)
    {
        // On se connecte a la BDD
        $em = $this->getDoctrine()->getManager();

        // On recupere le user
        $user = $em->getRepository('SesileUserBundle:User')->findOneById($id);

        // On renvoit les infos du user et le menu admin a deplier
        return array(
            "user"    => $user,
            "menu_color" => "vert"
        );
    }


    /**
     * Liste des classeurs pour le user id
     *
     * @Route("/liste_admin/{id}", name="liste_classeurs_admin", options={"expose"=true})
     * @Method("GET")
     */
    public function listeAdminAction($id, Request $request) {

        // On se connecte a la BDD
        $em = $this->getDoctrine()->getManager();

        // On recupere les infos du tri et celle du user
        $get = $request->query;
        $user = $em->getRepository('SesileUserBundle:User')->findOneById($id);


        // Liste des classeurs visible pour l'utilisateur
        $entities = $em->getRepository('SesileClasseurBundle:Classeur')->getClasseursVisiblesForDTablesV3($user->getId(), $get);
        $recordsFiltered = count($em->getRepository('SesileClasseurBundle:Classeur')->countClasseursVisiblesForDTablesV3($user->getId()));

        // Constructions des infos du dataTable
        $output = array(
            "draw" => $get->get("draw"),
            "recordsTotal" => count($entities),
            "recordsFiltered" => $recordsFiltered,
            "data" => array()
        );

        // Construction des classeurs pour le dataTable
        foreach($entities as $classeur)
        {
            $validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($classeur);
            $doc = array();
            foreach ($classeur->getDocuments() as $document) {
                $doc[] = $document->getId();
            }

            $val = array();
            foreach ($validants as $validant) {
                if(count($val))
                {
                    $val[] = " / ".$validant->getPrenom() . " " . $validant->getNom();
                }
                else{
                    $val[] = $validant->getPrenom() . " " . $validant->getNom();
                }
            }

            $tabClasseurs = array(

                $classeur->getShortNom(),
                $classeur->getCreation()->format('d/m/Y H:i'),
                $classeur->getValidation()->format('d/m/Y'),
                implode($val),
                $classeur->getType()->getNom(),
                $classeur->getStatus(),
                $classeur->getId(),
                implode($doc),
                $classeur->getType()->getId(),

            );
            $output['data'][] = $tabClasseurs;
        }

        return new Response(json_encode($output));

    }

    /**
     * Liste des classeurs retiré
     *
     * @Route("/liste/retire", name="liste_classeurs_retired")
     * @Method("GET")
     * @Template("SesileClasseurBundle:Classeur:liste_retired.html.twig")
     */
    public function retiredAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('SesileDelegationsBundle:delegations');
        $usersdelegated = $repository->getUsersWhoHasMeAsDelegateRecursively($this->getUser());
        $usersdelegated[] = $this->getUser();

        $entities = $em->getRepository('SesileClasseurBundle:Classeur')->findByStatus(3);

        $tabClasseurs = array();
        foreach($entities as $classeur)
        {
            $validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($classeur);

            $tabClasseurs[] = array(
                'id'=>$classeur->getId(),
                'nom'=>$classeur->getShortNom(),
                'creation'=>$classeur->getCreation(),
                'validation'=>$classeur->getValidation(),
                'type'=>$classeur->getType(),
                'status'=>$classeur->getStatus(),
                'document'=>$classeur->getDocuments(),
                'validants'=>$validants);
        }

        return array(
            'classeurs' => $tabClasseurs,
            "menu_color" => "bleu"
        );
    }

    // SUPPRIMER UN CLASSEUR

    /**
     * Delete an existing Classeur entity.
     *
     * @Route("/delete/{id}", name="delete_classeur")
     * @Method("get")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->findOneById($id);
        $CUtodel = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->findByClasseur($classeur);
        foreach ($CUtodel as $Cluser) {
            $em->remove($Cluser);
        }

        $Actionstodel = $em->getRepository('SesileClasseurBundle:Action')->findByClasseur($classeur);

        foreach ($Actionstodel as $action) {
            $em->remove($action);
        }

        $em->remove($classeur);
        $em->flush();

        return $this->redirect($this->generateUrl('liste_classeurs_retired'));
    }

    /**
     * Delete an existing Classeur entity.
     *
     * @Route("/multiple_delete", name="multiple_delete_classeur")
     * @Method("POST")
     */
    public function multipleDeleteAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data = json_decode($request->request->get('data'));

        foreach ($data as $id) {
            $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->findOneById($id);
            $CUtodel = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->findByClasseur($classeur);
            foreach ($CUtodel as $Cluser) {
                $em->remove($Cluser);
            }

            $Actionstodel = $em->getRepository('SesileClasseurBundle:Action')->findByClasseur($classeur);

            foreach ($Actionstodel as $action) {
                $em->remove($action);
            }

            $em->remove($classeur);
        }

        $em->flush();
        $this->get('session')->getFlashBag()->add(
            'success',
            "Les classeurs ont bien été supprimés"
        );
        return new JsonResponse(array('ret' => true));
    }


    /**
     * Page qui affiche la liste des classeurs à valider pour le user connecté
     *
     * @Route("/liste-a-valider", name="index_valider")
     * @Method("GET")
     * @Template("SesileClasseurBundle:Classeur:a_valider.html.twig")
     */
    public function indexAValiderAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('SesileDelegationsBundle:delegations');
        $usersdelegated = $repository->getUsersWhoHasMeAsDelegateRecursively($this->getUser());
        $usersdelegated[] = $this->getUser();

        $entities = $em->getRepository('SesileClasseurBundle:Classeur')->findBy(
            array(
                "status" => array(0,1,4)
            ));

        $tabClasseurs = array();
        foreach($entities as $classeur)
        {
            $validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($classeur);

            if(array_intersect($usersdelegated, $validants))
            {
                $tabClasseurs[] = array(
                    'id'        => $classeur->getId(),
                    'nom'       => $classeur->getShortNom(),
                    'creation'  => $classeur->getCreation(),
                    'validation'=> $classeur->getValidation(),
                    'type'      => $classeur->getType(),
                    'status'    => $classeur->getStatus(),
                    'document'  => $classeur->getDocuments(),
                    'signable'  => $classeur->isSignableAndLastValidant(),
                    'validants' => $validants
                );
            }

        }

        return array(
            "classeurs"     => $tabClasseurs,
            "uservalidant"  => $this->getUser(),
            "menu_color"    => "bleu"
        );
    }


    /**
     * Liste des classeurs à valider
     *
     * @Route("/a_valider", name="classeur_a_valider")
     * @Method("GET")
     * @Template("SesileClasseurBundle:Classeur:liste_a_valider.html.twig")
     */
    public function aValiderAction() {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('SesileDelegationsBundle:delegations');
        $usersdelegated = $repository->getUsersWhoHasMeAsDelegateRecursively($this->getUser());
        $usersdelegated[] = $this->getUser();

        $entities = $em->getRepository('SesileClasseurBundle:Classeur')->findBy(
            array(
                "status" => array(0,1,4)
            ));

        $tabClasseurs = array();
        foreach($entities as $classeur)
        {
            $validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($classeur);

            if(array_intersect($usersdelegated, $validants))
            {
                $tabClasseurs[] = array(
                    'id'        => $classeur->getId(),
                    'nom'       => $classeur->getShortNom(),
                    'creation'  => $classeur->getCreation(),
                    'validation'=> $classeur->getValidation(),
                    'type'      => $classeur->getType(),
                    'status'    => $classeur->getStatus(),
                    'document'  => $classeur->getDocuments(),
                    'signable'  => $classeur->isSignableAndLastValidant(),
                    'validants' => $validants
                );
            }

        }

        return array(
            "classeurs"     => $tabClasseurs,
            "uservalidant"  => $this->getUser(),
            "menu_color"    => "bleu"
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
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('SesileClasseurBundle:Classeur')->findAll();
        $delegants = $em->getRepository('SesileDelegationsBundle:Delegations')->getUsersWhoHasMeAsDelegate($this->getUser()->getId());
        foreach ($delegants as $delegant) {
            $users[] = $delegant->getId();
        }
        $users[] = $this->getUser()->getId();

        foreach ($entities as $k => $entity) {
            $validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($entity);
            $validantId = array();
            foreach ($validants as $validant) {
                $validantId[] = $validant->getId();
            }

            $prevValidant = $em->getRepository('SesileClasseurBundle:Classeur')->getPrevValidantForRetract($entity);
            if ($entity->isRetractableByDelegates($users, $validantId, $prevValidant)) {

                $entity->validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($entity);
                $entity->setNom($entity->getShortNom());

            } else {
                unset($entities[$k]);
            }
        }

        return array(
            'classeurs' => $entities,
            "menu_color" => "bleu"
        );
    }


    /**
     * Creates a new Classeur entity.
     *
     * @Route("/", name="classeur_create")
     * @Method("POST")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
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


        $type = $em->getRepository('SesileClasseurBundle:TypeClasseur')->findOneById($request->request->get('type'));
        $classeur->setType($type);

        // Et on commence l enregistrement des circuits


        $classeur->setUser($this->getUser()->getId());
        // TODO a modifier par la bonne etape ?
        $classeur->setEtapeDeposante($this->getUser()->getId());

        $classeur->setVisibilite($request->request->get('visibilite'));

        $em->persist($classeur);
        $em->flush();


        // Etapes classeurs
        $tabEtapes = $request->request->get('valeurs');
        $classeur = $em->getRepository('SesileUserBundle:EtapeClasseur')->setEtapesForClasseur($classeur, $tabEtapes, true);


        // Fonction pour enregistrer dans la table Classeur_visible
        $usersVisible = $em->getRepository('SesileUserBundle:EtapeClasseur')->findAllUsers($classeur);
        $usersVisible[] = $this->getUser()->getId();
        $usersCV = $this->classeur_visible($request->request->get('visibilite'), $usersVisible, $request->request->get('userGroupe'));
        foreach ($usersCV as $userCV) {
            $userVisible = $em->getRepository('SesileUserBundle:User')->findOneById($userCV->getId());
            $classeur->addVisible($userVisible);
        }

        // Copies aux utilisateurs
        $em->getRepository('SesileClasseurBundle:Classeur')->setUserCopyForClasseur($classeur, $request->request->get('usersCopy'));

        $action = new Action();
        $action->setClasseur($classeur);
        $action->setUser($this->getUser());
        $action->setAction("Dépot du classeur");


        $em->persist($action);
        $em->flush();

        //Sauvegarde des enregistrements
        $manager = $this->get('oneup_uploader.orphanage_manager')->get('docs');
        $files = $manager->uploadFiles();

        foreach ($files as $k => $file) {

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

                $action = new Action();
                $action->setClasseur($classeur);
                $action->setUser($this->getUser());


                $action->setAction("Ajout du document " . $document->getName());
                $em->persist($action);


                $em->flush();
                $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($document, "Ajout du document au classeur " . $classeur->getNom(), null);
            }

        }

        // envoi d'un mail au premier validant
        $this->sendCreationMail($classeur);


        $error = false;
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
    public function newAction() {
        // On recupere tous les types de classeur et les groupes
        $em = $this->getDoctrine()->getManager();
        $types = $em->getRepository('SesileClasseurBundle:TypeClasseur')->findBy(array(), array('nom' => 'ASC'));

        // Nouveau code pour afficher l ordre des groupes
        $serviceOrgs = $em->getRepository('SesileUserBundle:EtapeGroupe')->findByUsers($this->getUser()->getId());

        if(!count($serviceOrgs)) {
            $this->get('session')->getFlashBag()->add('notice', 'Vous ne faites parti d\'aucun circuit de validation.');
            return $this->redirect($this->generateUrl('classeur'));
        }

        foreach($serviceOrgs as $serviceOrg) {
            $group = $em->getRepository('SesileUserBundle:Groupe')->findOneById($serviceOrg);
            $circuits[] = array(
                "id" => $group->getId(),
                "name" => $group->getNom(),
//                "ordre" => $group->getOrdre(),
                "groupe" => true
            );

        }
        // FIN du Nouveau code pour afficher l ordre des groupes

        return array(
//            "userGroupes" => $groupes,
            "typeClasseurs" => $types,
            "menu_color"    => "bleu",
            "userGroupes"   => $circuits,
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

        var_dump('Update Action !!! ', $request->request->get('circuit'));
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

        $em->flush();

        $action = new Action();
        $action->setClasseur($classeur);

        $action->setUser($this->getUser());
        $action->setAction("Modification du classeur");


        $message = $request->query->get('text-message');
        $action->setObservation($message);



        $em->persist($action);
        $em->flush();

        /**
         * TODO modifier le fonctionnement : on doit updater les users par la collection et non par suppression / rajout (un peu de propreté qd même!!!)
         */
        // gestion du circuit
        $users = explode(',', $circuit);
        $classeurUserObj = $em->getRepository("SesileClasseurBundle:ClasseursUsers");
        $classeurUserObj->deleteClasseurUser($classeur, $circuit);

        for ($i = 0; $i < count($users); $i++) {
            $userObj = $em->getRepository("SesileUserBundle:User")->findOneById($users[$i]);
            $classeurUser = $classeurUserObj->findOneBy(array("user" => $userObj, "classeur" => $classeur));

            $exist = true;

            if (empty($classeurUser)) {
                $exist = false;
                $classeurUser = new ClasseursUsers();
                $classeurUser->setClasseur($classeur);
                $classeurUser->setUser($userObj);
            }

            $classeurUser->setOrdre($i + 1);
            if (!$exist) {
                $em->persist($classeurUser);
            }

        }

        $em->flush();
        $error = false;
        if (!$error) {
            $this->get('session')->getFlashBag()->add(
                'success',
                'Classeur modifié avec succès !'
            );
        }

        $session = $request->getSession();
        $session->getFlashBag()->add(
            'success',
            "Le classeur a été modifié"
        );
        return $this->redirect($this->generateUrl('classeur'));
    }


    /**
     * Submit an existing Classeur entity.
     *
     * @Route("/soumettre", name="classeur_soumis")
     * @Method("POST")
     *
     */

    public function soumettreAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->find($request->get("id"));

        if (!$classeur) {
            throw $this->createNotFoundException('Unable to find Classeur entity.');
        }

//        $isvalidator = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->isDelegatedToUser($classeur->getId(), $this->getUser());
        $isvalidator = $em->getRepository('SesileClasseurBundle:Classeur')->isDelegatedToUser($classeur, $this->getUser());
        $validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($classeur);

        $validantsId = array();
        foreach ($validants as $validant) {
            $validantsId[] = $validant->getId();
        }

        if(!$isvalidator && in_array($this->getUser()->getId(), $validantsId)) {

            $classeur->setVisibilite($request->get("visibilite"));
            $classeur->setNom($request->get("name"));
            $classeur->setDescription($request->get("desc"));
            list($d, $m, $a) = explode("/", $request->request->get('validation'));
            $valid = new \DateTime($m . "/" . $d . "/" . $a);
            $classeur->setValidation($valid);
            $circuit = $request->request->get('circuit');
            $classeur->setCircuit($circuit);
        }



        $classeur = $em->getRepository('SesileUserBundle:EtapeClasseur')->setEtapesForClasseur($classeur, $request->request->get('valeurs'));
        // Et on met le bon status
        $classeur->setStatus(1);

        // MAJ de la visibilite
        $this->set_user_visible ($classeur, $request->get("visibilite"));

        $em->flush();

        $em->getRepository('SesileClasseurBundle:Classeur')->setUserCopyForClasseur($classeur, $request->request->get('usersCopy'));

        if ($request->get("moncul") == 1) {
            $action = new Action();
            $action->setClasseur($classeur);
            $action->setUser($this->getUser());
            $action_libelle = "Classeur signé";
            $action->setAction($action_libelle);
            $em->persist($action);
            $em->flush();
        }

        $action = new Action();

        $action->setCommentaire($request->request->get('comment'));
        $action->setClasseur($classeur);
        $action->setUser($this->getUser());
//        $action_libelle = ($classeur->getValidant() == 0) ? "Classeur finalisé" : "Validation";
        $action_libelle = "Classeur à nouveau soumis";

        if ($isvalidator) {
            $delegators = $em->getRepository('SesileDelegationsBundle:Delegations')->getDelegantsForUser($this->getUser());
            foreach ($delegators as $delegator) {
                $action_libelle .= " (Délégation recue de " . $delegator->getPrenom() . " " . $delegator->getNom() . ")";
            }
        }
        $action->setAction($action_libelle);
        $em->persist($action);
        $em->flush();

        $this->sendCreationMail($classeur);

        //$this->updateAction($request);

        $request->getSession()->getFlashBag()->add(
            'success',
            "Le classeur a été déposé"
        );
        return $this->redirect($this->generateUrl('index_valider'));

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


        if($request->get("moncul") != 1) {
            // Met a jour les etapes de validations
            $classeur = $em->getRepository('SesileUserBundle:EtapeClasseur')->setEtapesForClasseur($classeur, $request->request->get('valeurs'));
            $em->flush();

            $visibilite = $request->get("visibilite");
            $classeur->setVisibilite($visibilite);
            $classeur->setNom($request->get("name"));
            $classeur->setDescription($request->get("desc"));
            list($d, $m, $a) = explode("/", $request->request->get('validation'));
            $valid = new \DateTime($m . "/" . $d . "/" . $a);
            $classeur->setValidation($valid);
            $currentvalidant = $request->request->get('curentValidant');

        } else {
//            $circuit = $classeur->getCircuit();
            $visibilite = $classeur->getVisibilite();

            // On renomme le document avec -sign
            $doc = $classeur->getDocuments()[0];
            $path_parts = pathinfo($doc->getName());
            $nouveauNom = $path_parts['filename'] . '-sign.' . $path_parts['extension'];
            $doc->setName($nouveauNom);
            $currentvalidant = $this->getUser()->getId();
        }

        $classeur->setCircuit($currentvalidant);

        $isvalidator = $em->getRepository('SesileClasseurBundle:Classeur')->isDelegatedToUser($classeur, $this->getUser());


        // MAJ de la visibilite
        $this->set_user_visible ($classeur, $visibilite);

        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->validerClasseur($classeur);


        $em->flush();

        // Ajout des actions
        if($request->get("moncul") == 1) {
            $action = new Action();
            $action->setClasseur($classeur);
            $action->setUser($this->getUser());
            $action_libelle = "Classeur signé";
            $action->setAction($action_libelle);
            $em->persist($action);
            $em->flush();
        }

        $action = new Action();


        /*Ajout du commentaire*/

        $commentaire = $request->request->get('comment');
        $action->setCommentaire($commentaire);

        $action->setClasseur($classeur);
        $action->setUser($this->getUser());
        $action_libelle = ($classeur->getStatus() == 2) ? "Classeur finalisé" : "Validation";

        if($isvalidator) {
            $delegators = $em->getRepository('SesileDelegationsBundle:Delegations')->getDelegantsForUser($this->getUser());
            foreach ($delegators as $delegator) {
                $action_libelle .= " (Délégation reçue de " . $delegator->getDelegant()->getPrenom() . " " . $delegator->getDelegant()->getNom() . ")";
            }
        }
        $action->setAction($action_libelle);
        $em->persist($action);
        $em->flush();

        // Envoie du mail
        if($classeur->getStatus() != 2) {
            $this->sendCreationMail($classeur);
        } else {
//            $this->sendValidationMail($classeur, $currentvalidant);
            $this->sendValidationMail($classeur);
        }

        $request->getSession()->getFlashBag()->add(
            'success',
            "Le classeur a été validé"
        );
        return $this->redirect($this->generateUrl('index_valider'));
    }


    /**
     * refuser an existing Classeur entity.
     *
     * @Route("/refuser", name="classeur_refuser")
     * @Method("POST")
     *
     */

    public function refuserAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->find($request->request->get("id2"));

        if (!$classeur) {
            throw $this->createNotFoundException('Unable to find Classeur entity.');
        }


        $isvalidator = $em->getRepository('SesileClasseurBundle:Classeur')->isDelegatedToUser($classeur, $this->getUser());

        // envoi d'un mail validant suivant
        $this->sendRefusMail($classeur,$request->request->get('text-message'));

        $classeur->refuser();

        $em->flush();


        $action = new Action();

        $action->setCommentaire($request->request->get('comment'));

        $message = "motif du refus : " . $request->request->get('text-message');
        $action->setObservation($message);
        $action->setClasseur($classeur);
        $action->setUser($this->getUser());
        $action_libelle = "Refus";

        if($isvalidator) {
            $delegators = $em->getRepository('SesileDelegationsBundle:Delegations')->getDelegantsForUser($this->getUser());
            foreach ($delegators as $delegator) {
                $action_libelle .= " (Délégation reçue de " . $delegator->getDelegant()->getPrenom() . " " . $delegator->getDelegant()->getNom() . ")";
            }
        }
        $action->setAction($action_libelle);
        $em->persist($action);
        $em->flush();



        //$this->updateAction($request);

        $request->getSession()->getFlashBag()->add(
            'success',
            "Le classeur a été refusé"
        );

        return $this->redirect($this->generateUrl('index_valider'));
        //return $this->redirect($this->generateUrl('classeur_edit', array('id' => $classeur->getId())));
    }


    /**
     * Valider_et_signer an existing Classeur entity.
     *
     * @Route("/rename", name="testrename")
     * @Method("get")
     *
     */
    public function testrenameAction()
    {
        $em = $this->getDoctrine()->getManager();
        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->find(40);


        if (!$classeur) {
            throw $this->createNotFoundException('Unable to find Classeur entity.');
        }

        $doc = $classeur->getDocuments()[0];
        $ancienNom = $doc->getName();
        $path_parts = pathinfo($ancienNom);
        $nouveauNom = $path_parts['filename'] . '-sign.' . $path_parts['extension'];
        $doc->setName($nouveauNom);

        $em->flush();
        //var_dump($doc->getName());
        exit;
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

        $doc = $classeur->getDocuments()[0];
        $ancienNom = $doc->getName();
        $path_parts = pathinfo($ancienNom);
        $nouveauNom = $path_parts['filename'] . '-sign.' . $path_parts['extension'];
        $doc->setName($nouveauNom);

        if($request->get("moncul") != 1) {
            // Met a jour les etapes de validations
            $classeur = $em->getRepository('SesileUserBundle:EtapeClasseur')->setEtapesForClasseur($classeur, $request->request->get('valeurs'));
            $em->flush();

            $visibilite = $request->get("visibilite");
            $classeur->setVisibilite($visibilite);
            $classeur->setNom($request->get("name"));
            $classeur->setDescription($request->get("desc"));
            list($d, $m, $a) = explode("/", $request->request->get('validation'));
            $valid = new \DateTime($m . "/" . $d . "/" . $a);
            $classeur->setValidation($valid);
            $currentvalidant = $request->request->get('curentValidant');

        }
        $isvalidator = $em->getRepository('SesileClasseurBundle:Classeur')->isDelegatedToUser($classeur, $this->getUser());

        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->validerClasseur($classeur);

        $doc = $classeur->getDocuments()[0];
        $ancienNom = $doc->getName();
        $path_parts = pathinfo($ancienNom);
        $nouveauNom = $path_parts['filename'] . '-sign.' . $path_parts['extension'];
        $doc->setName($nouveauNom);

        $em->flush();


        $action = new Action();

        $commentaire = $request->request->get('comment');
        $action->setCommentaire($commentaire);

        $action->setClasseur($classeur);
        $action->setUser($this->getUser());
        $action_libelle = "Signature";
//        if($isvalidator) $action_libelle.=" (Délégation recue de ".$delegator->getPrenom()." ".$delegator->getNom().")";
        if($isvalidator) {
            $delegators = $em->getRepository('SesileDelegationsBundle:Delegations')->getDelegantsForUser($this->getUser());
            foreach ($delegators as $delegator) {
                $action_libelle .= " (Délégation reçue de " . $delegator->getDelegant()->getPrenom() . " " . $delegator->getDelegant()->getNom() . ")";
            }
        }
        $action->setAction($action_libelle);
        $em->persist($action);
        $em->flush();

        $session = $request->getSession();
        $session->getFlashBag()->add(
            'success',
            "Le classeur a été signé"
        );
        //$this->updateAction($request);
        return $this->redirect($this->generateUrl('index_valider'));
    }





    /**
     * @Route("/statusclasseur/{id}", name="status_classeur",  options={"expose"=true})
     * @param $id
     * @return JsonResponse
     */
    public function statusClasseurAction($id) {

        $em = $this->getDoctrine()->getManager();
        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->findOneById($id);


        return new JsonResponse($classeur->getStatus());
    }


    /**
     * Valider_et_signer an existing Classeur entity.
     *
     * @Route("/signdocjws/{id}/{role}", name="signdocjws")
     * @Template()
     *
     */
    public function signDocJwsAction(Request $request, $id, $role = null)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // Connexion a la BDD
        $em = $this->getDoctrine()->getManager();

        // Récupération du classeur
        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->findOneById($id);

        // Récupération du role
        $userRole = $em->getRepository('SesileUserBundle:UserRole')->findOneById($role);

        // Vérification que le classeur existe bien
        if (!$classeur) {
            throw $this->createNotFoundException('Unable to find Classeur entity.');
        }

        // MAJ des infos du classeur
        // Met a jour les etapes de validations
        $classeur = $em->getRepository('SesileUserBundle:EtapeClasseur')->setEtapesForClasseur($classeur, $request->request->get('valeurs'));
        $em->flush();

        // MAJ de l etat de la visibilité, nom, description, date de validation
        $this->updateInfosClasseurs($request, $id, $em);

        $classeurs[] = $classeur;

        $classeurId = array();
        foreach ($classeurs as $classeur) {
            $classeurId[] = $classeur->getId();
        }


        return array(
            'user'      => $user,
            'role'      => $userRole,
            'classeurs'  => $classeurs,
            'classeursId'  => urlencode(serialize($classeurId))
        );

    }


    /**
     * Valider_et_signer existing Classeurs entities.
     *
     * @Route("/signdocsjws/{role}", name="signdocsjws")
     * @Template("SesileClasseurBundle:Classeur:signDocJws.html.twig")
     * @param Request $request
     * @param null $role
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function signDocsJwsAction(Request $request, $role = null)
    {
        // on verifie qu un classeur a bien ete soumis
        if (!$request->get("classeurs")) {
            $request->getSession()->getFlashBag()->add(
                'warning',
                "Aucun classeur n'a été séléctionné pour la signature"
            );
            return $this->redirect($this->generateUrl('index_valider'));
        }
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // Connexion a la BDD
        $em = $this->getDoctrine()->getManager();

        // Récupération du classeur
        $classeurs = $em->getRepository('SesileClasseurBundle:Classeur')->findById(
            $request->get("classeurs")
        );
        // Récupération du role
        $userRole = $em->getRepository('SesileUserBundle:UserRole')->findOneById($role);

        // Vérification que le classeur existe bien
        if (!$classeurs) {
            throw $this->createNotFoundException('Unable to find Classeur entity.');
        }


        $classeurId = array();
        foreach ($classeurs as $classeur) {
            $classeurId[] = $classeur->getId();
        }


        return array(
            'user'      => $user,
            'role'      => $userRole,
            'classeurs'  => $classeurs,
            'classeursId'  => urlencode(serialize($classeurId))
        );
    }

    /**
     * Génération du fichier JNLP permettant l exécution de l application de signature depuis la preview
     *
     * @Route("/jnlpSignerFilesFromPreview/{role}", name="jnlpSignerFilesFromPreview")
     * @param Request $request
     * @param null $role
     * @return Response
     */
    public function jnlpSignerFilesFromPreviewAction (Request $request, $role = null) {
        // on verifie qu un classeur a bien ete soumis
        if (!$request->get("classeurs")) {
            $request->getSession()->getFlashBag()->add(
                'warning',
                "Aucun classeur n'a été séléctionné pour la signature"
            );
            return $this->redirect($this->generateUrl('index_valider'));
        }

        return $this->generateJnlp($request->get("classeurs"), $role);

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

        $classeur->retracter();

        $em->flush();

        $action = new Action();
        $action->setClasseur($classeur);
        $action->setUser($this->getUser());
        $action->setAction("Rétractation");
        $em->persist($action);
        $em->flush();

        $session = $request->getSession();
        $session->getFlashBag()->add(
            'success',
            "Le classeur a été rétracté"
        );
        return $this->redirect($this->generateUrl('index_valider'));
    }


    /**
     * Deletes a Classeur entity.
     *
     * @Route("/supprimer/{id}", name="classeur_supprimer")
     * @Method("get")
     */
    public function supprimerAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->find($id);
        if (!$classeur) {
            throw $this->createNotFoundException('Unable to find Classeur entity.');
        }


        $isvalidator = $em->getRepository('SesileClasseurBundle:Classeur')->isDelegatedToUser($classeur, $this->getUser());

        $classeur->supprimer();
        $em->flush();

        $action = new Action();
        $action->setClasseur($classeur);
        $action->setUser($this->getUser());
        $action_libelle = "Classeur retiré";
//        if($isvalidator) $action_libelle.=" (Délégation recue de ".$delegator->getPrenom()." ".$delegator->getNom().")";
        if($isvalidator) {
            $delegators = $em->getRepository('SesileDelegationsBundle:Delegations')->getDelegantsForUser($this->getUser());
            foreach ($delegators as $delegator) {
                $action_libelle .= " (Délégation reçue de " . $delegator->getDelegant()->getPrenom() . " " . $delegator->getDelegant()->getNom() . ")";
            }
        }
        $action->setAction($action_libelle);
        $em->persist($action);
        $em->flush();


        $this->get('session')->getFlashBag()->add(
            'success',
            "Le classeur a été retiré"
        );
        return $this->redirect($this->generateUrl('classeur'));
    }

    /**
     * Creates a form to edit a Classeur entity.
     *
     * @Route("/new_factory/", name="classeur_new_type", options={"expose"=true})
     * @Method("POST")
     *
     * @param Request $request
     * @return Response
     */
    public function formulaireFactory(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('SesileUserBundle:Groupe')->findBy(array(
            "collectivite" => $this->get("session")->get("collectivite")
        ));

        $type = $request->request->get('type', 'elclassico');
        $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));
        $userPacks = $em->getRepository('SesileUserBundle:UserPack')->findByCollectivite($collectivite);
        $users = $em->getRepository('SesileUserBundle:User')->findBy(
            array("collectivite" => $this->get("session")->get("collectivite"), 'enabled' => 1),
            array("Nom" => "ASC")
        );

        switch ($type) {
            // Le cas ou c est Helios
            case 2:
                $form = 'SesileClasseurBundle:Formulaires:elpez.html.twig';
                break;
            default:
                $form = 'SesileClasseurBundle:Formulaires:elclassico.html.twig';
                break;
        }
        return $this->render(
            $form,
            array(
                "groupes" => $entities,
                "users"   => $users,
                "userPacks"=>$userPacks
            )
        );
    }

    /**
     * Add a document to a classeur
     *
     * @Route("/addDocument/", name="add_document_to_classeur", options={"expose"=true})
     * @Method("POST")
     *
     *
     */
    public function addDocumentToClasseur(Request $request)
    {

        $reqid = $request->request->get('id');
//        var_dump($request->request->get('serverfilename'));
        if (empty($reqid)) {
            return new JsonResponse(array('error' => 'Parameters missing'));
        }


        //Récupérer le classeur

        $em = $this->getDoctrine()->getManager();
        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->find($request->get("id"));

        //Sauvegarde des enregistrements
        $manager = $this->get('oneup_uploader.orphanage_manager')->get('docs');



        // upload all files to the configured storage
        $files = $manager->uploadFiles();

        foreach ($files as $k => $file) {
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

                $action = new Action();
                $action->setClasseur($classeur);
                $action->setUser($this->getUser());
                $action->setAction("Ajout du document " . $document->getName());
                $em->persist($action);


                $em->flush();
                $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($document, "Ajout du document au classeur " . $classeur->getNom(), null);


            }
        }

        return new JsonResponse(array('error' => 'ok'));
    }



    /*                MAILS DE NOTIFICATION                      */

    private function sendMail($sujet, $to, $body)
    {
        $message = \Swift_Message::newInstance();
        // Pour l integration de l image du logo dans le mail
        $html = explode("**logo_coll**", $body);
        if($this->get('session')->get('logo') !== null && $this->container->getParameter('upload')['logo_coll'] !== null && !empty($html)) {
            $htmlBody = $html[0] . '<img src="' . $message->embed(\Swift_Image::fromPath($this->container->getParameter('upload')['logo_coll'] . $this->get('session')->get('logo'))) . '" width="75" alt="Sesile">' . $html[1];
        } else {
            $htmlBody = $body;
        }

        // On rajoute les balises manquantes
        $html_brkts_start = "<html><head></head><body>";
        $html_brkts_end = "</body></html>";
        $htmlBodyFinish = $html_brkts_start . $htmlBody . $html_brkts_end;

        // Constitution du mail
        $message->setSubject($sujet)
            ->setFrom($this->container->getParameter('email_sender_address'))
            ->setTo($to)
            ->setBody($htmlBodyFinish)
            ->setContentType('text/html');

        // Envoie de l email
        $this->get('mailer')->send($message);
    }

    private function sendValidationMail(Classeur $classeur, $currentvalidant = null)
    {
        $em = $this->getDoctrine()->getManager();
        if (is_null($currentvalidant)) {
            $currentvalidant = $this->getUser();
        }
        $coll = $em->getRepository("SesileMainBundle:Collectivite")->find($this->get("session")->get("collectivite"));
        $c_user = $em->getRepository("SesileUserBundle:User")->findOneById($currentvalidant);
        $deposant = $em->getRepository("SesileUserBundle:User")->findOneById($classeur->getUser());

        $env = new \Twig_Environment(new \Twig_Loader_Array(array()));
        $template = $env->createTemplate($coll->getTextMailwalid());
        $template_html = array(
            'validant' => $c_user->getPrenom() . " " . $c_user->getNom(),
            'role' => $c_user->getRole(),
            'qualite' => $c_user->getQualite(),
            'titre_classeur' => $classeur->getNom(),
            'date_limite' => $classeur->getValidation(),
            'type' => strtolower($classeur->getType()->getNom()),
            "lien" => '<a href="http://' . $this->container->get('router')->getContext()->getHost() . $this->generateUrl('classeur_edit', array('id' => $classeur->getId())) . '">voir le classeur</a>'
        );
        $subject = "SESILE - Classeur validé";

        // notification du deposant
        $this->sendMail(
            $subject,
            $deposant->getEmail(),
            $template->render(
                array_merge($template_html, array('deposant' => $deposant->getPrenom() . " " . $deposant->getNom()))
            )
        );

        // notification des users en copy
        $usersCopy = $classeur->getCopy();
        if ($usersCopy !== null && is_array($usersCopy)) {
            foreach ($usersCopy as $userCopy) {
                if ($userCopy != null && $userCopy != $deposant) {
                    $this->sendMail(
                        $subject,
                        $userCopy->getEmail(),
                        $template->render(
                            array_merge($template_html, array('deposant' => $userCopy->getPrenom() . " " . $userCopy->getNom()))
                        )
                    );
                }
            }
        }

        // notification des utilisateurs se trouvant dans les etapes
        $etapesClasseur = $classeur->getEtapeClasseurs();
        foreach ($etapesClasseur as $etapeClasseur) {
            $users = $etapeClasseur->getUsers();
            foreach ($users as $user) {
                $this->sendMail(
                    $subject,
                    $user->getEmail(),
                    $template->render(
                        array_merge($template_html, array('deposant' => $user->getPrenom() . " " . $user->getNom()))
                    )
                );
            }
        }

    }

    private function sendCreationMail(Classeur $classeur) {
        $em = $this->getDoctrine()->getManager();
        $coll = $em->getRepository("SesileMainBundle:Collectivite")->find($this->get("session")->get("collectivite"));
        $d_user = $em->getRepository("SesileUserBundle:User")->find($classeur->getUser());

        $env = new \Twig_Environment(new \Twig_Loader_Array(array()));
        $template = $env->createTemplate($coll->getTextmailnew());
        $template_html = array(
            'deposant' => $d_user->getPrenom() . " " . $d_user->getNom(),
            'role' => $d_user->getRole(),
            'qualite' => $d_user->getQualite(),
            'titre_classeur' => $classeur->getNom(),
            'date_limite' => $classeur->getValidation(),
            'type' => strtolower($classeur->getType()->getNom()),
            "lien" => '<a href="http://'.$this->container->get('router')->getContext()->getHost().$this->generateUrl('classeur_edit', array('id' => $classeur->getId())) . '">valider le classeur</a>'
        );

        $validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($classeur);
        foreach($validants as $validant) {
            if ($validant != null) {
                $this->sendMail(
                    "SESILE - Nouveau classeur à valider",
                    $validant->getEmail(),
                    $template->render(
                        array_merge($template_html, array('validant' => $validant->getPrenom() . " " . $validant->getNom()))
                    )
                );
            }
        }

        // notification des users en copy
        $usersCopy = $classeur->getCopy();
        if ($usersCopy !== null && is_array($usersCopy)) {
            foreach ($usersCopy as $userCopy) {
                if($userCopy != null && !in_array($userCopy, $validants)) {
                    $this->sendMail(
                        "SESILE - Nouveau classeur déposé",
                        $userCopy->getEmail(),
                        $template->render(
                            array_merge($template_html, array('validant' => $userCopy->getPrenom() . " " . $userCopy->getNom()))
                        )
                    );
                }
            }
        }
    }

    private function sendRefusMail(Classeur $classeur,$motif) {
        $em = $this->getDoctrine()->getManager();
        $coll = $em->getRepository("SesileMainBundle:Collectivite")->find($this->get("session")->get("collectivite"));
        $c_user = $em->getRepository("SesileClasseurBundle:Classeur")->classeurValidator($classeur, $this->getUser());

        $env = new \Twig_Environment(new \Twig_Loader_Array(array()));
        $template = $env->createTemplate($coll->getTextmailrefuse());
        $template_html = array(
            'validant' => $c_user->getPrenom()." ".$c_user->getNom(),
            'role' => $c_user->getRole(),
            'qualite' => $c_user->getQualite(),
            'titre_classeur' => $classeur->getNom(),
            'date_limite' => $classeur->getValidation(),
            'type' => strtolower($classeur->getType()->getNom()),
            "lien" => '<a href="http://'.$this->container->get('router')->getContext()->getHost().$this->generateUrl('classeur_edit', array('id' => $classeur->getId())) . '">voir le classeur</a>',
            "motif" => $motif
        );

        $deposant = $em->getRepository('SesileUserBundle:User')->find($classeur->getUser());

        if ($deposant != null) {
            $this->sendMail(
                "SESILE - Classeur refusé",
                $deposant->getEmail(),
                $template->render(
                    array_merge($template_html, array('validant' => $c_user->getPrenom()." ".$c_user->getNom()))
                )
            );
        }

        // notification des users en copy
        $usersCopy = $classeur->getCopy();
        if ($usersCopy !== null && is_array($usersCopy)) {
            foreach ($usersCopy as $userCopy) {
                if ($userCopy != null && $userCopy != $deposant) {
                    $this->sendMail(
                        "SESILE - Classeur refusé",
                        $userCopy->getEmail(),
                        $template->render(
                            array_merge($template_html, array('validant' => $userCopy->getPrenom() . " " . $userCopy->getNom()))
                        )
                    );
                }
            }
        }
    }


    /**
     * Fonction pour determiner la visibilite et enregister dans Classeur_visible
     *
     * @param $visibilite
     * @param $users
     * @param null $requestUserGroupe
     * @return string|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function classeur_visible($visibilite, $users, $requestUserGroupe = false) {
        $em = $this->getDoctrine()->getManager();
        switch ($visibilite) {
            // Privé soit le circuit
            case 0:
                return $em->getRepository('SesileUserBundle:User')->findById(array_unique($users));
                break;
            // Public
            case 1:
                return $em->getRepository('SesileUserBundle:User')->findByCollectivite($this->get("session")->get("collectivite"));
                break;
            // Privé à partir de moi
            case 2:
                return '2';
                break;
            // Pour le service organisationnel (et le circuit)
            case 3:
                if ($requestUserGroupe) {

                    $userGroupe = array();
                    // Service Organistionnel
                    $servicesOrg = $em->getRepository('SesileUserBundle:Groupe')->findById($requestUserGroupe);
                    foreach ($servicesOrg as $serviceOrg) {

                        // Etapes du SO
                        $etapesGroupe = $serviceOrg->getEtapeGroupes();
                        foreach ($etapesGroupe as $etapeGroupe) {

                            // UserPack des etapes
                            $usersPacks = $etapeGroupe->getUserPacks();
                            foreach ($usersPacks as $usersPack) {

                                // Users des UserPack
                                $usersFromUserPack = $usersPack->getUsers();
                                foreach ($usersFromUserPack as $userFromUserPack) {
                                    $userGroupe[] = $userFromUserPack->getId();
                                }
                            }

                            // Liste des utilisateurs directement ajouté
                            $usersFromEtapes = $etapeGroupe->getUsers();
                            foreach ($usersFromEtapes as $usersFromEtape) {
                                $userGroupe[] = $usersFromEtape->getId();
                            }
                        }
                    }
                    $usersAll = array_unique(array_merge($userGroupe, $users));
                    return $em->getRepository('SesileUserBundle:User')->findById($usersAll);
                } else {
                    return $this->redirect($this->generateUrl('classeur_create'));
                }
                break;
            // Par défaut on fait quoi ?
            default:
                $this->get('session')->getFlashBag()->add('notice', 'Merci de choisir une visibilité.');
                return $this->redirect($this->generateUrl('classeur_create'));
                break;
        }
    }

    /**
     * Fonction permettant la mise a jour de la visibilite
     *
     * @param $classeur
     * @param $visibilite
     */
    public function set_user_visible($classeur, $visibilite) {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('SesileUserBundle:EtapeClasseur')->findAllUsers($classeur);
        $users[] = $classeur->getUser();

        if ($visibilite != 2 && $visibilite != 3) {
            $usersCV = $this->classeur_visible($visibilite, $users);
            // On vide la table many to many
            $classeur->getVisible()->clear();
            foreach ($usersCV as $userCV) {
                $userVisible = $em->getRepository('SesileUserBundle:User')->findOneById($userCV->getId());
                $classeur->addVisible($userVisible);
            }

        }

        // Si la visibilite du classeur est prive a partir de moi
        elseif ($visibilite == 2) {
            $usersCV = $em->getRepository('SesileUserBundle:EtapeClasseur')->findAllUsersAfterMe($classeur);
            $usersCV = array_unique($usersCV);

            // On vide la table many to many
            $classeur->getVisible()->clear();
            foreach ($usersCV as $userCV) {
                $userVisible = $em->getRepository('SesileUserBundle:User')->findOneById($userCV);
                $classeur->addVisible($userVisible);
            }
        }

        // Si la visibilite du classeur est service organisationnel (et le circuit)
        elseif ($visibilite == 3) {

            $usersVisible = $classeur->getVisible();
            $usersAlreadyVisible = array();
            foreach ($usersVisible as $userV) {
                $usersAlreadyVisible[] = $userV->getId();
            }
            $usersCV = $em->getRepository('SesileUserBundle:EtapeClasseur')->findAllUsersAfterMe($classeur);
            $usersCV = array_unique($usersCV);

            $usersCV = array_diff($usersCV, $usersAlreadyVisible);
            // On vide la table many to many
            // $classeur->getVisible()->clear();
            foreach ($usersCV as $userCV) {
                $userVisible = $em->getRepository('SesileUserBundle:User')->findOneById($userCV);
                $classeur->addVisible($userVisible);
            }
        }
    }


    /**
     * MAJ de l etat de la visibilité, nom, description, date de validation
     *
     * @param $request
     * @param $id
     * @param $em
     * @internal param $classeur
     */
    public function updateInfosClasseurs($request, $id, $em) {
        if (null !== $request && $request->isMethod('post')) {
//            $em = $this->getEntityManager();
            $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->findOneById($id);
            $visibilite = $request->get("visibilite");
            $classeur->setVisibilite($visibilite);
            $classeur->setNom($request->get("name"));
            $classeur->setDescription($request->get("desc"));
            list($d, $m, $a) = explode("/", $request->request->get('validation'));
            $valid = new \DateTime($m . "/" . $d . "/" . $a);
            $classeur->setValidation($valid);

            // MAJ de la visibilite
            $this->set_user_visible($classeur, $visibilite);

            $em->flush();
        }
    }


}