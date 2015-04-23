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
use Sesile\ClasseurBundle\Form\ClasseurType;
use Sesile\ClasseurBundle\Entity\Action;
use Sesile\DelegationsBundle\Entity\Delegations;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;

/**
 * Classeur controller.
 *
 */
class ClasseurController extends Controller {
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
    public function listeAction() {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SesileUserBundle:User')->findOneById($this->getUser()->getId());

        foreach ($entities->getClasseurs() as $entity) {
            $user = $em->getRepository('SesileUserBundle:User')->findOneById($entity->getValidant());
            $entity->validantName = $user ? $user->getPrenom() . " " . $user->getNom() : " ";
        }
//        $entities = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->getClasseursVisibles($this->getUser()->getId());

//        foreach ($entities as $key => $value) {
//            $user = $em->getRepository('SesileUserBundle:User')->findOneById($value->getValidant());
//            $value->validantName = $user ? $user->getPrenom() . " " . $user->getNom() : " ";
//        }

        return array(
            'classeurs' => $entities->getClasseurs(),
            "menu_color" => "bleu"
        );
    }

    /**
     * Liste des classeurs en cours
     *
     * @Route("/liste/retire", name="liste_classeurs_retired")
     * @Method("GET")
     * @Template("SesileClasseurBundle:Classeur:retired.html.twig")
     */
    public function retiredAction()
    {

        return array("menu_color" => "bleu");
    }

    /**
     * @Route("/ajax/listRetired", name="ajax_classeurs_list_retired")
     * @Template()
     */
    public function listAjaxRetiredAction(Request $request)
    {
        $get = $request->query->all();
        $columns = array('Nom', 'Creation', 'Validation', 'Validant', 'Type', 'Status', 'Id');
        $get['colonnes'] = &$columns;

        $em = $this->getDoctrine()->getManager();
        $rResult = $em->getRepository('SesileClasseurBundle:Classeur')->findByStatus(3);

        // $em->getRepository('SesileClasseurBundle:ClasseursUsers')->countClasseursVisiblesForDTables($this->getUser()->getId())
        $output = array(
            "draw" => $get["draw"],
            "recordsTotal" => 0,//$em->getRepository('SesileClasseurBundle:ClasseursUsers')->countClasseursVisiblesForDTables($this->getUser()->getId()),
            "recordsFiltered" => count($rResult),
            "data" => array()
        );

        foreach ($rResult as $aRow) {
            $row = array();
            for ($i = 0; $i < count($columns); $i++) {
                if ($columns[$i] == "Creation") {
                    $row[] = $aRow->{"get" . $columns[$i]}()->format('d/m/Y H:i');
                } elseif ($columns[$i] == "Validation") {
                    $row[] = $aRow->{"get" . $columns[$i]}()->format('d/m/Y');
                } elseif ($columns[$i] == "Validant") {
                    $intervenant = $aRow->{"get" . $columns[$i]}();

                    $row[] = ($intervenant == 0) ? "" : $em->getRepository('SesileUserBundle:User')->find($intervenant)->getNom();
                } elseif ($columns[$i] == 'Type') {
//                    var_dump($aRow->getType()->getNom());
                    $row[] = $aRow->getType()->getNom();
                } elseif ($columns[$i] != ' ') {
                    $row[] = $aRow->{"get" . $columns[$i]}();
                }
            }
            $output['data'][] = $row;
        }

        unset($rResult);

        return new Response(
            json_encode($output)
        );
    }

    // SUPPRIMER UN CLASSEUR

    /**
     * Edits an existing Classeur entity.
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
        return $this->redirect($this->generateUrl('index'));
    }

    /**
     * Edits an existing Classeur entity.
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
        return new JsonResponse(array('ret' => true));
    }

    /**
     * @Route("/ajax/list", name="ajax_classeurs_list")
     * @Template()
     */
    public function listAjaxAction(Request $request) {
        $get = $request->query->all();
        $columns = array( 'Nom', 'Creation', 'Validation', 'Validant', 'Type', 'Status', 'Id' );
        $get['colonnes'] = &$columns;

        $em = $this->getDoctrine()->getManager();
        $rResult = $em->getRepository('SesileClasseurBundle:Classeur')->getClasseursVisiblesForDTablesV3($this->getUser()->getId(), $get);

        $recordsFiltered = count($em->getRepository('SesileUserBundle:User')->findOneById($this->getUser()->getId())->getClasseurs());

        $output = array(
            "draw" => $get["draw"],
//            "recordsTotal" => $em->getRepository('SesileClasseurBundle:ClasseursUsers')->countClasseursVisiblesForDTables($this->getUser()->getId()),
            "recordsTotal" => count($rResult),
//            "recordsFiltered" => $rResult["count"],
            "recordsFiltered" => $recordsFiltered,
            "data" => array()
        );

//        foreach($rResult["data"] as $aRow) {
        foreach($rResult as $aRow) {
            $row = array();
            for ($i = 0 ; $i < count($columns) ; $i++) {
                if ($columns[$i] == "Creation") {
                    $row[] = $aRow->{"get".$columns[$i]}()->format('d/m/Y H:i');
                } elseif ($columns[$i] == "Validation") {
                    $row[] = $aRow->{"get".$columns[$i]}()->format('d/m/Y');
                } elseif ($columns[$i] == "Validant") {
                    $intervenant = $aRow->{"get".$columns[$i]}();

                    $row[] = ($intervenant == 0)?"":$em->getRepository('SesileUserBundle:User')->find($intervenant)->getNom();
                } elseif ($columns[$i] == "Id") {
                    if ($aRow->getType()->getNom() == 'Helios') {
                        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->findOneById($aRow->{"get" . $columns[$i]}());

                        if (count($classeur->getDocuments())) {
                            $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($classeur->getDocuments()[0]);
                            $row[] = array('id' => $aRow->{"get" . $columns[$i]}(), 'idDoc' => $doc->getId());
                        } else {
                            $row[] = array('id' => $aRow->{"get" . $columns[$i]}(), 'idDoc' => 0);
                        }
                    } else {
                        $row[] = array('id' => $aRow->{"get" . $columns[$i]}(), 'idDoc' => 0);
                    }

                } elseif ($columns[$i] == "Type") {
                    $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->findOneById($aRow->{"get" . $columns[6]}());
                    $Type = $classeur->getType()->getNom();
                    $row[] = $Type;
                } elseif ($columns[$i] != ' ') {
                    $row[] = $aRow->{"get".$columns[$i]}();
                }
            }
            $output['data'][] = $row;
        }

        unset($rResult);

        return new Response(
            json_encode($output)
        );
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
        return $this->aValiderAction();
    }


    /**
     * Liste des classeurs à valider
     *
     * @Route("/a_valider", name="classeur_a_valider")
     * @Method("GET")
     * @Template("SesileClasseurBundle:Classeur:liste.html.twig")
     */
    public function aValiderAction() {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('SesileDelegationsBundle:delegations');
        $usersdelegated = $repository->getUsersWhoHasMeAsDelegateRecursively($this->getUser());

        if(!empty($usersdelegated)) {
            $entities = $em->getRepository('SesileClasseurBundle:Classeur')->findBy(
                array(
                    "validant" => $usersdelegated,
                    "status" => 1
                ));
        }
        else{
            $entities = $em->getRepository('SesileClasseurBundle:Classeur')->findBy(
                array(
                    "validant" => $this->getUser(),
                    "status" => 1
                ));
        }

        return array(
            'classeurs' => $entities,
            "menu_color" => "bleu"
        );
    }

    /**
     * Liste des classeurs à valider pour datatables version de test simplifiée
     *
     * @Route("/ajax/a_validertest", name="ajax_a_validertest")
     * @Method("GET")
     * @Template()
     */
    public function aValiderAjaxtestAction(Request $request) {

        $get = $request->query->all();
        $output = array(
            "draw" => $get["draw"],
            "recordsTotal" => 3,
            "recordsFiltered" => 4,
            "data" => array()
        );

        // Conection BDD
        $em = $this->getDoctrine()->getManager();
        // On chope les delegations
        $repository = $this->getDoctrine()->getRepository('SesileDelegationsBundle:delegations');
        $usersdelegated = $repository->getUsersWhoHasMeAsDelegateRecursively($this->getUser());

        // Et on definit le validant
        $validant = "";
        foreach($usersdelegated as $ud) {
            $validant .= $ud->getId().",";
        }
        $validant = (!empty($validant))?substr($validant, 0, -1):$this->getUser()->getId();

        // Pour modifier l ordre d affichage
        if(isset($get['order'])) {

            $columns = array( 'nom', 'creation', 'validation', 'validant', 'type', 'status', 'id');
            if($columns[$get["order"][0]["column"]] == "type") {
                $order = array('some_attribute', 'ASC');
            } else {
                $order = array($columns[$get["order"][0]["column"]] => $get['order'][0]["dir"]);
            }

        } else {
            $order = "";
        }

        $classeurs = $this->getDoctrine()->getRepository('SesileClasseurBundle:Classeur')
            ->findBy(
                array('validant' => $validant, 'status' => 1),
                $order
            );



        foreach ($classeurs as $classeur) {
            $row = array();
            $row[] = $classeur->getNom();
            $row[] = $classeur->getCreation()->format('d/m/Y H:i');
            $row[] = $classeur->getValidation()->format('d/m/Y');
            $row[] = $em->getRepository('SesileUserBundle:User')->find($classeur->getValidant())->getNom();
            $row[] = $classeur->getType()->getNom();
            $row[] = $classeur->getStatus();
            $row[] = $this->linkClasseur($classeur->getType()->getId(), $classeur->getId());
            $output['data'][] = $row;
        }

        return new Response(
            json_encode($output)
        );

    }

    // Fonction pour la creation du lien vers le document
    private function linkClasseur ($idType, $id) {
        if ($idType == '2') {
            $em = $this->getDoctrine()->getManager();
            $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->findOneById($id);

            if (count($classeur->getDocuments())) {
                $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($classeur->getDocuments()[0]);
                $row = array('id' => $id, 'idDoc' => $doc->getId());
            } else {
                $row = array('id' => $id, 'idDoc' => 0);
            }
        } else {
            $row = array('id' => $id, 'idDoc' => 0);
        }
        return $row;
    }

    /**
     * Liste des classeurs à valider pour datatables
     *
     * @Route("/ajax/a_valider", name="ajax_a_valider")
     * @Method("GET")
     * @Template()
     */
    public function aValiderAjaxAction(Request $request) {
        $get = $request->query->all();

        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('SesileDelegationsBundle:delegations');
        $usersdelegated = $repository->getUsersWhoHasMeAsDelegateRecursively($this->getUser());

        $columns = array( 'nom', 'creation', 'validation', 'validant', 'type', 'status', 'id');
        $columnsBis = array( 'nom', 'creation', 'validation', 'validant', '1', 'status', 'id');
        $aColumns = array();
        foreach($columns as $value) $aColumns[] = 'c.'.$value;
        $aColumnStr = str_replace(" , ", " ", implode(", ", $aColumns));

//        $aColumnStr .= ", IDENTITY(c.type)";
        $aColumnStr = str_replace("c.type", "IDENTITY(c.type)", $aColumnStr);

        $validant = "";
        foreach($usersdelegated as $ud) {
            $validant .= $ud->getId().",";
        }
//        $validant = (!empty($validant))?substr($validant, 0, -1):$this->getUser()->getId();
        $validant = (!empty($validant)) ? $validant.$this->getUser()->getId() : $this->getUser()->getId();
//        var_dump($validant);
//        $sql = "SELECT $aColumnStr FROM SesileClasseurBundle:Classeur c WHERE c.validant = '$validant' AND c.status = 1";
//        $sql = "SELECT $aColumnStr FROM SesileClasseurBundle:Classeur c WHERE c.validant IN ($validant) AND c.status = 1";
        $sql = "SELECT $aColumnStr FROM SesileClasseurBundle:Classeur c WHERE c.validant IN ($validant) AND c.status IN (1,4)";

        $query = $em->createQuery($sql);
        $rResult = $query->getResult();


        $output = array(
            "draw" => $get["draw"],
            "recordsTotal" => count($rResult),
            "data" => array()
        );

        // Il est temps de faire le barbu ...
        // TODO attention les mm requêtes sont passées plusieurs fois (il faut faire le count une fois puis le CALC ROW)
        $order = "";
        if(isset($get['order'])) {
            $order = " ORDER BY ".$aColumns[$get["order"][0]["column"]]." ".$get['order'][0]["dir"]." ";
        }

        $where = '';
        if (isset($get['search']) && $get['search']['value'] != '') {
            $globalSearch = array();
            $str = $get['search']['value'];

            for ($i=0; $i < count($get['columns']) ; $i++) {
                if ($get['columns'][$i]['searchable'] == 'true') {
                    $requestColumn = strtolower($aColumns[$i]);
                    if ($requestColumn != 'c.type') {
                        $binding = "'%".$str."%'";
                        $globalSearch[] = $requestColumn." LIKE ".$binding;
                    }

                }
            }
            if (count($globalSearch)) {
                $where = '('.implode(' OR ', $globalSearch).')';
                $where = 'AND '.$where;
            }
        }

//        $sql = "SELECT $aColumnStr FROM SesileClasseurBundle:Classeur c WHERE c.validant IN($validant) AND c.status = 1 $where $order";
        $sql = "SELECT $aColumnStr FROM SesileClasseurBundle:Classeur c WHERE c.validant IN ($validant) AND c.status IN (1,4) $where $order";


//        var_dump($sql);

        $query = $em->createQuery($sql);

        if ( isset( $get['start'] ) && $get['length'] != '-1' ) {
            $query->setFirstResult((int)$get['start'])->setMaxResults((int)$get['length']);
        }

        $rResult = $query->getResult();

        foreach($rResult as $aRow) {
            $row = array();
            for ($i = 0 ; $i < count($columns) ; $i++) {
//                var_dump($columns[$i]);
                if ($columns[$i] == "creation") {
                    $row[] = $aRow[$columns[$i]]->format('d/m/Y H:i');
                } elseif ($columns[$i] == "validation") {
                    $row[] = $aRow[$columns[$i]]->format('d/m/Y');
                } elseif ($columns[$i] == "validant") {
                    $intervenant = $aRow[$columns[$i]];
                    $row[] = ($intervenant == 0) ? "" : $em->getRepository('SesileUserBundle:User')->find($intervenant)->getPrenom() . " " .$em->getRepository('SesileUserBundle:User')->find($intervenant)->getNom();
                } elseif ($columns[$i] == "id") {

                    if ($em->getRepository('SesileClasseurBundle:Classeur')->findOneById($aRow['id'])->getType()->getNom() == 'Helios') {

                        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->findOneById($aRow[$columns[$i]]);
                        if (count($classeur->getDocuments())) {
                            $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($classeur->getDocuments()[0]);
                            $row[] = array('id' => $aRow[$columns[$i]], 'idDoc' => $doc->getId());
                        } else {
                            $row[] = array('id' => $aRow[$columns[$i]], 'idDoc' => 0);
                        }

                    } else {
                        $row[] = array('id' => $aRow[$columns[$i]], 'idDoc' => 0);
                    }

                }elseif($columns[$i] === 'type'){
//                    var_dump('test');
                    $row[] = $em->getRepository('SesileClasseurBundle:Classeur')->findOneById($aRow['id'])->getType()->getNom();
                }
                elseif ($columns[$i] != ' ') {
//                    var_dump($aRow);exit;
                    $row[] = $aRow[$columns[$i]];
                }
            }
            $output['data'][] = $row;
        }

        unset($rResult);

//        $sql = "SELECT $aColumnStr FROM SesileClasseurBundle:Classeur c WHERE c.validant = '$validant' AND c.status = 1 $order";
        $sql = "SELECT $aColumnStr FROM SesileClasseurBundle:Classeur c WHERE c.validant IN ($validant) AND c.status IN (1,4) $order";
        $query = $em->createQuery($sql);
        $rResult = $query->getResult();
        $output["recordsFiltered"] = count($rResult);
        unset($rResult);

        return new Response(
            json_encode($output)
        );


        unset($rResult);

        return new Response(
            json_encode($output)
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
//        $entities = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->getClasseursRetractables($this->getUser()->getId());
        $entities = $em->getRepository('SesileClasseurBundle:Classeur')->findAll();
        $delegants = $em->getRepository('SesileDelegationsBundle:Delegations')->getUsersWhoHasMeAsDelegate($this->getUser()->getId());
        foreach ($delegants as $delegant) {
            $users[] = $delegant->getId();
        }
        $users[] = $this->getUser()->getId();

        foreach ($entities as $k => $entity) {
            if ($entity->isRetractableByDelegates($users)) {
                $user = $em->getRepository('SesileUserBundle:User')->findOneById($entity->getValidant());
                $entity->validantName = $user ? $user->getPrenom() . " " . $user->getNom() : " ";
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
        // Mise en place d une securite avec le csrf v1...
        $csrf_token = $request->request->get('_csrf_token');
        $csrf = $this->get('form.csrf_provider');
        if(!$csrf->isCsrfTokenValid('authenticate', $csrf_token)) {
            throw $this->createNotFoundException('Unable to submit the form.');
        }

        $em = $this->getDoctrine()->getManager();
        $classeur = new Classeur();
        $classeur->setNom($request->request->get('name'));
        $classeur->setDescription($request->request->get('desc'));
        list($d, $m, $a) = explode("/", $request->request->get('validation'));
        $valid = new \DateTime($m . "/" . $d . "/" . $a);
        $classeur->setValidation($valid);

        $type = $em->getRepository('SesileClasseurBundle:TypeClasseur')->findOneById($request->request->get('type'));
        $classeur->setType($type);
        $circuit = $request->request->get('circuit');
        $classeur->setCircuit($circuit);
        $classeur->setUser($this->getUser()->getId());

        $classeur->setVisibilite($request->request->get('visibilite'));

        // enregistrer les users du circuit
        $users = explode(',', $circuit);
        $users[] = $this->getUser()->getId();
        // Fonction pour enregistrer dans la table Classeur_visible
        $usersCV = $this->classeur_visible($request->request->get('visibilite'), $users, $request->request->get('userGroupe'));
        foreach ($usersCV as $userCV) {
            $userVisible = $em->getRepository('SesileUserBundle:User')->findOneById($userCV->getId());
            $classeur->addVisible($userVisible);
        }

        $em->persist($classeur);
        $em->flush();


        // enregistrer les users du circuit
        //$users = explode(',', $circuit);

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

                $action = new Action();
                $action->setClasseur($classeur);
                $action->setUser($this->getUser());


                $action->setAction("Ajout du document " . $document->getName());
                $em->persist($action);


                $em->flush();
                $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($document, "Ajout du document au classeur " . $classeur->getNom(), null);


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
    public function newAction() {
        // On recupere tous les types de classeur et les groupes
        $em = $this->getDoctrine()->getManager();
//        $groupes = $em->getRepository('SesileUserBundle:Groupe')->findByCollectivite($this->get("session")->get("collectivite"));
//        $types = $em->getRepository('SesileClasseurBundle:TypeClasseur')->findAll();
        $types = $em->getRepository('SesileClasseurBundle:TypeClasseur')->findBy(array(), array('nom' => 'ASC'));

        // Nouveau code pour afficher l ordre des groupes
        $id_user = $this->get('security.context')->getToken()->getUser()->getId();
        $groupes_du_user = $em->getRepository('SesileUserBundle:UserGroupe')->findByUser($this->getUser());
        if(!$groupes_du_user) {
            $this->get('session')->getFlashBag()->add('notice', 'Vous ne faites parti d\'aucun service organisationnel.');
            return $this->redirect($this->generateUrl('classeur'));
        }
        foreach($groupes_du_user as $group) {
            $hierarchie = $em->getRepository('SesileUserBundle:UserGroupe')->findBy(array("groupe" => $group->getGroupe()), array("parent" => "DESC"));
            $this->ordre = "";
            $circuits[] = array(
                "id" => $group->getGroupe()->getId(),
                "name" => $group->getGroupe()->getNom(),
                "ordre" => $this->recursivesortHierarchie($hierarchie, $id_user),
                "groupe" => true
            );
        }
        // FIN du Nouveau code pour afficher l ordre des groupes

        // CSRF
        $csrf_token = $this->get('form.csrf_provider')->generateCsrfToken('authenticate');

        return array(
//            "userGroupes" => $groupes,
            "typeClasseurs" => $types,
            "menu_color" => "bleu",
            "userGroupes" => $circuits,
            "csrf_token" => $csrf_token
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
        $entity = $em->getRepository('SesileClasseurBundle:Classeur')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Classeur entity.');
        }

        $users = $em->getRepository('SesileUserBundle:User')->findBy(array(
            "collectivite" => $this->get("session")->get("collectivite"), 'enabled' => 1
        ), array("Nom" => "ASC"));

        $repositorydelegates = $this->getDoctrine()->getRepository('SesileDelegationsBundle:delegations');
//        $repositoryusers = $this->getDoctrine()->getRepository('SesileUserBundle:user');


        $usersdelegated = $repositorydelegates->getUsersWhoHasMeAsDelegate($this->getUser()->getId());
//        $usersdelegated[]=$this->getUser();

        // Definition des users pour le bouton retractable


        foreach($usersdelegated as $userdelegated) {
            $delegants[] = $userdelegated->getId();
        }
//        $uservalidant = $usersdelegated[0];
//        $isDelegatedToMe = true;


        $usersdelegated[] = $this->getUser();
        $delegants[] = $this->getUser()->getId();
//        var_dump($delegants[0], $entity->getValidant());

//        $isDelegatedToMe = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->isDelegatedToUser($entity, $this->getUser());
        $isRetractableByDelegates = $entity->isRetractableByDelegates($delegants);

        if (($entity->getValidant() == $delegants[0] || ($isRetractableByDelegates && $entity->getPrevValidant() == $delegants[0])) && $this->getUser()->getId() != $delegants[0]) {
            $isDelegatedToMe = true;
            $uservalidant = $usersdelegated[0];
        } else {
            $isDelegatedToMe = false;
            $uservalidant = "";
        }

//        $isSignable = $entity->isSignable($em);
        $isSignable = $entity->isSignable();


        $d = $em->getRepository('SesileUserBundle:User')->find($entity->getUser());
        $deposant = array("id" => $d->getId(), "nom" => $d->getPrenom() . " " . $d->getNom(), "path" => $d->getPath());
        $validant = $entity->getvalidant();





        return array(
            'deposant'      => $deposant,
            'validant'      => $validant,
            'classeur'      => $entity,
            'retractable'   => $isRetractableByDelegates,
            'signable'      => $isSignable,
            'usersdelegated'=> $usersdelegated,
            'isDelegatedToMe' => $isDelegatedToMe,
            'uservalidant'  => $uservalidant,
            "menu_color"    => "bleu",
            'users'         => $users
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

        list($d, $m, $a) = explode("/",

            $request->request->get('validation'));

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
     * Edits an existing Classeur entity.
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

        $isvalidator = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->isDelegatedToUser($classeur->getId(), $this->getUser());

        if(!$isvalidator && $this->getUser()->getId() == $classeur->getValidant()) {
            $classeur->setVisibilite($request->get("visibilite"));
            $classeur->setNom($request->get("name"));
            $classeur->setDescription($request->get("desc"));
            list($d, $m, $a) = explode("/", $request->request->get('validation'));
            $valid = new \DateTime($m . "/" . $d . "/" . $a);
            $classeur->setValidation($valid);
            $circuit = $request->request->get('circuit');
            $classeur->setCircuit($circuit);

        }

        $currentvalidant = $classeur->getValidant();
        $repositoryusers = $this->getDoctrine()->getRepository('SesileUserBundle:user');
        $delegator = $repositoryusers->find($currentvalidant);

        // Remet le circuit a zero
        $classeur->setValidant($classeur->soumettre());
        // Et on met le bon status
        $classeur->setStatus(1);

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
        $action_libelle = ($classeur->getValidant() == 0) ? "Classeur finalisé" : "Validation";

        if ($isvalidator) $action_libelle .= " (Délégation recue de " . $delegator->getPrenom() . " " . $delegator->getNom() . ")";
        $action->setAction($action_libelle);
        $em->persist($action);
        $em->flush();

        $this->sendValidationMail($classeur);

        //$this->updateAction($request);

        $request->getSession()->getFlashBag()->add(
            'success',
            "Le classeur a été validé"
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

        /* ->getRepository('SesileClasseurBundle:Classeur')->find($request->get("commentaire")*/
        $isvalidator = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->isDelegatedToUser($classeur, $this->getUser());

        // mise a jour des données soumises
//        if(!$isvalidator && $this->getUser()->getId() == $classeur->getValidant()) {

        if($request->request->get('circuit')) {
            $circuit = $request->request->get('circuit');
            $classeur->setCircuit($circuit);
            $em->flush();
        }

        if(!$classeur->isSignable()) {

            $visibilite = $request->get("visibilite");
            $classeur->setVisibilite($visibilite);
            $classeur->setNom($request->get("name"));
            $classeur->setDescription($request->get("desc"));
            list($d, $m, $a) = explode("/", $request->request->get('validation'));
            $valid = new \DateTime($m . "/" . $d . "/" . $a);
            $classeur->setValidation($valid);
//            $circuit = $request->request->get('circuit');
//            $classeur->setCircuit($circuit);
        } else {
            $circuit = $classeur->getCircuit();
            $visibilite = $classeur->getVisibilite();

            // On renomme le document avec -sign
            $doc = $classeur->getDocuments()[0];
            $path_parts = pathinfo($doc->getName());
            $nouveauNom = $path_parts['filename'] . '-sign.' . $path_parts['extension'];
            $doc->setName($nouveauNom);
        }

        $currentvalidant = $classeur->getValidant();
        $repositoryusers = $this->getDoctrine()->getRepository('SesileUserBundle:user');
        $delegator=$repositoryusers->find($currentvalidant);

        // Pour la visibilite
        // recuperation des users du circuit
        $users = explode(',', $circuit);

        if ($visibilite != 2 && $visibilite != 3) {
            $usersCV = $this->classeur_visible($visibilite, $users);
            // On vide la table many to many
            $classeur->getVisible()->clear();
            foreach ($usersCV as $userCV) {
                $userVisible = $em->getRepository('SesileUserBundle:User')->findOneById($userCV->getId());
                $classeur->addVisible($userVisible);
            }

        } elseif ($visibilite == 2) {
            $usersCV = $classeur->getPrivateAfterMeVisible();
            $usersCV = array_unique($usersCV);
            // On vide la table many to many
            $classeur->getVisible()->clear();
            foreach ($usersCV as $userCV) {
//                echo $userCV . '<br>';
                $userVisible = $em->getRepository('SesileUserBundle:User')->findOneById($userCV);
//                var_dump($userVisible->getId());
                $classeur->addVisible($userVisible);
            }
        }

        // FIN Pour la visibilite
        $classeur->valider($em);


        $em->flush();


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
        $action_libelle = ($classeur->getValidant() == 0) ? "Classeur finalisé" : "Validation";

        if($isvalidator) $action_libelle.=" (Délégation recue de ".$delegator->getPrenom()." ".$delegator->getNom().")";
        $action->setAction($action_libelle);
        $em->persist($action);
        $em->flush();

        $this->sendValidationMail($classeur);

        //$this->updateAction($request);

        $session = $request->getSession();
        $session->getFlashBag()->add(
            'success',
            "Le classeur a été validé"
        );
        return $this->redirect($this->generateUrl('index_valider'));
        //return $this->redirect($this->generateUrl('classeur_edit', array('id' => $classeur->getId())));
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


        $isvalidator = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->isDelegatedToUser($classeur, $this->getUser());
        $currentvalidant = $classeur->getValidant();
        $repositoryusers = $this->getDoctrine()->getRepository('SesileUserBundle:user');
        $delegator=$repositoryusers->find($currentvalidant);

        // envoi d'un mail validant suivant
        $this->sendRefusMail($classeur);

        $classeur->refuser();

        $em->flush();


        $action = new Action();

        $action->setCommentaire($request->request->get('comment'));

        $message = "motif du refus : " . $request->request->get('text-message');
        $action->setObservation($message);
        $action->setClasseur($classeur);
        $action->setUser($this->getUser());
        $action_libelle = "Refus";

        if($isvalidator) $action_libelle.=" (Délégation recue de ".$delegator->getPrenom()." ".$delegator->getNom().")";
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
        var_dump($doc->getName());
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

        $isvalidator = $isvalidator = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->isDelegatedToUser($classeur, $this->getUser());
        $currentvalidant = $classeur->getValidant();
        $repositoryusers = $this->getDoctrine()->getRepository('SesileUserBundle:user');
        $delegator=$repositoryusers->find($currentvalidant);

        $classeur->valider($em);

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
        if($isvalidator) $action_libelle.=" (Délégation recue de ".$delegator->getPrenom()." ".$delegator->getNom().")";
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
        $url_applet = $this->container->getParameter('url_applet');

        return array('user' => $user, 'classeur' => $classeur, 'session_id' => $session->getId(), 'docstosign' => $docstosign, 'servername' => $servername, "url_applet" => $url_applet);

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
//        $classeur->setOrdreCircuit($classeur->setOrdreMoins());

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


        $isvalidator = $isvalidator = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->isDelegatedToUser($classeur, $this->getUser());
        $currentvalidant = $classeur->getValidant();
        $repositoryusers = $this->getDoctrine()->getRepository('SesileUserBundle:user');
        $delegator=$repositoryusers->find($currentvalidant);

        $classeur->supprimer();
        $em->flush();

        $action = new Action();
        $action->setClasseur($classeur);
        $action->setUser($this->getUser());
        $action_libelle = "Classeur retiré";
        if($isvalidator) $action_libelle.=" (Délégation recue de ".$delegator->getPrenom()." ".$delegator->getNom().")";
        $action->setAction($action_libelle);
        $em->persist($action);
        $em->flush();

        $session = $request->getSession();
        $session->getFlashBag()->add(
            'success',
            "Le classeur a été retiré"
        );
        return $this->redirect($this->generateUrl('index_valider'));
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
            //"type" => 0,
            "collectivite" => $this->get("session")->get("collectivite")
        ));

        $type = $request->request->get('type', 'elclassico');

        switch ($type) {
//            case "Classique":
//                return $this->render(
//                    'SesileClasseurBundle:Formulaires:elclassico.html.twig', array("groupes" => $entities)
//                );
//                break;
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
        if (empty($reqid)) {
            return new JsonResponse(array('error' => 'Parameters missing'));
        }


        //Récupérer le classeur

        $em = $this->getDoctrine()->getManager();
        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->find($request->get("id"));

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
        $message = \Swift_Message::newInstance()
            ->setSubject($sujet)
            ->setFrom('sesile@sictiam.fr')
            ->setTo($to)
            ->setBody($body)
            ->setContentType('text/html');
        $this->get('mailer')->send($message);
    }

    private function sendValidationMail($classeur)
    {
        $em = $this->getDoctrine()->getManager();
        $coll = $em->getRepository("SesileMainBundle:Collectivite")->find($this->get("session")->get("collectivite"));
        $c_user = $em->getRepository("SesileUserBundle:User")->find($classeur->getUser());

        $env = new \Twig_Environment(new \Twig_Loader_String());
        $body = $env->render($coll->getTextMailwalid(),
            array(
                'validant' => $c_user->getPrenom()." ".$c_user->getNom(),
                'titre_classeur' => $classeur->getNom(),
                'date_limite' => $classeur->getValidation(),
                "lien" => '<a href="http://' . $this->container->get('router')->getContext()->getHost().$this->generateUrl('classeur_edit', array('id' => $classeur->getId())) . '">Valider le classeur</a>'
            )
        );

        $validant_obj = ($classeur->getValidant() == 0)?$em->getRepository('SesileUserBundle:User')->find($classeur->getUser()):$em->getRepository('SesileUserBundle:User')->find($classeur->getValidant());

        if ($validant_obj != null) {
            $this->sendMail("SESILE - Nouveau classeur à valider", $validant_obj->getEmail(), $body);
        }
    }

    private function sendCreationMail($classeur) {
        $em = $this->getDoctrine()->getManager();
        $coll = $em->getRepository("SesileMainBundle:Collectivite")->find($this->get("session")->get("collectivite"));
        $c_user = $em->getRepository("SesileUserBundle:User")->find($classeur->getUser());

        $env = new \Twig_Environment(new \Twig_Loader_String());
        $body = $env->render($coll->getTextmailnew(),
            array(
                'deposant' => $c_user->getPrenom()." ".$c_user->getNom(),
                'titre_classeur' => $classeur->getNom(),
                'date_limite' => $classeur->getValidation(),
                "lien" => '<a href="http://'.$this->container->get('router')->getContext()->getHost().$this->generateUrl('classeur_edit', array('id' => $classeur->getId())) . '">Voir le classeur</a>'
            )
        );

        $validant_obj = $em->getRepository('SesileUserBundle:User')->find($classeur->getValidant());

        if ($validant_obj != null) {
            $this->sendMail("SESILE - Nouveau classeur à valider", $validant_obj->getEmail(), $body);
        }
    }

    private function sendRefusMail($classeur) {
        $em = $this->getDoctrine()->getManager();
        $coll = $em->getRepository("SesileMainBundle:Collectivite")->find($this->get("session")->get("collectivite"));
        $c_user = $em->getRepository("SesileUserBundle:User")->find($classeur->getValidant());

//        var_dump($classeur->getId(), $classeur->getValidant()); die();

        $env = new \Twig_Environment(new \Twig_Loader_String());
        $body = $env->render($coll->getTextmailrefuse(),
            array(
                'validant' => $c_user->getPrenom()." ".$c_user->getNom(),
                'titre_classeur' => $classeur->getNom(),
                'date_limite' => $classeur->getValidation(),
                "lien" => '<a href="http://'.$this->container->get('router')->getContext()->getHost().$this->generateUrl('classeur_edit', array('id' => $classeur->getId())) . '">Voir le classeur</a>'
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

    // Fonction pour determiner enregister dans Classeur_visible
    private function classeur_visible($visibilite, $users, $requestUserGroupe = null) {
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
                    $usersGF = $em->getRepository('SesileUserBundle:UserGroupe')->findByGroupe($requestUserGroupe);
                    foreach ($usersGF as $k => $userGF) {
                        $userGroupe[] = $usersGF[$k]->getUser()->getId();
                    }
                    $usersAll = array_unique(array_merge($userGroupe, $users));
                    return $em->getRepository('SesileUserBundle:User')->findById($usersAll);
                } else {
                    return $this->redirect($this->generateUrl('classeur_create'));
                }
                break;
            // Par défaut on fait quoi ?
            default:
                $request->getSession()->getFlashBag()->add('notice', 'Merci de choisir une visibilité.');
                return $this->redirect($this->generateUrl('classeur_create'));
                break;
        }
    }
}