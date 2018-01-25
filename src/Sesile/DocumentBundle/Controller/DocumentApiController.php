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
     */
    public function onlyOfficeAction (Request $request, Document $document) {
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

}
