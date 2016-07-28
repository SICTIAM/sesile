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
     * @Route("/uploadpdffile", name="upload_pdf_doc",  options={"expose"=true})
     *
     */
    public function uploadPdfAction(Request $request) {

//        error_log(" - upload PDF" . print_r($request->files->all(),true));
        $repourl = $request->files->get('formpdf')->getClientOriginalName();
        error_log(" - form PDF" . $request->files->get('formpdf')->getClientOriginalName());
        $em = $this->getDoctrine()->getManager();
        $uploadedfile = $request->files->get('formpdf');
//        $id = $request->request->get('id');
        if (empty($uploadedfile)) {
            error_log(" - Upload empty ");
            return new JsonResponse(array("error" => "nothinguploaded"));
        }

//        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);
        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneByRepourl($repourl);
        if (empty($doc)) {
            error_log(" - No document");
            return new JsonResponse(array("error" => "nodocumentwiththisname", "name" => $uploadedfile->getClientOriginalName()));
        }

        if (file_exists('uploads/docs/' . $doc->getRepourl())) {
            unlink('uploads/docs/' . $doc->getRepourl());
            $uploadedfile->move('uploads/docs/', $doc->getRepourl());
            $doc->setSigned(true);
            $em->flush();
            error_log(" - Uploaded !");
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

//        $servername = $this->getRequest()->getHost();
        $servername = $this->get('router')->getContext()->getHost();

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

            // On recupere le dernier utilisateur ayant validé le classeur
            $lastUserId = $doc->getClasseur()->getLastValidant();
            $lastUser = $em->getRepository('SesileUserBundle:User')->findOneById($lastUserId);

            // Recup infos users
            if ($lastUser) {
                $signature = $lastUser->getPathSignature();
            } else{
                $signature = null;
            }
            $city = $this->get('security.context')->getToken()->getUser()->getCollectivite();



            // Recup des thumbs
            if ($doc->getClasseur()->getStatus() == 2 && $doc->getType() == "application/pdf") {

                // Recup du doc pour utiliser SetaPDF
                require($this->container->get('kernel')->getRootDir() . '/../vendor/setapdf/SetaPDF/Autoload.php');
                $filename = 'uploads/docs/' . $doc->getRepourl();
                $document = \SetaPDF_Core_Document::loadByFilename($filename);

                // Pour la première page
                $orientationPDFFirst = $document->getCatalog()->getPages()->getPage(1)->getRotation();
                $imagePDFFirst = $doc->getPDFImage(0, $orientationPDFFirst);


                // Si c est la derniere page
                if (!$city->getPageSignature()){


                    $pages = $document->getCatalog()->getPages();
                    $pageCount = $pages->count();
                    $orientationPDFLast = $document->getCatalog()->getPages()->getLastPage()->getRotation();
                    $imagePDFLast = $doc->getPDFImage($pageCount-1, $orientationPDFLast);
                }
                else {
                    $orientationPDFLast = $orientationPDFFirst;
                    $imagePDFLast = $imagePDFFirst;
                    // $imagePDFLast = $doc->getPDFImage(0, $orientationPDFLast);
                }
            } else {
                $imagePDFFirst = "";
                $imagePDFLast = "";
                $orientationPDFFirst = "";
                $orientationPDFLast = "";
            }

            // coordonnées visa
            if (!$city->getAbscissesVisa()) {
                $abscissesVisa = 10;
            } else {
                // Si on est au format portrait
                if($orientationPDFFirst == 270) {
                    $abscissesVisa = $city->getAbscissesVisa();
                } else {
                    $abscissesVisa = $city->getAbscissesVisa() * 1.63;
                }
            }
            if (!$city->getOrdonneesVisa()) {
                $ordonneesVisa = 10;
            } else {
                // Si on est au format portrait
                if($orientationPDFFirst == 270) {
                    $ordonneesVisa = $city->getOrdonneesVisa();
                } else {
                    $ordonneesVisa = $city->getOrdonneesVisa() * 0.67;
                }
            }

            // coordonnées signature
            if (!$city->getAbscissesSignature()) {
                $abscissesSignature = 10;
            } else {
                // Si on est au format portrait
                if ($orientationPDFLast == 270) {
                    $abscissesSignature = $city->getAbscissesSignature();
                } else {
                    $abscissesSignature = $city->getAbscissesSignature() * 1.65;
                }
            }
            if (!$city->getOrdonneesSignature()) {
                $ordonneesSignature = 10;
            } else {
                // Si on est au format portrait
                if ($orientationPDFLast == 270) {
                    $ordonneesSignature = $city->getOrdonneesSignature();
                } else {
                    $ordonneesSignature = $city->getOrdonneesSignature() * 0.67;
                }
            }



        } else {
            $doc = null;
            $historyinverse = null;
            $name = $id;
            $isValidant = null;
            $signature = null;
            $city = null;

        }


        return array(
            'doc' => $doc,
            'name' => $name,
            'servername' => $servername,
            'historyinverse' => $historyinverse,
            'isvalidant' => $isValidant,
            'signature' => $signature,
            'city' => $city,
            'imagePDFFirst' => $imagePDFFirst,
            'imagePDFLast' => $imagePDFLast,
            'abscissesVisa' => $abscissesVisa,
            'ordonneesVisa' => $ordonneesVisa,
            'abscissesSignature' => $abscissesSignature,
            'ordonneesSignature' => $ordonneesSignature,
            'orientationPDFFirst' => $orientationPDFFirst,
            'orientationPDFLast' => $orientationPDFLast
        );

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
     * @Route("/download_visa/{id}/{absVisa}/{ordVisa}", name="download_doc_visa",  options={"expose"=true})
     *
     */
    public function downloadVisaAction($id, $absVisa = 10, $ordVisa = 10)
    {

        require($this->get('kernel')->getRootDir() . '/../vendor/setapdf/SetaPDF/Autoload.php');

        $em = $this->getDoctrine()->getManager();
        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);
        // Ecriture de l'hitorique du document
        $id_user = $this->get('security.context')->getToken()->getUser()->getId();
        $user = $em->getRepository('SesileUserBundle:User')->findOneByid($id_user);
        $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($doc, "Téléchargement du document par " . $user->getPrenom() . " " . $user->getNom(), null);

        $city = $user->getCollectivite();

        /* SetaPDF */

        $firstPage = true;
//        $texteVisa = 'VISE PAR';
        $texteVisa = $city->getTitreVisa();
        $classeurId = $doc->getClasseur()->getId();
//        $color = '#454545';
        $color = $city->getCouleurVisa();

        $em->getRepository('SesileDocumentBundle:Document')->setaPDFTamponVisa($doc->getRepourl(), $classeurId, $absVisa, $ordVisa, $firstPage, $texteVisa, $color);
        /* FIN SetaPDF */

    }

    /**
     * @Route("/download_sign/{id}/{absSign}/{ordSign}", name="download_doc_sign",  options={"expose"=true})
     *
     */
    public function downloadSignAction($id, $absSign = 10, $ordSign = 10) {
        require($this->get('kernel')->getRootDir() . '/../vendor/setapdf/SetaPDF/Autoload.php');

        $em = $this->getDoctrine()->getManager();
        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);
        // Ecriture de l'hitorique du document
        $id_user = $this->get('security.context')->getToken()->getUser()->getId();
        $user = $em->getRepository('SesileUserBundle:User')->findOneByid($id_user);
        $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($doc, "Téléchargement du document par " . $user->getPrenom() . " " . $user->getNom(), null);

        // On recupère la collectivité pour ses paramètres
        $city = $user->getCollectivite();

        // On recupere le dernier utilisateur ayant validé le classeur
        $lastUserId = $doc->getClasseur()->getLastValidant();
        $lastUser = $em->getRepository('SesileUserBundle:User')->findOneById($lastUserId);

        /* SetaPDF */

        $firstPage = $city->getPageSignature();

        $imageSignature = $this->container->getParameter('upload')['signatures'] . $lastUser->getPathSignature();

//        $em->getRepository('SesileDocumentBundle:Document')->setaPDFTampon($doc->getRepourl(), $classeurId, $translateX, $translateY, $firstPage, $texteVisa, false, $imageSignature);
        $em->getRepository('SesileDocumentBundle:Document')->setaPDFTamponSignature($doc->getRepourl(), $absSign, $ordSign, $firstPage, $imageSignature, $lastUser);
        /* FIN SetaPDF */
    }

    /**
     * @Route("/download_all/{id}/{absVisa}/{ordVisa}/{absSign}/{ordSign}", name="download_doc_all",  options={"expose"=true})
     *
     */
    public function downloadAllAction($id, $absVisa = 10, $ordVisa = 10, $absSign = 10, $ordSign = 10) {
        require($this->get('kernel')->getRootDir() . '/../vendor/setapdf/SetaPDF/Autoload.php');

        $em = $this->getDoctrine()->getManager();
        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);
        // Ecriture de l'hitorique du document
        $id_user = $this->get('security.context')->getToken()->getUser()->getId();
        $user = $em->getRepository('SesileUserBundle:User')->findOneByid($id_user);
        $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($doc, "Téléchargement du document par " . $user->getPrenom() . " " . $user->getNom(), null);

        $city = $user->getCollectivite();

        // On recupere le dernier utilisateur ayant validé le classeur
        $lastUserId = $doc->getClasseur()->getLastValidant();
        $lastUser = $em->getRepository('SesileUserBundle:User')->findOneById($lastUserId);

        /* SetaPDF */

        $texteVisa = $city->getTitreVisa();
        $color = $city->getCouleurVisa();
        $firstVisa = true;

        $firstSign = $city->getPageSignature();
        $classeurId = $doc->getClasseur()->getId();
        $imageSignature = $this->container->getParameter('upload')['signatures'] . $lastUser->getPathSignature();

        $em->getRepository('SesileDocumentBundle:Document')->setaPDFTamponALL($doc->getRepourl(), $classeurId, $absVisa, $ordVisa, $absSign, $ordSign, $firstSign, $firstVisa, $imageSignature, $texteVisa, $color, $lastUser);
        /* FIN SetaPDF */
    }


    /**
     * @Route("/{id}/delete", name="delete_document",  options={"expose"=true})
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id) {

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
            $date = $jour . '/' . $mois . '/' . $annee . ' ' . intval($heure) . ':' . $minute;

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
            $date = $jour . '/' . $mois . '/' . $annee . ' ' . intval($heure) . ':' . $minute;
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
        $PJName = $PES->listBord[$bord]->listPieces[$piece]->listePJs[$peji]->nom[0];

        // On recupere l extension de la PJ
        $extension = explode('.', $PJName);
        $PJextension = end($extension);

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
        if ($PJextension != "zip") {
            $response->headers->set('Content-Type', 'application/pdf');
            $response->headers->set('Content-Disposition', 'inline;filename=' . $PJName);
        }
        // Download des zip
        else {
            $response->headers->set('Content-Type', 'application/zip');
            $response->headers->set('Content-disposition', 'inline;filename=' . $PJName);
        }


        $response->setContent($PJ);
        return $response;
    }


}
