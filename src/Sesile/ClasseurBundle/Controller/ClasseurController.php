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
 *
 */
class ClasseurController extends Controller {


    /**
     * Page qui affiche le dashboard.
     *
     * @Route("/dashborad", name="classeur_dashboard")
     * @Method("GET")
     * @Template()
     */
    public function dashboardAction() {
        return array(
            "menu_color" => "bleu"
        );
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


        $type = $em->getRepository('SesileClasseurBundle:TypeClasseur')->findOneById($request->request->get('type'));
        $classeur->setType($type);

        // Et on commence l enregistrement des circuits


        $classeur->setUser($this->getUser()->getId());
        // TODO a modifier par la bonne etape ?
        $classeur->setEtapeDeposante($this->getUser()->getId());

        $classeur->setVisibilite($request->request->get('visibilite'));

        $em->persist($classeur);
        $em->flush();


//        $tabEtapes = json_decode($request->request->get('valeurs'));
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

                $document = new Document();

            $document->setName($request->request->get('serverfilename')[$k]);
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
            $this->get('session')->getFlashBag()->add('notice', 'Vous ne faites parti d\'aucun service organisationnel.');
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
            "menu_color" => "bleu",
            "userGroupes" => $circuits,
        );
    }


    /**
     * Displays a form to edit an existing Classeur entity.
     *
     * @Route("/{id}", name="classeur_edit", options={"expose"=true}, requirements={"id" = "\d+"})
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction($id) {

        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        $entity = $em->getRepository('SesileClasseurBundle:Classeur')->findOneById($id);

        $usersdelegated = $em->getRepository('SesileDelegationsBundle:delegations')->getUsersWhoHasMeAsDelegate($this->getUser()->getId());
        $isusersdelegated = $em->getRepository('SesileDelegationsBundle:delegations')->getUsersWhoHasMeAsDelegate($this->getUser()->getId());
        $editDelegants = false;
        foreach($usersdelegated as $userdelegated) {
            $delegants[] = $userdelegated->getId();
            if (in_array($entity, $userdelegated->getClasseurs()->toArray())) {
                $editDelegants = true;
            }
        }

        // Si le user n est pas un super admin ou un user avec des droits de delgations ou un user du circuit
        if ((!in_array($entity, $user->getClasseurs()->toArray()) and !$editDelegants) && !$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $this->get('session')->getFlashBag()->add(
                'error',
                "Vous n'avez pas accès à ce classeur"
            );
            return $this->redirect($this->generateUrl('classeur'));
        }
        $validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($entity);
        $prevValidants = $em->getRepository('SesileClasseurBundle:Classeur')->getPrevValidant($entity);
        $prevValidantRetract = $em->getRepository('SesileClasseurBundle:Classeur')->getPrevValidantForRetract($entity);

        $users = $em->getRepository('SesileUserBundle:User')->findBy(array(
            "collectivite" => $this->get("session")->get("collectivite"), 'enabled' => 1
        ), array("Nom" => "ASC"));

        // Definition des users pour le bouton retractable
        $usersdelegated[] = $this->getUser();
        $delegants[] = $this->getUser()->getId();

        $validantsId = array();
        foreach ($validants as $validant) {
            $validantsId[] = $validant->getId();
        }
        foreach ($prevValidants as $prevValidant) {
            $prevValidantsId[] = $prevValidant->getId();
        }

        // Definition si rétractation
        $isRetractableByDelegates = $entity->isRetractableByDelegates($delegants, $validantsId, $prevValidantRetract);


        // Test pour le validant courant
        if ($entity->isValidableByDelegates($usersdelegated, $validants)) {
            $currentValidant = array("id" => end($usersdelegated)->getId(), "nom" => end($usersdelegated)->getPrenom() . " " . end($usersdelegated)->getNom(), "path" => end($usersdelegated)->getPath());
        }
        else {
            $currentValidant = '';
        }

        // Test Pour la délégation
        if ($isusersdelegated and !in_array($this->getUser()->getId(), $validantsId)) {
            $isDelegatedToMe = true;
            $uservalidant = $usersdelegated[0];
        } else {
            $isDelegatedToMe = false;
            $uservalidant = $this->getUser();
        }


        // Test pour savoir si le classeur est signable
//        $isSignable = $entity->isSignable();
        $isSignable = $entity->isSignableAndLastValidant();

        // Test pour savoir si on peut signer le PDF
        $isSignablePDF = $entity->isSignablePDF();


        return array(
            'validant'      => $validants,
            'currentValidant' => $currentValidant,
            'classeur'      => $entity,
            'retractable'   => $isRetractableByDelegates,
            'signable'      => $isSignable,
            'signablePDF'   => $isSignablePDF,
            'usersdelegated'=> $usersdelegated,
            'isDelegatedToMe' => $isDelegatedToMe,
            'uservalidant'  => $uservalidant,
            "menu_color"    => "bleu",
            'users'         => $users,
            'classeursId'  => urlencode(serialize($entity->getId()))
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
//        $em->getRepository('SesileClasseurBundle:Classeur')->set_user_visible($classeur, $request->get("visibilite"));

        $em->flush();

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
     * @Route("/sign_pdf", name="classeur_valider_pdf")
     * @Method("POST")
     *
     */
    public function signPDFAction(Request $request)
    {

        var_dump("Welcome");
        var_dump($request);
        $em = $this->getDoctrine()->getManager();
        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->find($request->get("id"));

        if (!$classeur) {
            throw $this->createNotFoundException('Unable to find Classeur entity.');
        }

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
//        $em->getRepository('SesileClasseurBundle:Classeur')->set_user_visible($classeur, $visibilite);


//        $classeur->valider($em);
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
     * Valider_et_signer an existing Classeur entity from JWS.
     *
     * @Route("/valider_classeur_jws/{id}/{user_id}/{valid}", name="valider_classeur_jws")
     * @Method("GET")
     *
     */
    public function valider_classeur_jws(Request $request, $id, $user_id, $valid = -1)
    {

        if($valid == 1) {

            // Connexion BDD
            $em = $this->getDoctrine()->getManager();

            // Récup de l entité classeur et user
            $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->find($id);
            $user = $em->getRepository('SesileUserBundle:User')->findOneById($user_id);

            // Test si le classeur exite
            if (!$classeur) {
                throw $this->createNotFoundException('Unable to find Classeur entity.');
            }

            // Test si le classeur est délégué
            $isvalidator = $em->getRepository('SesileClasseurBundle:Classeur')->isDelegatedToUser($classeur, $user);


            // Validation du classeur
            $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->validerClasseur($classeur);
            $classeur->setCircuit($user->getId());
            $em->flush();

            // Ajout d'une action pour le classeur
            $action = new Action();

            $commentaire = "Classeur signé.";
            $action->setCommentaire($commentaire);

            $action->setClasseur($classeur);
            $action->setUser($user);
            $action_libelle = "Signature";

            // Si le user est un délégué alors on rajoute un de qui il a reçue sa délégation
            if ($isvalidator) {
                $delegators = $em->getRepository('SesileDelegationsBundle:Delegations')->getDelegantsForUser($user);
                foreach ($delegators as $delegator) {
                    $action_libelle .= " (Délégation reçue de " . $delegator->getDelegant()->getPrenom() . " " . $delegator->getDelegant()->getNom() . ")";
                }
            }
            $action->setAction($action_libelle);
            $em->persist($action);
            $em->flush();

            // Envoie du mail de confirmation
            $this->sendValidationMail($classeur, $user);

            return new JsonResponse(array("classeur_valid" => "1"));
        }
        elseif ($valid == 0) {
            return new JsonResponse(array("classeur_valid" => "0"));
        }
        else {
            return new JsonResponse(array("classeur_valid" => "-1"));
        }
    }


    /**
     * @Route("/statusclasseur/{id}", name="status_classeur",  options={"expose"=true})
     *
     */
    public function statusClasseurAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();
        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->findOneById($id);


        return new JsonResponse($classeur->getStatus());
    }


    /**
     * Valider_et_signer an existing Classeur entity.
     *
     * @Route("/signform/{id}/{role}", name="signform")
     * @Template()
     *
     */
    public function signAction(Request $request, $id, $role = null)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        if($request->request->get('circuit')) {
            $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->find($id);
//            $circuit = $request->request->get('circuit');
            $classeur->setCircuit($user->getId());
            $em->flush();
        }
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

        // Gestion du role de l utilisateur
        // Dans le cas l utilisateur a plusieurs roles
        if(null !== $role) {
            $roleUser = $em->getRepository('SesileUserBundle:UserRole')->findOneById($role);
            $role = $roleUser->getUserRoles();
        }
        // Dans le cas l utilisateur a un seul role
        else {
            $roleUser = $em->getRepository('SesileUserBundle:UserRole')->findByUser($user);
            if (!empty($roleUser)) {
                $role = $roleUser[0]->getUserRoles();
            } else {
                $role = '';
            }
        }

        $servername = $_SERVER['HTTP_HOST'];
        $url_applet = $this->container->getParameter('url_applet');

        return array(
            'user'      => $user,
            'role'      => $role,
            'classeur'  => $classeur,
            'session_id' => $session->getId(),
            'docstosign' => $docstosign,
            'servername' => $servername,
            "url_applet" => $url_applet
        );

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
//        $em->getRepository('SesileClasseurBundle:Classeur')->updateInfosClasseurs($request, $id);
        $this->updateInfosClasseurs($request, $id, $em);
        /*if ($request->isMethod('post')) {
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
        }*/

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
     *
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
     * Valider_et_signer an existing Classeur entity.
     *
     * @Route("/signPdfForm/{id}", name="signPdfDocAction")
     * @Template()
     *
     */
    public function signPdfDocAction(Request $request, $id)
    {
        //var_dump($request->get("moncul"));exit;
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        if($request->request->get('circuit')) {
            $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->find($id);
            $classeur->setCircuit($user->getId());
            $em->flush();
        }
        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->findOneById($id);


        $session = $this->get('session');
        $session->start();

        $tmpdocs = $classeur->getPdfDocuments();

        $docstosign = array();

        foreach ($tmpdocs as $key => $value) {
            $tmpdo = array();
            $tmpdo['name'] = $value->getName();
            $tmpdo['id'] = $value->getId();
            $tmpdo['repourl'] = $value->getRepourl();
            $docstosign[$key] = $tmpdo;
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

        }


        $servername = $_SERVER['HTTP_HOST'];
        $url_applet = $this->container->getParameter('url_applet');

        return array(
            'user'      => $user,
            'classeur'  => $classeur,
            'session_id' => $session->getId(),
            'docstosign' => $docstosign,
            'servername' => $servername,
            "url_applet" => $url_applet
        );

    }

    /**
     * Génération du fichier JNLP permettant l exécution de l application de signature
     *
     * @Route("/jnlpsignerfiles/{id}/{role}", name="jnlpSignerFiles")
     *
     */
    public function jnlpSignerFilesAction(Request $request, $id, $role = null) {

        // On recupere les ids des classeurs a signer
        $ids = unserialize(urldecode($id));

        $arguments = array();

        // Connexion BDD
        $em = $this->getDoctrine()->getManager();

        // User courant
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // MAJ de l etat de la visibilité, nom, description, date de validation
//        $em->getRepository('SesileClasseurBundle:Classeur')->updateInfosClasseurs($request, $ids);
        $this->updateInfosClasseurs($request, $ids, $em);

        // Infos JSON liste des fichiers
//        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->findOneById($id);
        $classeurs = $em->getRepository('SesileClasseurBundle:Classeur')->findById($ids);

        // Gestion du role de l utilisateur
        // Dans le cas l utilisateur a plusieurs roles
        if(null !== $role) {
            $roleUser = $em->getRepository('SesileUserBundle:UserRole')->findOneById($role);
            $roleArg = $roleUser->getUserRoles();
        }
        // Dans le cas l utilisateur a un seul role
        else {
            $roleUser = $em->getRepository('SesileUserBundle:UserRole')->findByUser($user);
            if (!empty($roleUser)) {
                $roleArg = $roleUser[0]->getUserRoles();
            } else {
                $roleArg = 'Non renseigné';
            }
        }
//        $documents = $classeur->getDocuments();
        $classeursJSON = array();
//        $documentsJSON = array();

        // Generation du token pour les documents
        $token = uniqid();

        // Pour chaque classeurs
        foreach ($classeurs as $classeur) {
//            var_dump("Classeur : " . $classeur->getId() . " " . $classeur->getNom() . "<br>");

            // Recuperation url de retour pour la validation du classeur
            $url_valid_classeur = $this->generateUrl('valider_classeur_jws', array('id' => $classeur->getId(), 'user_id' => $user->getId()), UrlGeneratorInterface::ABSOLUTE_URL);

            $documentsJSON = array();


            foreach ($classeur->getDocuments() as $document) {

                if(!$document->getSigned()) {
//                    var_dump("Document : " . $document->getName() . "<br>");

                    $document->setToken($token);

                    $typeDocument = $document->getType();

                    // Definition du type de document a transmettre au JWS
                    if($typeDocument == "application/xml" && $classeur->getType()->getId() == 2) {
                        $typeJWS = "xades-pes";
                    } else if($typeDocument == "application/xml") {
                        $typeJWS = "xades";
                    } else if($typeDocument == "application/pdf") {
                        $typeJWS = "pades";
                    } else {
                        $typeJWS = "cades";
                    }

                    $documentsJSON[] = array(
                        'name'          => $document->getName(),
                        'type'          => $typeJWS,
                        'description'   => $classeur->getDescription(),
                        'url_file'      => $this->generateUrl('download_jws_doc', array('name' => $document->getrepourl()), UrlGeneratorInterface::ABSOLUTE_URL),
                        'url_upload'    => $this->generateUrl('upload_document_fron_jws', array('id' => $document->getId()), UrlGeneratorInterface::ABSOLUTE_URL)
                    );
                }

            }

            // On enregistre les modifications du document en bas
            $em->flush();

            // On incrémente les arguments passés
            $classeursJSON[] = array(
                'name' => $classeur->getNom(),
                'url_valid_classeur' => $url_valid_classeur,
                'documents' => $documentsJSON
            );
        }
//        $classeursJSON[] = $documentsJSON;
//        var_dump($classeursJSON); die();
        $arguments[] = json_encode($classeursJSON);


        // Récupération des infos du user
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $arguments[] = ($user->getPays() === null) ? "Non renseigné" : $user->getPays();
        $arguments[] = ($user->getVille() === null) ? "Non renseignée" : $user->getVille();
        $arguments[] = ($user->getCp() === null) ? "Non renseigné" : $user->getCp();
        $arguments[] = $roleArg;

        // On passse le token
        $arguments[] = $token;


        // Création de la réponse pour envoyer le fichier JNLP générer automatiquement
        $response = new Response();
        // Envoie des bonnes headers pour le JNLP
        $response->headers->set('Content-type', 'application/x-java-jnlp-file');
        $response->headers->set('Content-disposition', 'filename="signer.jnlp"');

        $url_applet = 'http://' . $this->container->getParameter('url_applet') . '/jws/sesile-jws-signer.jar';

        $contentSigner = '<?xml version="1.0" encoding="utf-8"?>
<jnlp spec="1.0+" codebase="' . $this->generateUrl('jnlpSignerFiles', array('id' => $id, 'role' => $role), UrlGeneratorInterface::ABSOLUTE_URL) . '">
  <information>
    <title>SESILE JWS Signer</title>
    <vendor>SICTIAM</vendor>
    <homepage href="' . $url_applet . '"/>
    <description>Application de de signature de documents</description>
    <description kind="short">Application de signatures</description>
    <offline-allowed/>
  </information>
<security><all-permissions /></security>
  <resources>
    <j2se version="1.8" initial-heap-size="128m" max-heap-size="1024m"/>
    <jar href="' . $url_applet . '"/>
  </resources>
  <application-desc >';

        foreach ($arguments as $argument) {
            $contentSigner .= '<argument>' . $argument . '</argument>';
        }

        $contentSigner .= '</application-desc>
</jnlp>';
        $response->setContent($contentSigner);

        return $response;

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
     * @return \Symfony\Component\Form\Form The form
     */
    public function formulaireFactory(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('SesileUserBundle:Groupe')->findBy(array(
            "collectivite" => $this->get("session")->get("collectivite")
        ));

        $type = $request->request->get('type', 'elclassico');

        switch ($type) {
            // Le cas ou c est Helios
            case 2:
                return $this->render(
                    'SesileClasseurBundle:Formulaires:elpez.html.twig', array("groupes" => $entities)
                );
                break;
            default:
                return $this->render(
                    'SesileClasseurBundle:Formulaires:elclassico.html.twig', array("groupes" => $entities)
                );
                break;
        }
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
            /*if ($request->request->get(str_replace(".", "_", $file->getBaseName())) == null) {
                var_dump('ok 2');
                unlink($file->getPathname());
            } else {*/ // Pas d'erreur, on crée un document correspondant
                $document = new Document();
//                $document->setName($request->request->get(str_replace(".", "_", $file->getBaseName())));
                $document->setName($request->request->get('serverfilename')[$k]);
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


//            }
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

    private function sendValidationMail($classeur, $currentvalidant = null)
    {
        $em = $this->getDoctrine()->getManager();
        if (is_null($currentvalidant)) {
//            $currentvalidant = $classeur->getValidant();
            $currentvalidant = $this->getUser();
        }
        $coll = $em->getRepository("SesileMainBundle:Collectivite")->find($this->get("session")->get("collectivite"));
        $c_user = $em->getRepository("SesileUserBundle:User")->findOneById($currentvalidant);
        $validant_obj = $em->getRepository("SesileUserBundle:User")->findOneById($classeur->getUser());
        $env = new \Twig_Environment(new \Twig_Loader_String());
        $body = $env->render($coll->getTextMailwalid(),
            array(
                'deposant' => $validant_obj->getPrenom() . " " . $validant_obj->getNom(),
                'validant' => $c_user->getPrenom() . " " . $c_user->getNom(),
                'role' => $c_user->getRole(),
                'qualite' => $c_user->getQualite(),
                'titre_classeur' => $classeur->getNom(),
                'date_limite' => $classeur->getValidation(),
                'type' => strtolower($classeur->getType()->getNom()),
                "lien" => '<a href="http://' . $this->container->get('router')->getContext()->getHost() . $this->generateUrl('classeur_edit', array('id' => $classeur->getId())) . '">voir le classeur</a>'
            )
        );

//        $validant_obj = ($classeur->getValidant() == 0)?$em->getRepository('SesileUserBundle:User')->find($classeur->getUser()):$em->getRepository('SesileUserBundle:User')->find($classeur->getValidant());

        $validants_id = $classeur->getUser();
        $validants = $em->getRepository("SesileUserBundle:User")->findById($validants_id);

        foreach($validants as $validant_obj) {
            if ($validant_obj != null) {
                $this->sendMail("SESILE - Classeur validé", $validant_obj->getEmail(), $body);
            }
        }
    }

    private function sendCreationMail($classeur) {
        $em = $this->getDoctrine()->getManager();
        $coll = $em->getRepository("SesileMainBundle:Collectivite")->find($this->get("session")->get("collectivite"));
//        $c_user = $em->getRepository("SesileUserBundle:User")->find($classeur->getPrevValidant());
        $d_user = $em->getRepository("SesileUserBundle:User")->find($classeur->getUser());
//        $currentvalidant = $this->getUser();
//        $c_user = $em->getRepository("SesileUserBundle:User")->findOneById($currentvalidant);
        $env = new \Twig_Environment(new \Twig_Loader_String());

        $validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($classeur);
//        foreach($classeur->getValidant() as $validant) {
        foreach($validants as $validant) {

            if ($validant != null) {
                $body = $env->render($coll->getTextmailnew(),
                    array(
                        'deposant' => $d_user->getPrenom() . " " . $d_user->getNom(),
                        'validant' => $validant->getPrenom() . " " . $validant->getNom(),
                        'role' => $d_user->getRole(),
                        'qualite' => $d_user->getQualite(),
                        'titre_classeur' => $classeur->getNom(),
                        'date_limite' => $classeur->getValidation(),
                        'type' => strtolower($classeur->getType()->getNom()),
                        "lien" => '<a href="http://'.$this->container->get('router')->getContext()->getHost().$this->generateUrl('classeur_edit', array('id' => $classeur->getId())) . '">valider le classeur</a>'
                    )
                );
                $this->sendMail("SESILE - Nouveau classeur à valider", $validant->getEmail(), $body);
            }
        }
    }

    private function sendRefusMail($classeur,$motif) {
        $em = $this->getDoctrine()->getManager();
        $coll = $em->getRepository("SesileMainBundle:Collectivite")->find($this->get("session")->get("collectivite"));
//        $c_user = $em->getRepository("SesileUserBundle:User")->find($classeur->getValidant());
        $c_user = $em->getRepository("SesileClasseurBundle:Classeur")->classeurValidator($classeur, $this->getUser());

//        var_dump($classeur->getId(), $classeur->getValidant()); die();

        $env = new \Twig_Environment(new \Twig_Loader_String());
        $body = $env->render($coll->getTextmailrefuse(),
            array(
                'validant' => $c_user->getPrenom()." ".$c_user->getNom(),
                'role' => $c_user->getRole(),
                'qualite' => $c_user->getQualite(),
                'titre_classeur' => $classeur->getNom(),
                'date_limite' => $classeur->getValidation(),
                'type' => strtolower($classeur->getType()->getNom()),
                "lien" => '<a href="http://'.$this->container->get('router')->getContext()->getHost().$this->generateUrl('classeur_edit', array('id' => $classeur->getId())) . '">voir le classeur</a>',
                "motif" => $motif
            )
        );

        $deposant = $em->getRepository('SesileUserBundle:User')->find($classeur->getUser());

        if ($deposant != null) {
            $this->sendMail("SESILE - Classeur refusé", $deposant->getEmail(), $body);
        }
    }

    /**
     * Retourne le circuit associé à un groupe pour un user donné
     * @param Groupe $group
     * @param User $user
     * @return string les id user du circuit dans l'ordre
     */
    private $ordre;

    private function recursivesortHierarchie($hierarchie, $curr, $recurs = 0) {
//        static $recurs = 0;
        foreach($hierarchie as $k => $groupeUser) {
            if($groupeUser->getUser()->getId() == $curr ) {
//                var_dump($recurs, $curr);
                if($recurs > 0) {
                    $this->ordre .= $groupeUser->getUser()->getId().",";
                }

                if($curr != 0 ) {
                    $recurs++;
                    $this->recursivesortHierarchie($hierarchie, $groupeUser->getParent(), $recurs);
                }

            }
        }
        $this->ordre = rtrim($this->ordre, ",");
        return $this->ordre;
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
     * @param $classeur
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