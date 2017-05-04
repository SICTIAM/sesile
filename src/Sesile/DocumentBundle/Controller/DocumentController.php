<?php

namespace Sesile\DocumentBundle\Controller;

//use Sabre\VObject\Property\DateTime;
use Sesile\DocumentBundle\Entity\DocumentDetachedSign;
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

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
        $path = $this->container->getParameter('upload')['fics'];
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
            $tailles[$doc->getId()] = filesize($path . $doc->getRepoUrl());
            $types[$doc->getName()] = $doc->getType();
            $ids[$doc->getId()] = $doc->getName();
        }

        // On recupere le dernier utilisateur ayant validé le classeur
        $lastUser = $em->getRepository('SesileUserBundle:User')->findOneById($doc->getClasseur()->getLastValidant());

        // Recup infos users
        $lastUser ? $signature = $lastUser->getPathSignature() : $signature = null;

        return array(
            'names'     => $names,
            'docs'      => $docs,
            'classeur'  => $classeur,
            'tailles'   => $tailles,
            'types'     => $types,
            'ids'       => $ids,
            'isvalidable' => $isvalidable,
            'signature' => $signature
        );

    }

    /**
     * @Route("/uploadfile", name="upload_doc",  options={"expose"=true})
     *
     */
    public function uploadAction(Request $request) {


        $em = $this->getDoctrine()->getManager();
        $uploadedfile = $request->files->get('signedFile');
        $id = $request->request->get('id');
        $path = $this->container->getParameter('upload')['fics'];

        if (empty($uploadedfile)) {
            return new JsonResponse(array("error" => "nothinguploaded"));
        }

        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);
        if (empty($doc)) {
            return new JsonResponse(array("error" => "nodocumentwiththisname", "name" => $uploadedfile->getClientOriginalName()));
        }

        if (file_exists($path . $doc->getRepourl())) {
            unlink($path . $doc->getRepourl());
            $uploadedfile->move($path, $doc->getRepourl());
            $doc->setSigned(true);
            $em->flush();
            return new JsonResponse(array("error" => "ok", "url" => $path . $doc->getRepourl()));

        } else {
            unlink($uploadedfile->getRealPath());

            return new JsonResponse(array("error" => "nodocumentwiththisname"));

        }


    }


    /**
     * @Route("/statusdocument/{id}", name="status_document",  options={"expose"=true})
     *
     */
    public function statusDocumentAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();
        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);


        return new JsonResponse($doc->getSigned());
    }

    /**
     * @Route("/uploaddocument/{id}/{token}", name="upload_document_fron_jws")
     *
     */
    public function uploadDocumentAction(Request $request, $id, $token = null) {

        $em = $this->getDoctrine()->getManager();

        // Récupération des variables
        $uploadedfile = $request->files->get('upload-file');
        $path = $this->container->getParameter('upload')['fics'];


        // Verification des paramètres
        if (empty($uploadedfile)) {
            return new JsonResponse(array("error" => "nothinguploaded"));
        }

        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);

        // Vérification que le document existe
        if (empty($doc)) {
            return new JsonResponse(array("error" => "nodocumentwiththisname", "name" => $uploadedfile->getClientOriginalName()));
        }

        // Vérification du token
        if ($doc->getToken() !== null && $doc->getToken() == $token && file_exists($path . $doc->getRepourl())) {

            // On renomme le document pour indiquer qu il est signéz
            $ancienNom = $doc->getName();
            $path_parts = pathinfo($ancienNom);
            $nouveauNom = $path_parts['filename'] . '-sign.' . $path_parts['extension'];

            $typeDocument = $doc->getType();

            // Si le document renvoyé est signature détachée
            // Dans le cas d un CADES
            if ($typeDocument != "application/xml" && $typeDocument != "application/pdf") {

                $dateToday = new \DateTime();

                $docSignNom = $path_parts['filename'] . '-sign';
                $path_doc = pathinfo($doc->getRepourl());
                $documentSignedURL = $path_doc['filename'] . '-sign-' . $dateToday->format('YmdHis');
                // Upload du nouveau fichier
                $uploadedfile->move($path, $documentSignedURL);
                $documentSign = new DocumentDetachedSign();
                $documentSign->setName($docSignNom);
                $documentSign->setRepourl($documentSignedURL);
                $documentSign->setDocument($doc);
                $em->persist($documentSign);

            }
            // Dans les autres cas : pades, xades, xades-pes
            else {
                unlink($path . $doc->getRepourl());

                // Upload du nouveau fichier
                $uploadedfile->move($path, $doc->getRepourl());
                // On enregistre le nouveau nom
                $doc->setName($nouveauNom);
            }

            // On valide la singature
            $doc->setSigned(true);

            // On supprime le token pour plus que le doc soit DL
            $doc->setToken(null);

            // On enregistre les données
            $em->flush();
            return new JsonResponse(array("error" => "ok", "url" => $path . $doc->getRepourl()));

        } else {
            unlink($uploadedfile->getRealPath());

            return new JsonResponse(array("error" => "nodocumentwiththisname"));

        }

    }


    /**
     * @Route("/uploadpdffile", name="upload_pdf_doc",  options={"expose"=true})
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadPdfAction(Request $request) {

        $repourl = $request->files->get('formpdf')->getClientOriginalName();
        $em = $this->getDoctrine()->getManager();
        $uploadedfile = $request->files->get('formpdf');
        $path = $this->container->getParameter('upload')['fics'];

        if (empty($uploadedfile)) {
            return new JsonResponse(array("error" => "nothinguploaded"));
        }

        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneByRepourl($repourl);
        if (empty($doc)) {
            error_log(" - No document");
            return new JsonResponse(array("error" => "nodocumentwiththisname", "name" => $uploadedfile->getClientOriginalName()));
        }

        if (file_exists($path . $doc->getRepourl())) {
            unlink($path . $doc->getRepourl());
            $uploadedfile->move($path, $doc->getRepourl());
            $doc->setSigned(true);
            $em->flush();
            return new JsonResponse(array("error" => "ok", "url" => $path . $doc->getRepourl()));

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
            $city = $this->get('security.token_storage')->getToken()->getUser()->getCollectivite();



            // Recup des thumbs
            if ($doc->getClasseur()->getStatus() == 2 && $doc->getType() == "application/pdf") {

                // Recup du doc pour utiliser SetaPDF
                $filename = $this->container->getParameter('upload')['fics'] . $doc->getRepourl();
                $document = \SetaPDF_Core_Document::loadByFilename($filename);

                // Pour la première page
//                $orientationPDFFirst = $document->getCatalog()->getPages()->getPage(1)->getRotation();
                $orientationPDFFirst = $this->getDocumentOrientation($document->getCatalog()->getPages()->getPage(1)->getWidthAndHeight());

                $path = $this->container->getParameter('upload')['fics'];
                $imagePDFFirst = $doc->getPDFImage(0, $orientationPDFFirst, $path);


                // Si c est la derniere page
                if (!$city->getPageSignature()){


                    $pages = $document->getCatalog()->getPages();
                    $pageCount = $pages->count();
                    $orientationPDFLast = $this->getDocumentOrientation($document->getCatalog()->getPages()->getLastPage()->getWidthAndHeight());
                    $imagePDFLast = $doc->getPDFImage($pageCount-1, $orientationPDFLast, $path);
                }
                else {
                    $orientationPDFLast = $orientationPDFFirst;
                    $imagePDFLast = $imagePDFFirst;
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
                if($orientationPDFFirst == "PORTRAIT") {
                    $abscissesVisa = $city->getAbscissesVisa();
                } else {
                    $abscissesVisa = $city->getAbscissesVisa() * 1.63;
                }
            }
            if (!$city->getOrdonneesVisa()) {
                $ordonneesVisa = 10;
            } else {
                // Si on est au format portrait
                if($orientationPDFFirst == "PORTRAIT") {
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
                if ($orientationPDFLast == "PORTRAIT") {
                    $abscissesSignature = $city->getAbscissesSignature();
                } else {
                    $abscissesSignature = $city->getAbscissesSignature() * 1.65;
                }
            }
            if (!$city->getOrdonneesSignature()) {
                $ordonneesSignature = 10;
            } else {
                // Si on est au format portrait
                if ($orientationPDFLast == "PORTRAIT") {
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

    private function authorizeToDownloadDocument($visible, $user) {
        // user courant
        $repository = $this->getDoctrine()->getRepository('SesileDelegationsBundle:delegations');
        $usersdelegated = $repository->getUsersWhoHasMeAsDelegateRecursively($user);
        $usersdelegated[] = $user;

        // Verification que l utilisateur a bien les droits
        $usersForClasseur = $visible;
        // Si l'utilisateur n a pas les droits, on l eject
        if(!array_intersect($usersdelegated, $usersForClasseur->toArray())
            && !$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')
            && !$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')
        ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @Route("/download/{id}", name="download_doc",  options={"expose"=true})
     *
     */
    public function downloadAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();
        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);

        // Verif des autorisations
        if(!$this->authorizeToDownloadDocument($doc->getClasseur()->getVisible(), $this->getUser())) {
            return $this->render('SesileMainBundle:Default:errorrestricted.html.twig');
        }

        $path = $this->container->getParameter('upload')['fics'];
        $user = $em->getRepository('SesileUserBundle:User')->findOneByid($this->getUser()->getId());

        // Ecriture de l'hitorique du document
        $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($doc, "Téléchargement du document par " . $user->getPrenom() . " " . $user->getNom(), null);

        $response = new Response();

        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($path . $doc->getRepourl()));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $doc->getName() . '"');
        $response->headers->set('Content-Length', filesize($path . $doc->getRepourl()));

        $response->setContent(file_get_contents($path . $doc->getRepourl()));

        return $response;
    }

    /**
     * @Route("/download_zip/{id}", name="download_doc_zip",  options={"expose"=true})
     *
     */
    public function downloadZipAction($id)
    {

        // Recuperation du classeur
        $em = $this->getDoctrine()->getManager();
        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);

        // Verif des autorisations
        if(!$this->authorizeToDownloadDocument($doc->getClasseur()->getVisible(), $this->getUser())) {
            return $this->render('SesileMainBundle:Default:errorrestricted.html.twig');
        }

        $path = $this->container->getParameter('upload')['fics'];

        // Ecriture de l'hitorique du document
        $id_user = $this->get('security.token_storage')->getToken()->getUser()->getId();
        $user = $em->getRepository('SesileUserBundle:User')->findOneByid($id_user);
        $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($doc, "Téléchargement du document par " . $user->getPrenom() . " " . $user->getNom(), null);

        // On créé le fichier ZIP
        $zip = new \ZipArchive();
        $zipRepoUrl = $path . $doc->getRepourl() . '.zip';
        if($zip->open($zipRepoUrl, \ZipArchive::CREATE) === true) {

            // On ajoute le fichier original
            $zip->addFile($path . $doc->getRepourl(), $doc->getName());

            // On ajoute tous les fichiers signés
            foreach ($doc->getDetachedsign() as $detachedFile) {
                $zip->addFile($path . $detachedFile->getRepourl(), $detachedFile->getName());
            }


            // On finalise le zip
            $zip->close();
        } else {
            die('Impossible de créer une archive.');
        }

        // On créé la réponse pour télécharger le fichier zip
        $response = new Response();

        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $doc->getName() . '.zip"');
        $response->headers->set('Content-Length', filesize($zipRepoUrl));

        $response->setContent(file_get_contents($zipRepoUrl));

        // Suppression du zip
        unlink($zipRepoUrl);

        return $response;
    }

    /**
     * @Route("/downloadJWS/{name}/{token}", name="download_jws_doc",  options={"expose"=true})
     *
     */
    public function downloadJWSAction(Request $request, $name, $token = null)
    {
        $em = $this->getDoctrine()->getManager();
        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneBy(array('repourl' => $name));

        $path = $this->container->getParameter('upload')['fics'];

        if($doc->getToken() !== null && $doc->getToken() == $token) {
            $response = new Response();

            $response->headers->set('Cache-Control', 'private');
            $response->headers->set('Content-type', mime_content_type($path . $doc->getRepourl()));
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $doc->getName() . '"');
            $response->headers->set('Content-Length', filesize($path . $doc->getRepourl()));

            $response->setContent(file_get_contents($path . $doc->getRepourl()));

            return $response;
        } else {
            return new JsonResponse(array("Requete invalide" => "0"));
        }

    }

    /**
     * @Route("/download_visa/{id}/{absVisa}/{ordVisa}", name="download_doc_visa",  options={"expose"=true})
     * @param $id
     * @param int $absVisa
     * @param int $ordVisa
     * @return Response
     */
    public function downloadVisaAction($id, $absVisa = 10, $ordVisa = 10)
    {
        $em = $this->getDoctrine()->getManager();
        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);

        // Verif des autorisations
        if(!$this->authorizeToDownloadDocument($doc->getClasseur()->getVisible(), $this->getUser())) {
            return $this->render('SesileMainBundle:Default:errorrestricted.html.twig');
        }

        // Ecriture de l'hitorique du document
        $id_user = $this->get('security.token_storage')->getToken()->getUser()->getId();
        $user = $em->getRepository('SesileUserBundle:User')->findOneByid($id_user);
        $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($doc, "Téléchargement du document par " . $user->getPrenom() . " " . $user->getNom(), null);

        $city = $user->getCollectivite();
        $path = $this->container->getParameter('upload')['fics'];

        /* SetaPDF */
        $em->getRepository('SesileDocumentBundle:Document')->setaPDFTamponVisa($doc->getRepourl(),
            $doc->getClasseur()->getId(),
            $absVisa,
            $ordVisa,
            true,
            $city->getTitreVisa(),
            $city->getCouleurVisa(),
            $path
        );
        /* FIN SetaPDF */

    }

    /**
     * @Route("/download_sign/{id}/{absSign}/{ordSign}", name="download_doc_sign",  options={"expose"=true})
     *
     */
    public function downloadSignAction($id, $absSign = 10, $ordSign = 10) {

        $em = $this->getDoctrine()->getManager();
        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);

        // Verif des autorisations
        if(!$this->authorizeToDownloadDocument($doc->getClasseur()->getVisible(), $this->getUser())) {
            return $this->render('SesileMainBundle:Default:errorrestricted.html.twig');
        }

        // Ecriture de l'hitorique du document
        $id_user = $this->get('security.token_storage')->getToken()->getUser()->getId();
        $user = $em->getRepository('SesileUserBundle:User')->findOneByid($id_user);
        $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($doc, "Téléchargement du document par " . $user->getPrenom() . " " . $user->getNom(), null);

        // On recupère la collectivité pour ses paramètres
        $city = $user->getCollectivite();
        $path = $this->container->getParameter('upload')['fics'];

        // On recupere le dernier utilisateur ayant validé le classeur
        $lastUser = $em->getRepository('SesileUserBundle:User')->findOneById($doc->getClasseur()->getLastValidant());

        /* SetaPDF */
        $imageSignature = $this->container->getParameter('upload')['signatures'] . $lastUser->getPathSignature();

        $em->getRepository('SesileDocumentBundle:Document')->setaPDFTamponSignature($doc->getRepourl(),
            $absSign,
            $ordSign,
            $city->getPageSignature(),
            $imageSignature,
            $lastUser,
            $doc->getClasseur()->getId(),
            $path
        );
        /* FIN SetaPDF */
    }

    /**
     * @Route("/download_all/{id}/{absVisa}/{ordVisa}/{absSign}/{ordSign}", name="download_doc_all",  options={"expose"=true})
     *
     */
    public function downloadAllAction($id, $absVisa = 10, $ordVisa = 10, $absSign = 10, $ordSign = 10) {

        $em = $this->getDoctrine()->getManager();
        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);
        // Ecriture de l'hitorique du document
        $id_user = $this->get('security.token_storage')->getToken()->getUser()->getId();
        $user = $em->getRepository('SesileUserBundle:User')->findOneByid($id_user);
        $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($doc, "Téléchargement du document par " . $user->getPrenom() . " " . $user->getNom(), null);

        $city = $user->getCollectivite();
        $path = $this->container->getParameter('upload')['fics'];

        // On recupere le dernier utilisateur ayant validé le classeur
        $lastUser = $em->getRepository('SesileUserBundle:User')->findOneById($doc->getClasseur()->getLastValidant());
        $imageSignature = $this->container->getParameter('upload')['signatures'] . $lastUser->getPathSignature();
        /* SetaPDF */

        $em->getRepository('SesileDocumentBundle:Document')->setaPDFTamponALL(
            $doc->getRepourl(),
            $doc->getClasseur()->getId(),
            $absVisa,
            $ordVisa,
            $absSign,
            $ordSign,
            $city->getPageSignature(),
            true,
            $imageSignature,
            $city->getTitreVisa(),
            $city->getCouleurVisa(),
            $lastUser,
            $path
        );
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
     * @Route("/download_doc_all_files/{id}", name="download_doc_all_files")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param Classeur $classeur
     * @return Response
     */
    public function downloadDocAllFilesAction(Classeur $classeur)
    {
        return $this->zipFileDownload($classeur, "NONE");
    }

    /**
     * @Route("/download_doc_visa_all_files/{id}", name="download_doc_visa_all_files")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param Classeur $classeur
     * @return Response
     */
    public function downloadDocVisaAllFilesAction(Classeur $classeur)
    {
        return $this->zipFileDownload($classeur, "VISA");
    }

    /**
     * @Route("/download_doc_sign_all_files/{id}", name="download_doc_sign_all_files")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param Classeur $classeur
     * @return Response
     */
    public function downloadDocSignAllFilesAction(Classeur $classeur)
    {
        return $this->zipFileDownload($classeur, "SIGN");

    }
    /**
     * @Route("/download_doc_all_all_files/{id}", name="download_doc_all_all_files")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param Classeur $classeur
     * @return Response
     */
    public function downloadDocAllAllFilesAction(Classeur $classeur)
    {
        return $this->zipFileDownload($classeur, "ALL");
    }

    /**
     * @param $classeur
     * @param $type
     * @return Response
     */
    private function zipFileDownload ($classeur, $type) {

        // Verif des autorisations
        if(!$this->authorizeToDownloadDocument($classeur->getVisible(), $this->getUser())) {
            return $this->render('SesileMainBundle:Default:errorrestricted.html.twig');
        }

        // Ecriture de l'historique du document
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $docs = $classeur->getDocuments();

        $path = $this->container->getParameter('upload')['fics'];

        // On créé le fichier ZIP
        $zip = new \ZipArchive();
        $zipRepoUrl = $path . 'classeur_' . $classeur->getId() . '.zip';

        $em = $this->getDoctrine()->getManager();

        foreach ($docs as $doc) {
            $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($doc, "Téléchargement du document par " . $user->getPrenom() . " " . $user->getNom(), null);
            if($zip->open($zipRepoUrl, \ZipArchive::CREATE) === true) {

                $files_to_delete = array();

                if ($doc->getType() != "application/pdf" || $type == "NONE") {
                    // On ajoute le fichier original
                    $zip->addFile($path . $doc->getRepourl(), $doc->getName());

                    // On ajoute tous les fichiers signés
                    foreach ($doc->getDetachedsign() as $detachedFile) {
                        $zip->addFile($path . $detachedFile->getRepourl(), $detachedFile->getName());
                    }
                }
                else {
                    $city = $user->getCollectivite();
                    $lastUser = $em->getRepository('SesileUserBundle:User')->findOneById($doc->getClasseur()->getLastValidant());
                    $imageSignature = $this->container->getParameter('upload')['signatures'] . $lastUser->getPathSignature();


                    /* SetaPDF */
                    // on créé le fichier avec le visa
                    switch ($type) {
                        case "VISA" :
                            $em->getRepository('SesileDocumentBundle:Document')->setaPDFTamponVisaAll(
                                $doc->getRepourl(),
                                $doc->getClasseur()->getId(),
                                $city->getAbscissesVisa(),
                                $city->getOrdonneesVisa(),
                                true,
                                $city->getTitreVisa(),
                                $city->getCouleurVisa(),
                                $path
                            );
                            break;
                        case "SIGN" :
                            $em->getRepository('SesileDocumentBundle:Document')->setaPDFTamponSignatureAll(
                                $doc->getRepourl(),
                                $city->getAbscissesSignature(),
                                $city->getOrdonneesSignature(),
                                $city->getPageSignature(),
                                $imageSignature,
                                $lastUser,
                                $doc->getClasseur()->getId(),
                                $path
                            );
                            break;
                        case "ALL" :
                            $em->getRepository('SesileDocumentBundle:Document')->setaPDFTamponALLFiles(
                                $doc->getRepourl(),
                                $doc->getClasseur()->getId(),
                                $city->getAbscissesVisa(),
                                $city->getOrdonneesVisa(),
                                $city->getAbscissesSignature(),
                                $city->getOrdonneesSignature(),
                                $city->getPageSignature(),
                                true,
                                $imageSignature,
                                $city->getTitreVisa(),
                                $city->getCouleurVisa(),
                                $lastUser,
                                $path
                            );
                            break;
                    }

                    /* FIN SetaPDF */

                    // on ajoute le fichier
                    $zip->addFile($path . 'visa-' . $doc->getRepourl(), $doc->getName());

                    // on supprime le fichier
                    $files_to_delete[] = $path . 'visa-' . $doc->getRepourl();

                }

                // On finalise le zip
                $zip->close();

                foreach ($files_to_delete as $file_to_delete) {
                    @unlink($file_to_delete);
                }
            } else {
                die('Impossible de créer une archive.');
            }
        }

        // On créé la réponse pour télécharger le fichier zip
        $response = new Response();
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $classeur->getId() . '.zip"');
        $response->headers->set('Content-Length', filesize($zipRepoUrl));
        $response->setContent(file_get_contents($zipRepoUrl));

        // Suppression du zip
        unlink($zipRepoUrl);

        return $response;

    }

/*
    /**
     * @Route("/edit-history/{id}/document", name="edit_history_document",  options={"expose"=true})
     * @Method("POST")
     */
    /*public function editHistoryFactory($id) {
        $em = $this->getDoctrine()->getManager();
        $doc = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);
        // Ecriture de l'hitorique du document
        $id_user = $this->get('security.token_storage')->getToken()->getUser()->getId();
        $user = $em->getRepository('SesileUserBundle:User')->findOneByid($id_user);
        $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($doc, "Edition du document par " . $user->getPrenom() . " " . $user->getNom(), null);

        return new Response();
    }*/


    /**
     * @Route("/visu/{id}/{ajax}", name="visu",  options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function visuAction($id, $ajax = false)
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

        if (!in_array($entity, $user->getClasseurs()->toArray()) and !$editDelegants && !$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $this->get('session')->getFlashBag()->add(
                'error',
                "Vous n'avez pas accès à ce classeur"
            );
            return $this->redirect($this->generateUrl('classeur'));
        }



        // Ecriture de l'hitorique du document
        $id_user = $this->get('security.token_storage')->getToken()->getUser()->getId();
        $user = $em->getRepository('SesileUserBundle:User')->findOneByid($id_user);
        $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($doc, "Visualisation du document par " . $user->getPrenom() . " " . $user->getNom(), null);


        $param = $this->container->getParameter('upload');
        $dir = $param['fics'];
        $path = $dir . $doc->getRepourl();

        // on enleve tout les putains de préfixes de mes 2

        $str = str_ireplace('ns3:', '', str_ireplace('xad:', '', str_ireplace('ds:', '', file_get_contents($path))));
        //$xml = simplexml_load_string($str);
        $xml = simplexml_load_string($str, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE);

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

            $date = $xml->PES_DepenseAller->Bordereau->Signature->Object->QualifyingProperties->SignedProperties->SignedSignatureProperties->SigningTime;

        } elseif(isset($xml->PES_RecetteAller) && count($xml->PES_RecetteAller->Bordereau->Signature)) {
            //si on a une signature  on récupère le certificat
            $sign = $xml->PES_RecetteAller->Bordereau->Signature->KeyInfo->X509Data->X509Certificate;
            $x509 = '-----BEGIN CERTIFICATE-----' . chr(10) . $sign . chr(10) . '-----END CERTIFICATE-----';
            //on récupère un tableau contenant les infos du certificat
            $tab = openssl_x509_parse($x509);
            $subject = $tab['subject'];
            $Signataire = $subject['CN'];

            //on récupère la date de signature (il y a surement plus simple)

            $date = $xml->PES_RecetteAller->Bordereau->Signature->Object->QualifyingProperties->SignedProperties->SignedSignatureProperties->SigningTime;

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

        return array(
            'budget' => $PES->budget,
            'signataire' => utf8_decode($PES->signataire),
            'dateSign' => $PES->dateSign,
            'bords' => $tabIdBord,
            'idDoc' => $doc->getId(),
            'ajax' => $ajax,
            'user' => $user,
            'classeur' => $entity,
            'signable'  => $entity->isSignableAndLastValidant(),
            'classeursId'  => urlencode(serialize($entity->getId()))
        );
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
        //$xml = simplexml_load_string($str);
        $xml = simplexml_load_string($str, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE);

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

            $date = $xml->PES_DepenseAller->Bordereau->Signature->Object->QualifyingProperties->SignedProperties->SignedSignatureProperties->SigningTime;

        } elseif(isset($xml->PES_RecetteAller) && count($xml->PES_RecetteAller->Bordereau->Signature)) {
            //si on a une signature  on récupère le certificat
            $sign = $xml->PES_RecetteAller->Bordereau->Signature->KeyInfo->X509Data->X509Certificate;
            $x509 = '-----BEGIN CERTIFICATE-----' . chr(10) . $sign . chr(10) . '-----END CERTIFICATE-----';
            //on récupère un tableau contenant les infos du certificat
            $tab = openssl_x509_parse($x509);
            $subject = $tab['subject'];
            $Signataire = $subject['CN'];

            //on récupère la date de signature (il y a surement plus simple)

            $date = $xml->PES_RecetteAller->Bordereau->Signature->Object->QualifyingProperties->SignedProperties->SignedSignatureProperties->SigningTime;

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
        $xml = simplexml_load_string($str, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE);

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
        $xml = simplexml_load_string($str, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE);

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
        $PJextension = strtolower(end($extension));

        $response = new Response();
        /*$PJ = base64_encode(gzdecode(base64_decode($PES->listBord[$bord]->listPieces[$piece]->listePJs[$peji]->content)));
        return new JsonResponse($PJ);*/

        $PJ = gzdecode(base64_decode($PES->listBord[$bord]->listPieces[$piece]->listePJs[$peji]->content));
        //set headers


        // Affichage du bordereau dans un onglet
        if ($PJextension == "pdf") {
            $response->headers->set('Content-Type', 'application/pdf');
        }
        // Download des zip
        else if ($PJextension == "zip") {
            $response->headers->set('Content-Type', 'application/zip');
        }
        else if ($PJextension == "xhl") {
            $response->headers->set('Content-Type', 'application/xhl');
        }
        else {
            $response->headers->set('Content-Type', 'application/zip');
        }
        $response->headers->set('Content-disposition', 'inline;filename=' . $PJName);


        $response->setContent($PJ);
        return $response;
    }

    /**
     * @param $widthAndHeight
     * @return string PORTRAIT|PAYSAGE
     *
     * Fonction permettant de déterminer si la page est au format paysage ou portrait
     */
    private function getDocumentOrientation(array $widthAndHeight) {
        $width = $widthAndHeight[0];
        $height = $widthAndHeight[1];

        if ($width < $height) {
            return "PORTRAIT";
        }
        else {
            return "PAYSAGE";
        }

    }
}
