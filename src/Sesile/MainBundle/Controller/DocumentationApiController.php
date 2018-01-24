<?php

namespace Sesile\MainBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Filesystem\Filesystem;
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
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


/**
 * @Security("is_granted('ROLE_SUPER_ADMIN')")
 * @Rest\Route("/apirest/documentation", options = { "expose" = true })
 */
class DocumentationApiController extends Controller {
    /**
     * @Rest\View()
     * @Rest\Get("/aides")
     * @return array
     */
    public function getAllAideAction()
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
     * @Rest\View()
     * @Rest\Get("/patchs/{id}")
     * @ParamConverter("Patch", options={"mapping": {"id": "id"}})
     * @return Patch
     */
    public function showPatchAction(Patch $patch) {
        $patch->setFile(array('name' => $patch->getPath()));
        return $patch;
    }

    /**
     * @Rest\View()
     * @Rest\Get("/aides/{id}")
     * @ParamConverter("Aide", options={"mapping": {"id": "id"}})
     * @return Aide
     */
    public function showAideAction(Aide $aide) {
        $aide->setFile(array('name' => $aide->getPath()));
        return $aide;
    }

    /**
     * @Rest\View("statusCode=Response::HTTP_CREATED")
     * @Rest\Post("/newAide")
     * @param Request $request
     * @return Aide|JsonResponse
     */
    public function postAideAction(Request $request)
    {
        $aide = new Aide();
        $form = $this->createForm(AideType::class, $aide);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $aide =
                $this->uploadFile(
                    $request->files->get('file'),
                    $aide);

            $em->persist($aide);
            $em->flush();

            return $aide;
        }
        else {
            return new JsonResponse($form->getErrors(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Rest\View("statusCode=Response::HTTP_CREATED")
     * @Rest\Post("/newPatch")
     * @param Request $request
     * @return JsonResponse|Patch
     */
    public function postPatchAction(Request $request)
    {
        $patch = new Patch();
        $form = $this->createForm(PatchType::class, $patch);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $patch =
                $this->uploadFile(
                    $request->files->get('file'),
                    $patch);
            $em->persist($patch);
            $em->flush();
            return $patch;
        } else {
            return new JsonResponse($form->getErrors(), Response::HTTP_BAD_REQUEST);
        }
    }


    /**
     * @Rest\View()
     * @Rest\Post("/aides/{id}")
     * @ParamConverter("Aide", options={"mapping": {"id": "id"}})
     * @param Request $request
     * @param Aide $aide
     * @return Aide|\Symfony\Component\Form\Form|JsonResponse
     */
    public function updateAideAction(Request $request, Aide $aide)
    {
        if (empty($aide)) {
            return new JsonResponse('', Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(AideType::class, $aide);
        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $aide =
                $this->uploadFile(
                    $request->files->get('file'),
                    $aide);
            $em->merge($aide);
            $em->flush();

            return $aide;
        }
        else {
            return new JsonResponse($form->getErrors(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Rest\View()
     * @Rest\Post("/patch/{id}")
     * @param Request $request
     * @param Patch $patch
     * @return Patch|\Symfony\Component\Form\Form|JsonResponse
     * @ParamConverter("Aide", options={"mapping": {"id": "id"}})
     */
    public function updatePatchAction(Request $request, Patch $patch)
    {
        if (empty($patch)) {
            return new JsonResponse('', Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(PatchType::class, $patch);
        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $patch =
                $this->uploadFile(
                    $request->files->get('file'),
                    $patch);
            $em->merge($patch);
            $em->flush();

            return $patch;
        }
        else {
            return new JsonResponse($form->getErrors(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Rest\View()
     * @Rest\Delete("/aide/{id}")
     * @ParamConverter("Aide", options={"mapping": {"id": "id"}})
     * @param Aide $aide
     * @return array|Aide[]|JsonResponse
     * @internal param $id
     */
    public function removeAideAction(Aide $aide) {
        if(empty($aide)) return new JsonResponse('',JsonResponse::HTTP_NOT_FOUND);
        $uploadPath = $this->getParameter('upload')['fics'];
        $em = $this->getDoctrine()->getManager();
        try {
            $this->removeDocument($uploadPath. $aide->getPath());
            $em->remove($aide);
            $em->flush();
            $aides = $this->getDoctrine()
                ->getManager()
                ->getRepository('SesileMainBundle:Aide')
                ->findAll();
            return $aides;
        } catch (IOExceptionInterface $e) {
            return new JsonResponse('',JsonResponse::HTTP_NOT_FOUND);
        }
    }

    /**
     * @Rest\View()
     * @Rest\Delete("/patch/{id}")
     * @ParamConverter("Patch", options={"mapping": {"id": "id"}})
     * @param Patch $patch
     * @return array|Patch[]|JsonResponse
     * @internal param $id
     */
    public function removePatchAction(Patch $patch) {
        if(empty($patch)) return new JsonResponse('',JsonResponse::HTTP_NOT_FOUND);
        $uploadPath = $this->getParameter('upload')['fics'];
        $em = $this->getDoctrine()->getManager();
        try {
            $this->removeDocument($uploadPath. $patch->getPath());
            $em->remove($patch);
            $em->flush();
            $patchs = $this->getDoctrine()
                ->getManager()
                ->getRepository('SesileMainBundle:Patch')
                ->findAll();
            return $patchs;
        } catch (IOExceptionInterface $e) {
            return new JsonResponse('',JsonResponse::HTTP_NOT_FOUND);
        }
    }

    /**
     * @Rest\Get("/download/patch/{id}")
     * @ParamConverter("Patch", options={"mapping": {"id": "id"}})
     * @param Patch $patch
     * @return Response
     */
    public function showDocumentPatchAction(Patch $patch)
    {
        $path = $this->container->getParameter('upload')['fics'] . $patch->getPath();

        return $this->file($path, $patch->getDescription(), ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * @Rest\Get("/download/aide/{id}")
     * @ParamConverter("Aide", options={"mapping": {"id": "id"}})
     * @param Aide $aide
     * @return Response
     */
    public function showDocumentAideAction(Aide $aide)
    {
        $path = $this->container->getParameter('upload')['fics'] . $aide->getPath();

        return $this->file($path, $aide->getDescription(), ResponseHeaderBag::DISPOSITION_INLINE);
    }

    protected function removeDocument($path) {
        $fs = new Filesystem();
        try {
            if(is_file($path)) $fs->remove($path);
        } catch (IOExceptionInterface $exception) {
            throw new IOExceptionInterface($exception);
        }
    }

    protected function uploadFile($file, $aideOrPatch) {
        $uploadPath = $this->getParameter('upload')['fics'];
        if($file) {
            if(!empty($aideOrPatch->getPath())) {
                $this->removeDocument($uploadPath. $aideOrPatch->getPath());
            }
            $fileName = sha1(uniqid(mt_rand(), true)) . '.' . $file->guessExtension();
            $aideOrPatch->setPath($fileName);
            $file->move($uploadPath, $fileName);
        }
        return $aideOrPatch;
    }

}
