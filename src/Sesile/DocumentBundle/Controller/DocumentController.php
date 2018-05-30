<?php

namespace Sesile\DocumentBundle\Controller;

use Sesile\DocumentBundle\Entity\DocumentDetachedSign;
use Sesile\MainBundle\Entity\Collectivite;
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

/**
 * @Route("/doc")
 */
class DocumentController extends Controller
{
    const DEFAULT_ABS = 10;
    const DEFAULT_ORD = 10;

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
     *
     * @todo valider que ceci n'est pas utilisé
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
            //@todo refactor getUser()->getCollectivite()
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

    private function authorizeToDownloadDocument($visible, $user)
    {
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
     * @ParamConverter("Document", options={"mapping": {"id": "id"}})
     * @param Document $doc
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     */
    public function downloadAction(Document $doc)
    {

        $em = $this->getDoctrine()->getManager();

        // Verif des autorisations
        if(!$this->authorizeToDownloadDocument($doc->getClasseur()->getVisible(), $this->getUser())) {
            return $this->render('SesileMainBundle:Default:errorrestricted.html.twig');
        }

        $path = $this->container->getParameter('upload')['fics'];
        $user = $em->getRepository('SesileUserBundle:User')->findOneByid($this->getUser()->getId());

        $response = new Response();
        if(!is_file($path . $doc->getRepourl())) {
            $response->setContent("No file");
            $response->setStatusCode(204);
            return $response;
        }

        // Ecriture de l'hitorique du document
        $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($doc, "Téléchargement du document par " . $user->getPrenom() . " " . $user->getNom(), null);
        $doc->setDownloaded(true);
        $em->flush();

        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($path . $doc->getRepourl()));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $doc->getName() . '"');
        $response->headers->set('Content-Length', filesize($path . $doc->getRepourl()));

        $response->setContent(file_get_contents($path . $doc->getRepourl()));

        return $response;
    }

    /**
     * @Route("/download_zip/{id}", name="download_doc_zip",  options={"expose"=true})
     * @ParamConverter("Document", options={"mapping": {"id": "id"}})
     */
    public function downloadZipAction(Document $doc)
    {

        // Recuperation du classeur
        $em = $this->getDoctrine()->getManager();

        // Verif des autorisations
        if(!$this->authorizeToDownloadDocument($doc->getClasseur()->getVisible(), $this->getUser())) {
            return $this->render('SesileMainBundle:Default:errorrestricted.html.twig');
        }

        $path = $this->container->getParameter('upload')['fics'];

        // Ecriture de l'hitorique du document
        $id_user = $this->get('security.token_storage')->getToken()->getUser()->getId();
        $user = $em->getRepository('SesileUserBundle:User')->findOneByid($id_user);
        $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($doc, "Téléchargement du document par " . $user->getPrenom() . " " . $user->getNom(), null);
        $doc->setDownloaded(true);
        $em->flush();

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
     * @Route("/org/{orgId}/download_visa/{id}/{absVisa}/{ordVisa}", defaults={"absVisa"=10, "ordVisa"=10}, name="download_doc_visa",  options={"expose"=true})
     * @ParamConverter("Document", options={"mapping": {"id": "id"}})
     * @param string $orgId
     * @param Document $doc
     * @param int $absVisa
     * @param int $ordVisa
     * @return Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \SetaPDF_Core_Exception
     * @todo refactor route on frontend
     */
    public function downloadVisaAction($orgId, Document $doc, $absVisa, $ordVisa)
    {
        $em = $this->getDoctrine()->getManager();

        // Verif des autorisations
        //@todo authorizeToDownloadDocument contient la notion de délégation qui n'est plus d'actualité
        if(!$this->authorizeToDownloadDocument($doc->getClasseur()->getVisible(), $this->getUser())) {
            return new JsonResponse(['message' => 'Vous n\'avez pas accès à ce document'], Response::HTTP_FORBIDDEN);
        }


        // Ecriture de l'hitorique du document
        $id_user = $this->getUser()->getId();
        $user = $em->getRepository('SesileUserBundle:User')->findOneByid($id_user);
        if (!$user || false === $user->hasCollectivity($orgId)) {
            return new JsonResponse(['message' => 'Vous n\'avez pas accès à la collectivité demandé'], Response::HTTP_FORBIDDEN);
        }
        $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($doc, "Téléchargement du document par " . $user->getPrenom() . " " . $user->getNom(), null);
        $doc->setDownloaded(true);
        $em->flush();
        $collectivity = $user->getCollectivityById($orgId);
        list($absVisa, $ordVisa) = $this->defineVisaPosition($absVisa, $ordVisa, $collectivity);
        $path = $this->container->getParameter('upload')['fics'];

        /* SetaPDF */
        $em->getRepository('SesileDocumentBundle:Document')->setaPDFTamponVisa($doc->getRepourl(),
            $doc->getClasseur()->getId(),
            $absVisa,
            $ordVisa,
            true,
            $collectivity->getTitreVisa(),
            $collectivity->getCouleurVisa(),
            $path
        );
        /* FIN SetaPDF */

    }

    /**
     * @Route("/org/{orgId}/download_sign/{id}/{absSign}/{ordSign}", defaults={"absSign"=10, "ordSign"=10}, name="download_doc_sign",  options={"expose"=true})
     * @ParamConverter("Document", options={"mapping": {"id": "id"}})
     * @param $orgId
     * @param Document $doc
     * @param int $absSign
     * @param int $ordSign
     * @return JsonResponse|Response
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function downloadSignAction($orgId, Document $doc, $absSign, $ordSign) {

        $em = $this->getDoctrine()->getManager();

        // Verif des autorisations
        if(!$this->authorizeToDownloadDocument($doc->getClasseur()->getVisible(), $this->getUser())) {
            return $this->render('SesileMainBundle:Default:errorrestricted.html.twig');
        }

        // Ecriture de l'hitorique du document
        $id_user = $this->getUser()->getId();
        $user = $em->getRepository('SesileUserBundle:User')->findOneByid($id_user);
        if (!$user || false === $user->hasCollectivity($orgId)) {
            return new JsonResponse(['message' => 'Vous n\'avez pas accès à la collectivité demandé'], Response::HTTP_FORBIDDEN);
        }
        $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($doc, "Téléchargement du document par " . $user->getPrenom() . " " . $user->getNom(), null);
        $doc->setDownloaded(true);
        $em->flush();

        // On recupère la collectivité pour ses paramètres
        $collectivity = $user->getCollectivityById($orgId);

        list($absSign, $ordSign) = $this->defineSignaturePosition($absSign, $ordSign, $collectivity);
        $path = $this->container->getParameter('upload')['fics'];

        // On recupere le dernier utilisateur ayant validé le classeur
        $lastUser = $em->getRepository('SesileUserBundle:EtapeClasseur')->getLastValidant($doc->getClasseur());

        if ($lastUser) {
            /* SetaPDF */
            $imageSignature = $this->container->getParameter('upload')['signatures'] . $lastUser->getPathSignature();

            $em->getRepository('SesileDocumentBundle:Document')->setaPDFTamponSignature($doc->getRepourl(),
                $absSign,
                $ordSign,
                $collectivity->getPageSignature(),
                $imageSignature,
                $lastUser,
                $doc->getClasseur()->getId(),
                $path
            );
            /* FIN SetaPDF */
        }

        return new JsonResponse(array("error" => "pas de validant"));
    }

    /**
     * @Route("/org/{orgId}/download_all/{id}/{absVisa}/{ordVisa}/{absSign}/{ordSign}", name="download_doc_all",  options={"expose"=true})
     * @ParamConverter("Document", options={"mapping": {"id": "id"}})
     * @param $orgId
     * @param Document $doc
     * @param int $absVisa
     * @param int $ordVisa
     * @param int $absSign
     * @param int $ordSign
     *
     * @return JsonResponse
     */
    public function downloadAllAction($orgId, Document $doc, $absVisa = 10, $ordVisa = 10, $absSign = 10, $ordSign = 10) {

        $em = $this->getDoctrine()->getManager();

        // Ecriture de l'hitorique du document
        $user = $this->getUser();
        if (!$user || false === $user->hasCollectivity($orgId)) {
            return new JsonResponse(['message' => 'Vous n\'avez pas accès à la collectivité demandé'], Response::HTTP_FORBIDDEN);
        }
        $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($doc, "Téléchargement du document par " . $user->getPrenom() . " " . $user->getNom(), null);
        $doc->setDownloaded(true);
        $em->flush();
        $collectivity = $user->getCollectivityById($orgId);

        list($absVisa, $ordVisa) = $this->defineVisaPosition($absVisa, $ordVisa, $collectivity);
        list($absSign, $ordSign) = $this->defineSignaturePosition($absSign, $ordSign, $collectivity);
        $path = $this->container->getParameter('upload')['fics'];

        // On recupere le dernier utilisateur ayant validé le classeur
        $lastUser = $em->getRepository('SesileUserBundle:EtapeClasseur')->getLastValidant($doc->getClasseur());
        if ($lastUser) {
            $imageSignature = $this->container->getParameter('upload')['signatures'] . $lastUser->getPathSignature();
            /* SetaPDF */

            $em->getRepository('SesileDocumentBundle:Document')->setaPDFTamponALL(
                $doc->getRepourl(),
                $doc->getClasseur()->getId(),
                $absVisa,
                $ordVisa,
                $absSign,
                $ordSign,
                $collectivity->getPageSignature(),
                true,
                $imageSignature,
                $collectivity->getTitreVisa(),
                $collectivity->getCouleurVisa(),
                $lastUser,
                $path
            );
            /* FIN SetaPDF */
        }

        return new JsonResponse(array("error" => "pas de validant"));
    }


    /**
     * @Route("/{id}/delete", name="delete_document",  options={"expose"=true})
     * @ParamConverter("Document", options={"mapping": {"id": "id"}})
     * @Method("POST")
     * @param Document $doc
     * @return JsonResponse
     */
    public function deleteAction(Document $doc) {

        $em = $this->getDoctrine()->getManager();

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
     * @Route("/org/{orgId}/download_doc_all_files/{id}", name="download_doc_all_files")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param string $orgId
     * @param Classeur $classeur
     * @return Response
     * @throws \SetaPDF_Core_Exception
     */
    public function downloadDocAllFilesAction($orgId, Classeur $classeur)
    {
        return $this->zipFileDownload($orgId, $classeur, "NONE");
    }

    /**
     * @Route("/org/{orgId}/download_doc_visa_all_files/{id}", name="download_doc_visa_all_files")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param string $orgId
     * @param Classeur $classeur
     * @return Response
     * @throws \SetaPDF_Core_Exception
     */
    public function downloadDocVisaAllFilesAction($orgId, Classeur $classeur)
    {
        return $this->zipFileDownload($orgId, $classeur, "VISA");
    }

    /**
     * @Route("/org/{orgId}/download_doc_sign_all_files/{id}", name="download_doc_sign_all_files")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param string $orgId
     * @param Classeur $classeur
     * @return Response
     */
    public function downloadDocSignAllFilesAction($orgId, Classeur $classeur)
    {
        return $this->zipFileDownload($orgId, $classeur, "SIGN");

    }
    /**
     * @Route("/org/{orgId}/download_doc_all_all_files/{id}", name="download_doc_all_all_files")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param string $orgId
     * @param Classeur $classeur
     * @return Response
     */
    public function downloadDocAllAllFilesAction($orgId, Classeur $classeur)
    {
        return $this->zipFileDownload($orgId, $classeur, "ALL");
    }

    /**
     * @param string $orgId
     * @param $classeur
     * @param $type
     * @return Response
     * @throws \SetaPDF_Core_Exception
     */
    private function zipFileDownload ($orgId, $classeur, $type) {

        // Verif des autorisations
        if(!$this->authorizeToDownloadDocument($classeur->getVisible(), $this->getUser())) {
            return $this->render('SesileMainBundle:Default:errorrestricted.html.twig');
        }

        // Ecriture de l'historique du document
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if (!$user || false === $user->hasCollectivity($orgId)) {
            return new JsonResponse(['message' => 'Vous n\'avez pas accès à la collectivité demandé'], Response::HTTP_FORBIDDEN);
        }
        $docs = $classeur->getDocuments();

        $path = $this->container->getParameter('upload')['fics'];

        // On créé le fichier ZIP
        $zip = new \ZipArchive();
        $zipRepoUrl = $path . 'classeur_' . $classeur->getId() . '.zip';

        $em = $this->getDoctrine()->getManager();

        foreach ($docs as $doc) {
            $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($doc, "Téléchargement du document par " . $user->getPrenom() . " " . $user->getNom(), null);
            $doc->setDownloaded(true);
            $em->flush();

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
                    $collectivity = $user->getCollectivityById($orgId);
                    $lastUser = $em->getRepository('SesileUserBundle:EtapeClasseur')->getLastValidant($doc->getClasseur());
                    $imageSignature = $this->container->getParameter('upload')['signatures'] . $lastUser->getPathSignature();


                    /* SetaPDF */
                    // on créé le fichier avec le visa
                    switch ($type) {
                        case "VISA" :
                            $em->getRepository('SesileDocumentBundle:Document')->setaPDFTamponVisaAll(
                                $doc->getRepourl(),
                                $doc->getClasseur()->getId(),
                                $collectivity->getAbscissesVisa(),
                                $collectivity->getOrdonneesVisa(),
                                true,
                                $collectivity->getTitreVisa(),
                                $collectivity->getCouleurVisa(),
                                $path
                            );
                            break;
                        case "SIGN" :
                            $em->getRepository('SesileDocumentBundle:Document')->setaPDFTamponSignatureAll(
                                $doc->getRepourl(),
                                $collectivity->getAbscissesSignature(),
                                $collectivity->getOrdonneesSignature(),
                                $collectivity->getPageSignature(),
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
                                $collectivity->getAbscissesVisa(),
                                $collectivity->getOrdonneesVisa(),
                                $collectivity->getAbscissesSignature(),
                                $collectivity->getOrdonneesSignature(),
                                $collectivity->getPageSignature(),
                                true,
                                $imageSignature,
                                $collectivity->getTitreVisa(),
                                $collectivity->getCouleurVisa(),
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

    /**
     * @param integer      $absVisa
     * @param integer      $ordVisa
     * @param Collectivite $collectivity
     *
     * @return array
     */
    private function defineVisaPosition($absVisa, $ordVisa, Collectivite $collectivity)
    {
        if (($absVisa == 10 || !$ordVisa == 10 ) && ($collectivity->getAbscissesVisa() != '' && $collectivity->getOrdonneesVisa() != '')) {
            $absVisa = $collectivity->getAbscissesVisa();
            $ordVisa = $collectivity->getOrdonneesVisa();
        } elseif (($absVisa == 10 || !$ordVisa == 10 ) && ($collectivity->getAbscissesVisa() == '' || $collectivity->getOrdonneesVisa() == '')) {
            $absVisa = self::DEFAULT_ABS;
            $ordVisa = self::DEFAULT_ORD;
        }

        return [$absVisa, $ordVisa];
    }

    /**
     * @param integer      $absSign
     * @param integer      $ordSign
     * @param Collectivite $collectivity
     *
     * @return array
     */
    private function defineSignaturePosition($absSign, $ordSign, Collectivite $collectivity)
    {
        if (($absSign == 10 || !$ordSign == 10 ) && ($collectivity->getAbscissesSignature() != '' && $collectivity->getOrdonneesSignature() != '')) {
            $absSign = $collectivity->getAbscissesSignature();
            $ordSign = $collectivity->getOrdonneesSignature();
        } elseif (($absSign == 10 || !$ordSign == 10 ) && ($collectivity->getAbscissesSignature() == '' || $collectivity->getOrdonneesSignature() == '')) {
            $absSign = self::DEFAULT_ABS;
            $ordSign = self::DEFAULT_ORD;
        }

        return [$absSign, $ordSign];
    }
}
