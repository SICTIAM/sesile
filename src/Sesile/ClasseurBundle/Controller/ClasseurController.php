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
//        return $this->listeAction();
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
//        $entities = $em->getRepository('SesileUserBundle:User')->findOneById($this->getUser()->getId())->getClasseurs();
        $entities = $em->getRepository('SesileClasseurBundle:Classeur')->getClasseursVisiblesForDTablesV3($this->getUser()->getId(), $get);

        $output = array(
            "draw" => $get->get("draw"),
            "recordsTotal" => count($entities),
            "recordsFiltered" => count($em->getRepository('SesileClasseurBundle:Classeur')->countClasseursVisiblesForDTablesV3($this->getUser()->getId())),
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

                $classeur->getNom(),
                $classeur->getCreation()->format('d/m/Y H:i'),
                $classeur->getValidation()->format('d/m/Y'),
                implode($val),
                $classeur->getType()->getNom(),
                $classeur->getStatus(),
                $classeur->getId(),
                implode($doc),
                $classeur->getType()->getId(),

            );
            /*$tabClasseurs[] = array(
                'id'        => $classeur->getId(),
                'nom'       => $classeur->getNom(),
                'creation'  => $classeur->getCreation(),
                'validation'=> $classeur->getValidation(),
                'type'      => $classeur->getType(),
                'status'    => $classeur->getStatus(),
                'document'  => $classeur->getDocuments(),
                'validants' => $validants
//                'deposant'  => $deposant->getPrenom() . " " . $deposant->getNom()
            );*/
            $output['data'][] = $tabClasseurs;
        }

        /*return array(
            'classeurs'  => $tabClasseurs,
            "menu_color" => "bleu"
        );*/
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
                'nom'=>$classeur->getNom(),
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

    /**
     * @Route("/ajax/listRetired", name="ajax_classeurs_list_retired")
     * @Template()
     */
    /*public function listAjaxRetiredAction(Request $request)
    {

        $get = $request->query->all();
//        $columns = array('Nom', 'Creation', 'Validation', 'Validant', 'Type', 'Status', 'Id');
        $columns = array('Nom', 'Creation', 'Validation', 'Visibilite', 'Type', 'Status', 'Id');
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
                } elseif ($columns[$i] == "Visibilite") {
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
    }*/

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
//        return $this->redirect($this->generateUrl('index'));
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
     * @Route("/ajax/list", name="ajax_classeurs_list")
     * @Template()
     */
    /*
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
//                } elseif ($columns[$i] == "Validant") {
                } elseif ($columns[$i] == "etapeDeposante") {
//                    $intervenants = $aRow->{"get".$columns[$i]."->getId"}();
//                    $intervenant = $aRow->getEtapeValidante()->getId();
                    $validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($aRow);
                    $userValidant = '';
                    foreach ($validants as $k => $validant) {
                        $userValidant .= $validant->getNom() . ' ' . $validant->getPrenom();
                        if (count($validants) != $k) {
                            $userValidant .= ' / ';
                        }
                    }

                    $row[] = $userValidant;
//                    $row[] = ($intervenant == 0)?"":$em->getRepository('SesileUserBundle:User')->find($intervenant)->getNom();
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
    */



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
//        $entities = $em->getRepository('SesileClasseurBundle:Classeur')->gatClasseurToValidate();

        $tabClasseurs = array();
        foreach($entities as $classeur)
        {
            $validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($classeur);
          /*  if($classeur->getId() == 106)
            {
                error_log(print_r($validants,true));
            }*/
            if(array_intersect($usersdelegated, $validants))
            {
                $tabClasseurs[] = array(
                    'id'        => $classeur->getId(),
                    'nom'       => $classeur->getNom(),
                    'creation'  => $classeur->getCreation(),
                    'validation'=> $classeur->getValidation(),
                    'type'      => $classeur->getType(),
                    'status'    => $classeur->getStatus(),
                    'document'  => $classeur->getDocuments(),
                    'validants' => $validants
                );
            }

        }

        return array(
            'classeurs' => $tabClasseurs,
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
/*
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
        );*/

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
        /*
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
        $sql = "SELECT $aColumnStr FROM SesileClasseurBundle:Classeur c WHERE c.validant IN ($validant) AND c.status IN (0,1,4)";

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

        $sql = "SELECT $aColumnStr FROM SesileClasseurBundle:Classeur c WHERE c.validant IN ($validant) AND c.status IN (0,1,4) $where $order";



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

        $sql = "SELECT $aColumnStr FROM SesileClasseurBundle:Classeur c WHERE c.validant IN ($validant) AND c.status IN (0,1,4) $order";
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
        */
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
            $validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($entity);
            $validantId = array();
            foreach ($validants as $validant) {
                $validantId[] = $validant->getId();
            }

            $prevValidant = $em->getRepository('SesileClasseurBundle:Classeur')->getPrevValidantForRetract($entity);
//            var_dump($prevValidant);
            if ($entity->isRetractableByDelegates($users, $validantId, $prevValidant)) {

                $entity->validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($entity);

//                $user = $em->getRepository('SesileUserBundle:User')->findOneById($entity->getValidant());
//                $user = $em->getRepository('SesileUserBundle:User')->findOneById($this->getUser());
//                $entity->validantName = $user ? $user->getPrenom() . " " . $user->getNom() : " ";
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


//        $circuit = $request->request->get('circuit');
//        $classeur->setCircuit($circuit);

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
        $types = $em->getRepository('SesileClasseurBundle:TypeClasseur')->findBy(array(), array('nom' => 'ASC'));

        // Nouveau code pour afficher l ordre des groupes
//        $id_user = $this->get('security.context')->getToken()->getUser()->getId();
//        $groupes_du_user = $em->getRepository('SesileUserBundle:UserGroupe')->findByUser($this->getUser());
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

            /*$hierarchie = $em->getRepository('SesileUserBundle:UserGroupe')->findBy(array("groupe" => $group->getGroupe()), array("parent" => "DESC"));
            $this->ordre = "";
            $circuits[] = array(
                "id" => $group->getGroupe()->getId(),
                "name" => $group->getGroupe()->getNom(),
                "ordre" => $this->recursivesortHierarchie($hierarchie, $id_user),
                "groupe" => true
            );*/
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
//        $entity = $em->getRepository('SesileClasseurBundle:Classeur')->find($id);

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

        if (!in_array($entity, $user->getClasseurs()->toArray()) and !$editDelegants) {
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

//        $repositorydelegates = $this->getDoctrine()->getRepository('SesileDelegationsBundle:delegations');
//        $repositorydelegates = $em->getRepository('SesileDelegationsBundle:delegations');
//        $repositoryusers = $this->getDoctrine()->getRepository('SesileUserBundle:user');



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


//        if (($entity->getValidant() == $delegants[0] || ($isRetractableByDelegates && $entity->getPrevValidant() == $delegants[0])) && $this->getUser()->getId() != $delegants[0]) {
//        if ((in_array($validantsId, $delegants) || ($isRetractableByDelegates && in_array($prevValidantsId, $delegants))) && $this->getUser()->getId() != $delegants  && !$entity->isAtLastValidant()) {
//        if ($isRetractableByDelegates && $this->getUser()->getId() != $delegants  && !$entity->isAtLastValidant()) {
        /*if ($isRetractableByDelegates && !$entity->isAtLastValidant()) {
            $isDelegatedToMe = true;
            $uservalidant = $usersdelegated[0];
            $currentValidant = array("id" => $usersdelegated[0]->getId(), "nom" => $usersdelegated[0]->getPrenom() . " " . $usersdelegated[0]->getNom(), "path" => $usersdelegated[0]->getPath());
        } else {
            $isDelegatedToMe = false;
            $uservalidant = "";
            if (in_array($this->getUser(), $validants) && !$entity->isAtLastValidant()) {
                $currentValidant = array("id" => $this->getUser()->getId(), "nom" => $this->getUser()->getPrenom() . " " . $this->getUser()->getNom(), "path" => $this->getUser()->getPath());
            }
            else {
                $currentValidant = array();
            }
        }*/

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



        $isSignable = $entity->isSignable();


//        $d = $em->getRepository('SesileUserBundle:User')->find($entity->getUser());
//        $deposant = array("id" => $d->getId(), "nom" => $d->getPrenom() . " " . $d->getNom(), "path" => $d->getPath());

//        $validant = $entity->getvalidant();
//var_dump($isSignable);exit;
        return array(
//            'deposant'      => $deposant,
            'validant'      => $validants,
            'currentValidant' => $currentValidant,
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

//        $isvalidator = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->isDelegatedToUser($classeur->getId(), $this->getUser());
        $isvalidator = $em->getRepository('SesileClasseurBundle:Classeur')->isDelegatedToUser($classeur, $this->getUser());
        $validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($classeur);

//        if(!$isvalidator && $this->getUser()->getId() == $classeur->getValidant()) {
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
       //     var_dump($circuit);exit;
        }

//        $currentvalidant = $classeur->getValidant();
//        $repositoryusers = $em->getRepository('SesileUserBundle:user');
//        $delegator = $em->getRepository('SesileUserBundle:user')->find($currentvalidant);

        // Remet le circuit a zero
//        $classeur->setValidant($classeur->soumettre());
//        $classeur->setOrdreValidant($classeur->soumettre());

        $classeur = $em->getRepository('SesileUserBundle:EtapeClasseur')->setEtapesForClasseur($classeur, $request->request->get('valeurs'));
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


//        $isvalidator = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->isDelegatedToUser($classeur, $this->getUser());
//        $isvalidator = $em->getRepository('SesileClasseurBundle:Classeur')->isDelegatedToUser($classeur, $this->getUser());
//        $currentvalidant = $classeur->getValidant();


        // mise a jour des données soumises

//        if(!$classeur->isSignable()) {

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

//        $repositoryusers = $this->getDoctrine()->getRepository('SesileUserBundle:user');
//        $delegator = $repositoryusers->find($currentvalidant);
        $isvalidator = $em->getRepository('SesileClasseurBundle:Classeur')->isDelegatedToUser($classeur, $this->getUser());

        // Pour la visibilite
        // recuperation des users du circuit
//        $users = explode(',', $circuit);
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

        } elseif ($visibilite == 2) {
//            $usersCV = $classeur->getPrivateAfterMeVisible();
            $usersCV = $em->getRepository('SesileUserBundle:EtapeClasseur')->findAllUsersAfterMe($classeur);
            $usersCV = array_unique($usersCV);
            // On vide la table many to many
            $classeur->getVisible()->clear();
            foreach ($usersCV as $userCV) {
                $userVisible = $em->getRepository('SesileUserBundle:User')->findOneById($userCV);
                $classeur->addVisible($userVisible);
            }
        } elseif ($visibilite == 3) {
//            $usersCV = $classeur->getPrivateAfterMeVisible();

            $usersVisible = $classeur->getVisible();
            $usersAlreadyVisible = array();
            foreach ($usersVisible as $userV) {
                $usersAlreadyVisible[] = $userV->getId();
            }
            $usersCV = $em->getRepository('SesileUserBundle:EtapeClasseur')->findAllUsersAfterMe($classeur);
            $usersCV = array_unique($usersCV);

            $usersCV = array_diff($usersCV, $usersAlreadyVisible);
            // On vide la table many to many
//            $classeur->getVisible()->clear();
            foreach ($usersCV as $userCV) {
                $userVisible = $em->getRepository('SesileUserBundle:User')->findOneById($userCV);
                $classeur->addVisible($userVisible);
            }
        }

        // FIN Pour la visibilite
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
//        $action_libelle = ($classeur->getValidant() == 0) ? "Classeur finalisé" : "Validation";
        $action_libelle = ($classeur->getStatus() == 2) ? "Classeur finalisé" : "Validation";

//        if($isvalidator) $action_libelle .= " (Délégation recue de " . $delegator->getPrenom() . " " . $delegator->getNom() . ")";
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
//        $this->sendValidationMail($classeur, $currentvalidant);

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


//        $isvalidator = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->isDelegatedToUser($classeur, $this->getUser());
        $isvalidator = $em->getRepository('SesileClasseurBundle:Classeur')->isDelegatedToUser($classeur, $this->getUser());
//        $currentvalidant = $classeur->getValidant();
//        $repositoryusers = $this->getDoctrine()->getRepository('SesileUserBundle:user');
//        $delegator=$repositoryusers->find($currentvalidant);

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

//        $isvalidator = $isvalidator = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->isDelegatedToUser($classeur, $this->getUser());
//        $currentvalidant = $classeur->getValidant();
//        $repositoryusers = $this->getDoctrine()->getRepository('SesileUserBundle:user');
//        $delegator=$repositoryusers->find($currentvalidant);
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

//        $classeur->valider($em);
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
     * Valider_et_signer an existing Classeur entity.
     *
     * @Route("/signform/{id}", name="signform")
     * @Template()
     *
     */
    public function signAction(Request $request, $id)
    {
        //var_dump($request->get("moncul"));exit;
        $user = $this->get('security.context')->getToken()->getUser();

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


//        $isvalidator = $isvalidator = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->isDelegatedToUser($classeur, $this->getUser());
//        $currentvalidant = $classeur->getValidant();
//        $currentvalidant = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($classeur);
//        $delegator = $em->getRepository('SesileUserBundle:user')->find($currentvalidant);

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
        $message = \Swift_Message::newInstance();
        // Pour l integration de l image du logo dans le mail
        $html = explode("**logo_coll**", $body);
        $htmlBody = $html[0] . '<img src="' . $message->embed(\Swift_Image::fromPath($this->container->getParameter('upload')['logo_coll'] . $this->get('session')->get('logo'))) . '" width="75">' . $html[1];

        // Constitution du mail
        $message->setSubject($sujet)
            ->setFrom($this->container->getParameter('email_sender_address'))
            ->setTo($to)
            ->setBody($htmlBody)
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
        $currentvalidant = $this->getUser();
        $c_user = $em->getRepository("SesileUserBundle:User")->findOneById($currentvalidant);
        $env = new \Twig_Environment(new \Twig_Loader_String());
        $body = $env->render($coll->getTextmailnew(),
            array(
                'deposant' => $d_user->getPrenom() . " " . $d_user->getNom(),
                'validant' => $c_user->getPrenom() . " " . $c_user->getNom(),
                'role' => $c_user->getRole(),
                'titre_classeur' => $classeur->getNom(),
                'date_limite' => $classeur->getValidation(),
                'type' => strtolower($classeur->getType()->getNom()),
                "lien" => '<a href="http://'.$this->container->get('router')->getContext()->getHost().$this->generateUrl('classeur_edit', array('id' => $classeur->getId())) . '">valider le classeur</a>'
            )
        );
        $validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($classeur);
//        foreach($classeur->getValidant() as $validant) {
        foreach($validants as $validant) {

            if ($validant != null) {
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
                $request->getSession()->getFlashBag()->add('notice', 'Merci de choisir une visibilité.');
                return $this->redirect($this->generateUrl('classeur_create'));
                break;
        }
    }
}