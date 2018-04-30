<?php

namespace Sesile\MainBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\MainBundle\Form\CollectiviteType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 * @Rest\Route("/apirest/collectivite", options = { "expose" = true })
 */
class CollectiviteApiController extends Controller
{
    /**
     * @Rest\View(serializerGroups={"getAllCollectivite"})
     * @Rest\Get("s")
     * @return array
     */
    public function getAllAction()
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {

            return $this->getDoctrine()
                ->getManager()
                ->getRepository('SesileMainBundle:Collectivite')
                ->findAll();
        } else {
            return array($this->getUser()->getCollectivite());
        }

    }

    /**
     * @Security("is_granted('IS_AUTHENTICATED_ANONYMOUSLY')")
     * @Rest\View(serializerGroups={"getAllCollectivite"})
     * @Rest\Get("/list")
     * @return JsonResponse
     */
    public function getOrganisationList()
    {
//        $data = $this->getDoctrine()->getRepository(Collectivite::class)->getCollectivitesList();
        return new JsonResponse($this->get('collectivite.manager')->getCollectivitesList(), Response::HTTP_OK);
    }

    /**
     * @Rest\View(serializerGroups={"getCollectiviteById"})
     * @Rest\Get("/{id}")
     * @ParamConverter("Collectivite", options={"mapping": {"id": "id"}})
     * @return Collectivite
     */
    public function getByIdAction(Collectivite $collectivite) {
        return $collectivite;
    }

    /**
     * @Rest\View()
     * @Rest\Post("/avatar/{id}")
     * @ParamConverter("Collectivite", options={"mapping": {"id": "id"}})
     * @param Request $request
     * @param Collectivite $collectivite
     * @return Collectivite|\Symfony\Component\Form\Form|JsonResponse
     */
    public function uploadAvatarAction(Request $request, Collectivite $collectivite) {
        return $this->uploadOrDeleteAvatar($request, $collectivite);
    }

    /**
     * @Rest\View()
     * @Rest\Delete("/avatar/{id}")
     * @param Request $request
     * @param Collectivite $collectivite
     * @return Collectivite|\Symfony\Component\Form\Form|JsonResponse
     */
    public function deleteAvatarAction(Request $request, Collectivite $collectivite) {
        return $this->uploadOrDeleteAvatar($request, $collectivite);
    }

    /**
     * @Rest\View()
     * @Rest\Patch("/{id}")
     * @param Request $request
     * @param Collectivite $collectivite
     * @ParamConverter("Collectivite", options={"mapping": {"id": "id"}})
     * @return Collectivite|\Symfony\Component\Form\Form|JsonResponse
     */
    public function updateCollectiviteAction(Request $request, Collectivite $collectivite) {
        return $this->updateOrCreateCollectivite($request,$collectivite,false);
    }

    private function updateOrCreateCollectivite(Request $request, Collectivite $collectivite, $clearMissing) {
        if (empty($collectivite)) {
            return new JsonResponse(['message' => 'Collectivité inexistante'], Response::HTTP_NOT_FOUND);
        }

        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') ||
            $this->getUser()->getCollectivite() == $collectivite) {

            $form = $this->createForm(CollectiviteType::class, $collectivite);
            $form->submit($request->request->all(), $clearMissing);

            if ($form->handleRequest($request)->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->flush();

                return $collectivite;
            }
            else {
                return $form;
            }
        } else {
            return new JsonResponse(['message' => "Denied Access"], Response::HTTP_FORBIDDEN);
        }
    }

    private function uploadOrDeleteAvatar(Request $request, Collectivite $collectivite) {
        if (empty($collectivite)) {
            return new JsonResponse(['message' => 'Collectivité inexistante'], Response::HTTP_NOT_FOUND);
        }

        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') ||
            $this->getUser()->getCollectivite() == $collectivite) {

            $em = $this->getDoctrine()->getManager();
            if($request->isMethod('POST')) {
                $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->uploadImage(
                    $request->files->get('image'),
                    $collectivite,
                    $this->getParameter('upload')['logo_coll']
                );
            } else if ($request->isMethod('DELETE')) {
                $collectivite->removeUpload($this->getParameter('upload')['logo_coll'] . $collectivite->getImage());
            }

            $em->persist($collectivite);
            $em->flush();

            return $collectivite;
        } else {
            return new JsonResponse(['message' => "Denied Access"], Response::HTTP_FORBIDDEN);
        }
    }
}
