<?php

namespace Sesile\MainBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sesile\MainBundle\Entity\Aide;
use Sesile\MainBundle\Entity\Patch;
use Sesile\MainBundle\Form\AideType;
use Sesile\MainBundle\Form\PatchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 * @Rest\Route("/apirest/documentation", options = { "expose" = true })
 */
class DocumentationApiController extends Controller
{
    /**
     * @Rest\View()
     * @Rest\Get("/aides")
     * @return array
     */
    public function getAllAidesAction()
    {
        return $this->getDoctrine()
            ->getManager()
            ->getRepository('SesileMainBundle:Aide')
            ->findAll();
    }

    /**
     * @Rest\View()
     * @Rest\Get("/patchs")
     * @return array
     */
    public function getAllPatchAction()
    {
        return $this->getDoctrine()
            ->getManager()
            ->getRepository('SesileMainBundle:Patch')
            ->findAll();
    }

    /**
     * @Rest\View("statusCode=Response::HTTP_CREATED")
     * @Rest\Post("/newAide")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     * @param Request $request
     * @return Aide|\Symfony\Component\Form\Form
     */
    public function postAideAction(Request $request)
    {
        $aide = new Aide();
        $form = $this->createForm(AideType::class, $aide);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($aide);
            $em->flush();

            return $aide;
        }
        else {
            return $form;
        }
    }

    /**
     * @Rest\View("statusCode=Response::HTTP_CREATED")
     * @Rest\Post("/newPatch")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     * @param Request $request
     * @return Patch|\Symfony\Component\Form\Form
     */
    public function postPatchAction(Request $request)
    {
        $patch = new Patch();
        $form = $this->createForm(PatchType::class, $patch);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($patch);
            $em->flush();

            return $patch;
        }
        else {
            return $form;
        }
    }


    /**
     * @Rest\View()
     * @Rest\Put("/aide/{id}")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     * @param Request $request
     * @param Aide $aide
     * @return Aide|\Symfony\Component\Form\Form|JsonResponse
     * @ParamConverter("Aide", options={"mapping": {"id": "id"}})
     */
    public function updateAideAction(Request $request, Aide $aide)
    {
        if (empty($aide)) {
            return new JsonResponse(['message' => 'Aide inexistante'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(AideType::class, $aide);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->merge($aide);
            $em->flush();

            return $aide;
        }
        else {
            return $form;
        }
    }

    /**
     * @Rest\View()
     * @Rest\Put("/patch/{id}")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     * @param Request $request
     * @param Patch $patch
     * @return Patch|\Symfony\Component\Form\Form|JsonResponse
     * @ParamConverter("Aide", options={"mapping": {"id": "id"}})
     */
    public function updatePatchAction(Request $request, Patch $patch)
    {
        if (empty($patch)) {
            return new JsonResponse(['message' => 'Mise à jour inexistante'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(PatchType::class, $patch);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->merge($patch);
            $em->flush();

            return $patch;
        }
        else {
            return $form;
        }
    }

    /**
     * @Rest\View()
     * @Rest\Delete("/aide/{id}")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     * @ParamConverter("Aide", options={"mapping": {"id": "id"}})
     * @param Aide $aide
     * @return Aide
     * @internal param $id
     */
    public function removeAideAction(Aide $aide)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($aide);
        $em->flush();

        return $aide;
    }

    /**
     * @Rest\View()
     * @Rest\Delete("/patch/{id}")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     * @ParamConverter("Patch", options={"mapping": {"id": "id"}})
     * @param Patch $patch
     * @return Patch
     * @internal param $id
     */
    public function removePatchAction(Patch $patch)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($patch);
        $em->flush();

        return $patch;
    }


    /**
     * @Rest\View()
     * @Rest\Post("/aide/document/{id}")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     * @param Request $request
     * @param Aide $aide
     * @return Aide|\Symfony\Component\Form\Form|JsonResponse
     * @ParamConverter("Aide", options={"mapping": {"id": "id"}})
     */
    public function uploadAideDocumentAction(Request $request, Aide $aide) {

        if (empty($aide)) {
            return new JsonResponse(['message' => 'Document inexistant'], Response::HTTP_NOT_FOUND);
        }

        $em = $this->getDoctrine()->getManager();
        $aide = $em->getRepository('SesileMainBundle:Aide')->uploadFile(
            $request->files->get('path'),
            $aide,
            $this->getParameter('upload')['fics']
        );

        $em->persist($aide);
        $em->flush();

        return $aide;
    }

    /**
     * @Rest\View()
     * @Rest\Post("/patch/document/{id}")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     * @param Request $request
     * @param Patch $patch
     * @ParamConverter("Aide", options={"mapping": {"id": "id"}})
     * @return Patch|JsonResponse
     */
    public function uploadPatchDocumentAction(Request $request, Patch $patch) {

        if (empty($patch)) {
            return new JsonResponse(['message' => 'Document inexistant'], Response::HTTP_NOT_FOUND);
        }

        $em = $this->getDoctrine()->getManager();
        $patch = $em->getRepository('SesileMainBundle:Patch')->uploadFile(
            $request->files->get('path'),
            $patch,
            $this->getParameter('upload')['fics']
        );

        $em->persist($patch);
        $em->flush();

        return $patch;
    }


    /**
     * @Rest\View()
     * @Rest\Delete("/aide/document/{id}")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     * @ParamConverter("Aide", options={"mapping": {"id": "id"}})
     * @param Aide $aide
     * @return Aide
     * @internal param $id
     */
    public function deleteAideDocumentAction(Aide $aide)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('SesileMainBundle:Aide')->removeUpload($this->getParameter('upload')['fics'] . $aide->getPath());
        $aide->setPath("");
        $em->flush();

        return $aide;
    }

    /**
     * @Rest\View()
     * @Rest\Delete("/patch/document/{id}")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     * @ParamConverter("Path", options={"mapping": {"id": "id"}})
     * @param Patch $patch
     * @return Patch
     * @internal param $id
     */
    public function deletePatchDocumentAction(Patch $patch)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('SesileMainBundle:Aide')->removeUpload($this->getParameter('upload')['fics'] . $patch->getPath());
        $patch->setPath("");
        $em->flush();

        return $patch;
    }

}
