<?php

namespace Sesile\DocumentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sesile\ClasseurBundle\Entity\Action;
use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\DocumentBundle\Classe\PES;
use Sesile\DocumentBundle\Classe\Piece;
use Sesile\DocumentBundle\Classe\Bordereau;
use Sesile\DocumentBundle\Classe\PJ;
use Sesile\DocumentBundle\Entity\Document;

class DocumentController extends Controller
{
    /**
     * @Route("/new", name="new_document",  options={"expose"=true})
     * @Template()
     */
    public function newAction() {
        return array();
    }


    /**
     * @Route("/editforclasseur/{id}", name="edit_document_for_classeur",  options={"expose"=true})
     * @Template()
     */
    public function editForClasseurAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();
        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->findOneById($id);
        $validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($classeur);
        $validantsId = array();
        foreach ($validants as $validant) {
            $validantsId[] =$validant->getId();
        }

        $isvalidable = $classeur->isValidable($this->getUser()->getId(), $validantsId);

        $docs = $classeur->getDocuments();
        $tailles = array();
        $types = array();
        $ids = array();
        $names = array();
        foreach ($docs as $doc) {
            $names[$doc->getId()] = addslashes($doc->getName());
            $tailles[$doc->getId()] = filesize('uploads/docs/' . $doc->getRepoUrl());
            $types[$doc->getName()] = $doc->getType();
            $ids[$doc->getId()] = $doc->getName();
        }


        return array('names'=>$names,'docs' => $docs, 'classeur' => $classeur, 'tailles' => $tailles, 'types' => $types, 'ids' => $ids, 'isvalidable' => $isvalidable);

    }

    /**
     * @Route("/uploadfile", name="upload_doc",  options={"expose"=true})
     *
     */
    public function uploadAction(Request $request) {


        $em = $this->getDoctrine()->getManager();
        $uploadedfile = $request->files->get('signedFile');
        $id = $request->request->get('id');
        if (empty($uploadedfile)) {
            return new JsonResponse(array("error" => "nothinguploaded"));
        }

        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);
        if (empty($doc)) {
            return new JsonResponse(array("error" => "nodocumentwiththisname", "name" => $uploadedfile->getClientOriginalName()));
        }

        if (file_exists('uploads/docs/' . $doc->getRepourl())) {
            unlink('uploads/docs/' . $doc->getRepourl());
            $uploadedfile->move('uploads/docs/', $doc->getRepourl());
            $doc->setSigned(true);
            $em->flush();
            return new JsonResponse(array("error" => "ok", "url" => 'uploads/docs/' . $doc->getRepourl()));

        } else {
            unlink($uploadedfile->getRealPath());

            return new JsonResponse(array("error" => "nodocumentwiththisname"));

        }


    }


    /**
     * @Route("/{id}", name="show_document",  options={"expose"=true})
     * @Template()
     */
    public function showAction(Request $request, $id)
    {
//var_dump($id);exit;

        $servername = $this->getRequest()->getHost();

        if (ctype_digit($id)) {


            $em = $this->getDoctrine()->getManager();
            $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);
            $validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($doc->getClasseur());
            $validantsId = array();
            foreach ($validants as $validant) {
                $validantsId[] =$validant->getId();
            }

            $name = $doc->getName();
            $historyinverse = $em->getRepository('SesileDocumentBundle:DocumentHistory')->getHistory($doc);

            // Test pour les droits de modifications des documents
            $id_classeur = $doc->getClasseur()->getId();
            $isValidant = $em->getRepository('SesileClasseurBundle:Classeur')->findOneById($id_classeur)->isValidable($this->getUser()->getId(), $validantsId);

        } else {
            $doc = null;
            $historyinverse = null;
            $name = $id;
            $isValidant = null;

        }


        return array('doc' => $doc, 'name' => $name, 'servername' => $servername, 'historyinverse' => $historyinverse, 'isvalidant' => $isValidant);

    }

    /**
     * @Route("/notifymodif/{name}", name="notify_modif_doc",  options={"expose"=true})
     * @Template()
     */
    public function notifyModificationAction(Request $request, $name)
    {


        $em = $this->getDoctrine()->getManager();
        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneBy(array('repourl' => $name));
        $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($doc, "Modification du document", null);
        $classeur = $doc->getClasseur();

        $action = new Action();
        $action->setClasseur($classeur);
        $action->setUser($this->getUser());
        $action->setAction("Modification du document " . $doc->getName());
        $em->persist($action);

        $em->flush();


        return array('name' => $name);
    }

    /**
     * @Route("/download/{id}", name="download_doc",  options={"expose"=true})
     *
     */
    public function downloadAction(Request $request, $id)
    {


        $em = $this->getDoctrine()->getManager();
        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);
        // Ecriture de l'hitorique du document
        $id_user = $this->get('security.context')->getToken()->getUser()->getId();
        $user = $em->getRepository('SesileUserBundle:User')->findOneByid($id_user);
        $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($doc, "Téléchargement du document par " . $user->getPrenom() . " " . $user->getNom(), null);

        $response = new Response();


        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type('uploads/docs/' . $doc->getRepourl()));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $doc->getName() . '"');
        $response->headers->set('Content-Length', filesize('uploads/docs/' . $doc->getRepourl()));

        // $response->sendHeaders();

        $response->setContent(file_get_contents('uploads/docs/' . $doc->getRepourl()));

        //  var_dump($response);
        return $response;
    }


    /**
     * @Route("/{id}/delete", name="delete_document",  options={"expose"=true})
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id)
    {


        $em = $this->getDoctrine()->getManager();
        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);

        $classeur = $doc->getClasseur();

        $action = new Action();
        $action->setClasseur($classeur);
        $action->setUser($this->getUser());
        $action->setAction("Suppression du document " . $doc->getName());
        $em->persist($action);

        $em->flush();


        $em->remove($doc);
        $em->flush();

        return new JsonResponse(array("error" => "ok"));

    }

    /**
     * @Route("/edit-history/{id}/document", name="edit_history_document",  options={"expose"=true})
     * @Method("POST")
     */
    public function editHistoryFactory($id) {
        $em = $this->getDoctrine()->getManager();
        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);
        // Ecriture de l'hitorique du document
        $id_user = $this->get('security.context')->getToken()->getUser()->getId();
        $user = $em->getRepository('SesileUserBundle:User')->findOneByid($id_user);
        $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($doc, "Edition du document par " . $user->getPrenom() . " " . $user->getNom(), null);

        return new Response();
    }


    /**
     * @Route("/visu/{id}", name="visu",  options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function visuAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);
        $user = $this->getUser();


        $entity = $doc->getClasseur();

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



        // Ecriture de l'hitorique du document
        $id_user = $this->get('security.context')->getToken()->getUser()->getId();
        $user = $em->getRepository('SesileUserBundle:User')->findOneByid($id_user);
        $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($doc, "Visualisation du document par " . $user->getPrenom() . " " . $user->getNom(), null);


        $param = $this->container->getParameter('upload');
        $dir = $param['fics'];
        $path = $dir . $doc->getRepourl();

        // on enleve tout les putains de préfixes de mes 2

        $str = str_ireplace('ns3:', '', str_ireplace('xad:', '', str_ireplace('ds:', '', file_get_contents($path))));
        $xml = simplexml_load_string($str);

        $arrayPJ = array();
        if (isset($xml->PES_PJ)) {
            foreach ($xml->PES_PJ->PJ as $pj) {
                $arrayPJ[] = $pj;
            }
        }


        $arrayBord = array();


        if (isset($xml->PES_DepenseAller)) {

            foreach ($xml->PES_DepenseAller->Bordereau as $Bord) {
                $Bord->type = 'Depense';
                $arrayBord[] = $Bord;
            }
        }
        if (isset($xml->PES_RecetteAller)) {

            foreach ($xml->PES_RecetteAller->Bordereau as $Bord) {
                $Bord->type = 'Recette';
                $arrayBord[] = $Bord;
            }
        }
        if(!isset($xml->PES_RecetteAller) && !isset($xml->PES_DepenseAller))
        {
            return array('isPJ' => true);
        }


        if (isset($xml->PES_DepenseAller) && count($xml->PES_DepenseAller->Bordereau->Signature)) {
            //si on a une signature  on récupère le certificat
            $sign = $xml->PES_DepenseAller->Bordereau->Signature->KeyInfo->X509Data->X509Certificate;
            $x509 = '-----BEGIN CERTIFICATE-----' . chr(10) . $sign . chr(10) . '-----END CERTIFICATE-----';
            //on récupère un tableau contenant les infos du certificat
            $tab = openssl_x509_parse($x509);
            $subject = $tab['subject'];
            $Signataire = $subject['CN'];

            //on récupère la date de signature (il y a surement plus simple)

            $dateMoche = $xml->PES_DepenseAller->Bordereau->Signature->Object->QualifyingProperties->SignedProperties->SignedSignatureProperties->SigningTime;
            list($jourMoche, $heureMoche) = explode('T', $dateMoche);
            list($annee, $jour, $mois) = explode('-', $jourMoche);
            list($heure, $minute, $reste) = explode(':', $heureMoche);
            $date = $jour . '/' . $mois . '/' . $annee . ' ' . intval($heure + 2) . ':' . $minute;

        } elseif(isset($xml->PES_RecetteAller) && count($xml->PES_RecetteAller->Bordereau->Signature)) {
            //si on a une signature  on récupère le certificat
            $sign = $xml->PES_RecetteAller->Bordereau->Signature->KeyInfo->X509Data->X509Certificate;
            $x509 = '-----BEGIN CERTIFICATE-----' . chr(10) . $sign . chr(10) . '-----END CERTIFICATE-----';
            //on récupère un tableau contenant les infos du certificat
            $tab = openssl_x509_parse($x509);
            $subject = $tab['subject'];
            $Signataire = $subject['CN'];

            //on récupère la date de signature (il y a surement plus simple)

            $dateMoche = $xml->PES_RecetteAller->Bordereau->Signature->Object->QualifyingProperties->SignedProperties->SignedSignatureProperties->SigningTime;
            list($jourMoche, $heureMoche) = explode('T', $dateMoche);
            list($annee, $jour, $mois) = explode('-', $jourMoche);
            list($heure, $minute, $reste) = explode(':', $heureMoche);
            $date = $jour . '/' . $mois . '/' . $annee . ' ' . intval($heure + 2) . ':' . $minute;
        }
        else{
            $Signataire = '';
            $date = '';
        }

        $PES = new PES($xml->EnTetePES->LibelleColBud->attributes()[0], $Signataire, $date, $arrayBord, $arrayPJ);
        $tabIdBord = array();
        foreach ($PES->listBord as $bordereau) {
            $tabIdBord[] = $bordereau->id;
        }

        return array('budget' => $PES->budget, 'signataire' => utf8_decode($PES->signataire), 'dateSign' => $PES->dateSign, 'bords' => $tabIdBord, 'idDoc' => $doc->getId());
    }

    /**
     * @Route("/visubord/{id}/{bord}", name="visubord",  options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function visubordAction($id, $bord)
    {

        $em = $this->getDoctrine()->getManager();
        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);


        $param = $this->container->getParameter('upload');
        $dir = $param['fics'];
        $path = $dir . $doc->getRepourl();

        $str = str_ireplace('ns3:', '', str_ireplace('xad:', '', str_ireplace('ds:', '', file_get_contents($path))));
        $xml = simplexml_load_string($str);

        $arrayPJ = array();
        if (isset($xml->PES_PJ)) {
            foreach ($xml->PES_PJ->PJ as $pj) {
                $arrayPJ[] = $pj;
            }
        }

        $arrayBord = array();


        if (isset($xml->PES_DepenseAller)) {

            foreach ($xml->PES_DepenseAller->Bordereau as $Bord) {
                $Bord->type = 'Depense';
                $arrayBord[] = $Bord;
            }
        }
        if (isset($xml->PES_RecetteAller)) {

            foreach ($xml->PES_RecetteAller->Bordereau as $Bord) {
                $Bord->type = 'Recette';
                $arrayBord[] = $Bord;
            }
        }
        if(!isset($xml->PES_RecetteAller) && !isset($xml->PES_DepenseAller))
        {
            return array('isPJ' => true);
        }


        if (isset($xml->PES_DepenseAller) && count($xml->PES_DepenseAller->Bordereau->Signature)) {
            //si on a une signature  on récupère le certificat
            $sign = $xml->PES_DepenseAller->Bordereau->Signature->KeyInfo->X509Data->X509Certificate;
            $x509 = '-----BEGIN CERTIFICATE-----' . chr(10) . $sign . chr(10) . '-----END CERTIFICATE-----';
            //on récupère un tableau contenant les infos du certificat
            $tab = openssl_x509_parse($x509);
            $subject = $tab['subject'];
            $Signataire = $subject['CN'];

            //on récupère la date de signature (il y a surement plus simple)

            $dateMoche = $xml->PES_DepenseAller->Bordereau->Signature->Object->QualifyingProperties->SignedProperties->SignedSignatureProperties->SigningTime;
            list($jourMoche, $heureMoche) = explode('T', $dateMoche);
            list($annee, $jour, $mois) = explode('-', $jourMoche);
            list($heure, $minute, $reste) = explode(':', $heureMoche);
            $date = $jour . '/' . $mois . '/' . $annee . ' ' . intval($heure + 2) . ':' . $minute;

        } elseif(isset($xml->PES_RecetteAller) && count($xml->PES_RecetteAller->Bordereau->Signature)) {
            //si on a une signature  on récupère le certificat
            $sign = $xml->PES_RecetteAller->Bordereau->Signature->KeyInfo->X509Data->X509Certificate;
            $x509 = '-----BEGIN CERTIFICATE-----' . chr(10) . $sign . chr(10) . '-----END CERTIFICATE-----';
            //on récupère un tableau contenant les infos du certificat
            $tab = openssl_x509_parse($x509);
            $subject = $tab['subject'];
            $Signataire = $subject['CN'];

            //on récupère la date de signature (il y a surement plus simple)

            $dateMoche = $xml->PES_RecetteAller->Bordereau->Signature->Object->QualifyingProperties->SignedProperties->SignedSignatureProperties->SigningTime;
            list($jourMoche, $heureMoche) = explode('T', $dateMoche);
            list($annee, $jour, $mois) = explode('-', $jourMoche);
            list($heure, $minute, $reste) = explode(':', $heureMoche);
            $date = $jour . '/' . $mois . '/' . $annee . ' ' . intval($heure + 2) . ':' . $minute;
        }
        else{
            $Signataire = '';
            $date = '';
        }


        $PES = new PES($xml->EnTetePES->LibelleColBud->attributes()[0], $Signataire, $date, $arrayBord, $arrayPJ);
        return array('Bord' => $PES->listBord[$bord], 'idDoc' => $doc->getId());
    }

    /**
     * @Route("/getpj/{id}/{bord}/{piece}/{peji}", name="getpj",  options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function getPJAction($id, $bord, $piece, $peji)
    {

        $em = $this->getDoctrine()->getManager();
        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);


        $param = $this->container->getParameter('upload');
        $dir = $param['fics'];
        $path = $dir . $doc->getRepourl();
        $str = str_ireplace('ns3:', '', str_ireplace('xad:', '', str_ireplace('ds:', '', file_get_contents($path))));
        $xml = simplexml_load_string($str);

        $arrayPJ = array();
        if (isset($xml->PES_PJ)) {
            foreach ($xml->PES_PJ->PJ as $pj) {
                $arrayPJ[] = $pj;
            }
        }

        if (isset($xml->PES_DepenseAller)) {

            foreach ($xml->PES_DepenseAller->Bordereau as $Bord) {
                $Bord->type = 'Depense';
                $arrayBord[] = $Bord;
            }
        }
        if (isset($xml->PES_RecetteAller)) {

            foreach ($xml->PES_RecetteAller->Bordereau as $Bord) {
                $Bord->type = 'Recette';
                $arrayBord[] = $Bord;
            }
        }
        if(!isset($xml->PES_RecetteAller) && !isset($xml->PES_DepenseAller))
        {
            return array('isPJ' => true);
        }
        $Signataire = '';
        $date = '';

        $PES = new PES($xml->EnTetePES->LibelleColBud->attributes()[0], $Signataire, $date, $arrayBord, $arrayPJ);
        $PJ = base64_encode(gzdecode(base64_decode($PES->listBord[$bord]->listPieces[$piece]->listePJs[$peji]->content)));
        return new JsonResponse($PJ);
    }

    /**
     * @Route("/getpjie/{id}/{bord}/{piece}/{peji}", name="getpjie",  options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function getPJieAction($id, $bord, $piece, $peji)
    {

        $em = $this->getDoctrine()->getManager();
        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);


        $param = $this->container->getParameter('upload');
        $dir = $param['fics'];
        $path = $dir . $doc->getRepourl();
        $str = str_ireplace('ns3:', '', str_ireplace('xad:', '', str_ireplace('ds:', '', file_get_contents($path))));
        $xml = simplexml_load_string($str);

        $arrayPJ = array();
        if (isset($xml->PES_PJ)) {
            foreach ($xml->PES_PJ->PJ as $pj) {
                $arrayPJ[] = $pj;
            }
        }

        if (isset($xml->PES_DepenseAller)) {

            foreach ($xml->PES_DepenseAller->Bordereau as $Bord) {
                $Bord->type = 'Depense';
                $arrayBord[] = $Bord;
            }
        }
        if (isset($xml->PES_RecetteAller)) {

            foreach ($xml->PES_RecetteAller->Bordereau as $Bord) {
                $Bord->type = 'Recette';
                $arrayBord[] = $Bord;
            }
        }
        if(!isset($xml->PES_RecetteAller) && !isset($xml->PES_DepenseAller))
        {
            return array('isPJ' => true);
        }
        $Signataire = '';
        $date = '';


        $PES = new PES($xml->EnTetePES->LibelleColBud->attributes()[0], $Signataire, $date, $arrayBord, $arrayPJ);
        $response = new Response();
        /*$PJ = base64_encode(gzdecode(base64_decode($PES->listBord[$bord]->listPieces[$piece]->listePJs[$peji]->content)));
        return new JsonResponse($PJ);*/

        $PJ = gzdecode(base64_decode($PES->listBord[$bord]->listPieces[$piece]->listePJs[$peji]->content));
        //set headers

        /* Download du PDF
        $response->headers->set('Content-Type', 'mime/type');
        $response->headers->set('Content-Disposition', 'attachment;filename=' . $PES->listBord[$bord]->listPieces[$piece]->listePJs[$peji]->nom[0]);
        */
        // Affichage du PDF dans un onglet
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline;filename=' . $PES->listBord[$bord]->listPieces[$piece]->listePJs[$peji]->nom[0]);


        $response->setContent($PJ);
        return $response;
    }
}
