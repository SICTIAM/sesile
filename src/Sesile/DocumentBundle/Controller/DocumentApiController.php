<?php

namespace Sesile\DocumentBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\DocumentBundle\Entity\Document;
use Symfony\Component\HttpFoundation\Request;
use Sesile\DocumentBundle\Classe\PES;
use Symfony\Component\HttpFoundation\Response;


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
     * @Rest\Get("s/classeur/{id}")
     * @param Classeur $classeur
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getByClasseurAction(Classeur $classeur)
    {
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
     * @internal param Document $document
     * @internal param $id
     * @return array
     */
    public function heliosAction(Document $document)
    {
        $user = $this->getUser();
        $classeur = $document->getClasseur();
        $em = $this->getDoctrine()->getManager();

        if (!in_array($classeur, $user->getClasseurs()->toArray()) && !$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $this->get('session')->getFlashBag()->add(
                'error',
                "Vous n'avez pas accès à ce classeur"
            );
            return false;
        }

        $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($document, "Visualisation du document par " . $user->getPrenom() . " " . $user->getNom(), null);
        $path = $this->container->getParameter('upload')['fics'] . $document->getRepourl();

        if (is_file($path)) {
            // on enleve tout les putains de préfixes de mes 2
            $str = str_ireplace('ns3:', '', str_ireplace('xad:', '', str_ireplace('ds:', '', file_get_contents($path))));
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
            if(!isset($xml->PES_RecetteAller) && !isset($xml->PES_DepenseAller)) {
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

            return array(
                'budget' => (string)$PES->budget,
                'signatory' => utf8_decode($PES->signataire),
                'dateSign' => $PES->dateSign,
                'vouchers' => $PES->listBord
            );
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

}
