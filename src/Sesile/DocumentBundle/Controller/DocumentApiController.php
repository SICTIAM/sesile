<?php

namespace Sesile\DocumentBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\DocumentBundle\Entity\Document;
use Sesile\DocumentBundle\Entity\DocumentDetachedSign;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sesile\DocumentBundle\Classe\PES;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 * @Rest\Route("/apirest/document", options = { "expose" = true })
 */
class DocumentApiController extends FOSRestController implements ClassResourceInterface
{

    /**
     * @Rest\View(serializerGroups={"classeurById"})
     * @Rest\Get("/{id}")
     * @param Document $document
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @return Document
     */
    public function getAction(Document $document)
    {
        return $document;
    }
    /**
     * @Rest\View(serializerGroups={"classeurById"})
     * @Rest\Get("/{id}/preview")
     * @param Document $document
     * @return Document
     */
    public function getPdfPreviewAction(Document $document)
    {
        //@todo
//        return false;
        $path = $this->container->getParameter('upload')['fics'];
        return $document->getPDFImage(0, "PORTRAIT", $path);
    }

    /**
     * @Rest\View(serializerGroups={"classeurById"})
     * @Rest\Get("s/classeur/{id}")
     * @param Classeur $classeur
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getByClasseurAction(Classeur $classeur)
    {
        foreach ($classeur->getDocuments() as $document) {
            $documentPath = $this->getParameter('upload')['fics'] . $document->getRepourl();
            $fileSize = 0;
            if(file_exists($documentPath)) {
                $fileSize = filesize($documentPath);
            }
            $document->setSize($fileSize);
        }
        return $classeur->getDocuments();
    }

    /**
     * @Rest\View(serializerGroups={"classeurById"})
     * @Rest\Post("/only-office/{id}")
     * @param Request $request
     * @param Document $document
     * @ParamConverter("Document", options={"mapping": {"id": "id"}})
     * @Security("is_granted('IS_AUTHENTICATED_ANONYMOUSLY')")
     * @return mixed
     */
    public function onlyOfficeAction (Request $request, Document $document) {

        $data = [];
        if ($content = $request->getContent()) {
            $data = json_decode($content, true);
        }


        if ($data["status"] === 2 || $data["status"] === 6){
            $downloadUri = $data["url"];

            if (($new_data = file_get_contents($downloadUri))===FALSE){
                $error['error'] = "Bad Response";
            } else {
                $path_for_save = $this->getParameter('upload')['fics'] . $document->getRepourl();
                file_put_contents($path_for_save, $new_data, LOCK_EX);
            }
        }
        $error['error'] = 0;

        return $error;
    }

    /**
     * @Rest\View("statusCode=Response::HTTP_CREATED", serializerGroups={"classeurById"})
     * @Rest\Post("/classeur/{id}")
     * @param Request $request
     * @param Classeur $classeur
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @return \Doctrine\Common\Collections\Collection
     */
    public function uploadAction(Request $request, Classeur $classeur) {

        $em = $this->getDoctrine()->getManager();

        foreach ($request->files as $documents) {

            $em->getRepository('SesileDocumentBundle:Document')->uploadDocuments(
                $documents,
                $classeur,
                $this->getParameter('upload')['fics'],
                $this->getUser()
            );
        }

        foreach ($classeur->getDocuments() as $document) {
            $documentPath = $this->getParameter('upload')['fics'] . $document->getRepourl();
            $fileSize = 0;
            if(file_exists($documentPath)) {
                $fileSize = filesize($documentPath);
            }
            $document->setSize($fileSize);
        }

        return $classeur->getDocuments();
    }


    /**
     * @Rest\View()
     * @Rest\Delete("/{id}")
     * @ParamConverter("Document", options={"mapping": {"id": "id"}})
     * @param Document $document
     * @internal param Document $document
     * @internal param $id
     * @return bool
     */
    public function remove(Document $document) {

        $em = $this->getDoctrine()->getManager();

        if ($em->getRepository('SesileDocumentBundle:Document')->removeDocument($this->getParameter('upload')['fics'] . $document->getRepourl()) ) {

            $em->getRepository('SesileClasseurBundle:Action')->addDocumentAction($document->getClasseur(), "Suppression du document " . $document->getName(), "", "", $this->getUser());

            $em->remove($document);
            $em->flush();

            return true;

        } else {
            return false;
        }

    }


    /**
     * @Rest\View()
     * @Rest\Get("/helios/{id}")
     * @ParamConverter("Document", options={"mapping": {"id": "id"}})
     * @param Document $document
     * @return PES|JsonResponse
     * @internal param Document $document
     * @internal param $id
     */
    public function heliosAction(Document $document)
    {
        $user = $this->getUser();
        $classeur = $document->getClasseur();
        $em = $this->getDoctrine()->getManager();

        if (!in_array($classeur, $user->getClasseurs()->toArray()) && !$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            return new JsonResponse(['message' => 'Vous n\'avez pas accès à ce classeur'], Response::HTTP_FORBIDDEN);
        }

        $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($document, "Visualisation du document par " . $user->getPrenom() . " " . $user->getNom(), null);
        $path = $this->container->getParameter('upload')['fics'] . $document->getRepourl();

        if (is_file($path)) {
            // on enleve tout les putains de préfixes de mes 2
            $str = str_ireplace('ns3:', '', str_ireplace('xad:', '', str_ireplace('ds:', '', file_get_contents($path))));
            $xml = simplexml_load_string($str, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE);

            $xml = new PES($xml);
            return $xml;
        }

        return new JsonResponse(['message' => 'Impossible de récupérer le document'], Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Get("/getpjie/{id}/{pejid}/{pejname}")
     * @ParamConverter("Document", options={"mapping": {"id": "id"}})
     * @param Document $document
     * @return bool|Response
     */
    public function getPJAction(Document $document, $pejid, $pejname)
    {
        $path = $this->container->getParameter('upload')['fics'] . $document->getRepourl();
        $str = str_ireplace('ns3:', '', str_ireplace('xad:', '', str_ireplace('ds:', '', file_get_contents($path))));
        $xml = simplexml_load_string($str, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE);

        $extension = explode('.', $pejname);
        $PJextension = strtolower(end($extension));

        if (isset($xml->PES_PJ)) {
            foreach ($xml->PES_PJ->PJ as $pj) {
                if ((string)$pj->IdUnique->attributes()[0] == $pejid) {
                    $PJ = gzdecode(base64_decode((string)$pj->Contenu->Fichier));

                    $response = new Response();
                    if ($PJextension == "pdf") {
                        $response->headers->set('Content-Type', 'application/pdf');
                    } else if ($PJextension == "zip") {
                        $response->headers->set('Content-Type', 'application/zip');
                    } else if ($PJextension == "xhl") {
                        $response->headers->set('Content-Type', 'application/xhl');
                    } else {
                        $response->headers->set('Content-Type', 'application/zip');
                    }
                    $response->headers->set('Content-disposition', 'inline;filename=' . $pejname);
                    $response->setContent($PJ);
                    return $response;
                }
            }
        }

        return false;
    }

    /**
     * @Security("is_granted('IS_AUTHENTICATED_ANONYMOUSLY')")
     * @Route("/downloadJWS/{name}/{token}", name="download_jws_doc")
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
     * @Security("is_granted('IS_AUTHENTICATED_ANONYMOUSLY')")
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
//            $nouveauNom = $path_parts['filename'] . '-sign.' . $path_parts['extension'];
            $nouveauNom = $path_parts['filename'] . '-sign.' . $uploadedfile->guessExtension();

//            $typeDocument = $doc->getType();

            // Si le document renvoyé est signature détachée
            // Dans le cas d un CADES
//            if ($typeDocument != "application/xml" && $typeDocument != "application/pdf") {
//
//                $dateToday = new \DateTime();
//
//                $docSignNom = $path_parts['filename'] . '-sign';
//                $path_doc = pathinfo($doc->getRepourl());
//                $documentSignedURL = $path_doc['filename'] . '-sign-' . $dateToday->format('YmdHis');
//                // Upload du nouveau fichier
//                $uploadedfile->move($path, $documentSignedURL);
//                $documentSign = new DocumentDetachedSign();
//                $documentSign->setName($docSignNom);
//                $documentSign->setRepourl($documentSignedURL);
//                $documentSign->setDocument($doc);
//                $em->persist($documentSign);
//
//            }
//            // Dans les autres cas : pades, xades, xades-pes
//            else {
                unlink($path . $doc->getRepourl());
                // Upload du nouveau fichier
                $uploadedfile->move($path, $doc->getRepourl());
                // On enregistre le nouveau nom
                $doc->setName($nouveauNom);
//            }

            // On valide la singature
            $doc->setSigned(true);

            // On enregistre les données
            $em->flush();
            return new JsonResponse(array("error" => "ok", "url" => $path . $doc->getRepourl()));

        } else {
            unlink($uploadedfile->getRealPath());

            return new JsonResponse(array("error" => "nodocumentwiththisname"));

        }

    }

}
