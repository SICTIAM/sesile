<?php

namespace Sesile\UserBundle\Controller;

use Sesile\UserBundle\Form\UserPackType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sesile\UserBundle\Entity\UserPack;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Rest\Route("/apirest/user_pack", options = { "expose" = true })
 */
class UserPackApiController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @Rest\View(serializerGroups={"userPack"})
     * @Rest\Get("s/by_collectivite")
     */
    public function listByCollectiviteAction()
    {
        return $this->getDoctrine()->getManager()->getRepository('SesileUserBundle:UserPack')->findByCollectivite($this->getUser()->getCollectivite()->getId());
    }

    /**
     * @return array
     * @Rest\View(serializerGroups={"userPack"})
     * @Rest\Get("s")
     *
     */
    public function listSuperAdminAction()
    {
        return $this->getDoctrine()->getManager()->getRepository('SesileUserBundle:UserPack')->findAll();
    }

    /**
     * @Rest\View(serializerGroups={"userPack"})
     * @Rest\Get("/{id}")
     * @ParamConverter("UserPack", options={"mapping": {"id": "id"}})
     * @param UserPack $userpack
     * @return UserPack
     * @internal param $id
     */
    public function getByIdAction(UserPack $userpack)
    {
        return $userpack;
    }


    /**
     * @Rest\View("statusCode=Response::HTTP_CREATED", serializerGroups={"userPack"})
     * @Rest\Post("")
     * @ParamConverter("UserPack")
     * @param Request $request
     * @return UserPack|\Symfony\Component\Form\Form
     */
    public function postUserpackAction(Request $request)
    {
        $userpack = new UserPack();
        $form = $this->createForm(UserPackType::class, $userpack);
        $form->submit($request->request->all());
        $this->get('logger')->info('Add new group {nom} in collectivite {collectivite}', array( 'nom' => $userpack->getNom(), "collectivite" => $userpack->getCollectivite()));

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($userpack);
            $em->flush();

            return $userpack;
        }
        else {
            return $form;
        }
    }


    /**
     * @Rest\View()
     * @Rest\Delete("/userpacks/apis/{id}")
     * @ParamConverter("UserPack", options={"mapping": {"id": "id"}})
     * @param UserPack $userpack
     * @return UserPack
     * @internal param $id
     */
    public function removeAction(UserPack $userpack)
    {
        if($userpack) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($userpack);
            $em->flush();
        }
    }

    /**
     * @Rest\View(serializerGroups={"userPack"})
     * @Rest\Put("/{id}")
     * @param Request $request
     * @param UserPack $userpack
     * @return UserPack|\Symfony\Component\Form\Form|JsonResponse
     * @ParamConverter("UserPack", options={"mapping": {"id": "id"}})
     */
    public function updateUserpackAction(Request $request, UserPack $userpack)
    {
        if (empty($userpack)) {
            return new JsonResponse(['message' => 'User group not found'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(UserPackType::class, $userpack);
        $form->submit($request->request->all());
        $this->get('logger')->info('Edit group {nom} in collectivite {collectivite}', array( 'nom' => $userpack->getNom(), "collectivite" => $userpack->getCollectivite()));

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->merge($userpack);
            $em->flush();

            return $userpack;
        }
        else {
            return $form;
        }
    }

}
