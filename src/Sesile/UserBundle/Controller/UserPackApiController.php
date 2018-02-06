<?php

namespace Sesile\UserBundle\Controller;

use Sesile\MainBundle\Entity\Collectivite;
use Sesile\UserBundle\Form\UserPackType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sesile\UserBundle\Entity\UserPack;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Security("has_role('ROLE_SUPER_ADMIN') or has_role('ROLE_ADMIN')")
 * @Rest\Route("/apirest/user_pack", options = { "expose" = true })
 */
class UserPackApiController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @Rest\View(serializerGroups={"userPack"})
     * @Rest\Get("s/{collectiviteId}")
     * @ParamConverter("collectivite", options={"mapping": {"collectiviteId": "id"}})
     * @param Collectivite $collectivite
     * @return \Doctrine\Common\Collections\Collection|UserPack
     */
    public function getByCollectiviteAction(Collectivite $collectivite)
    {
        $this->get('logger')->info('Get group by collectivite {id}', array('id' => $collectivite->getId()));
//        return $this->getDoctrine()->getManager()->getRepository('SesileUserBundle:UserPack')->findByCollectivite($collectivite->getId());
        return $collectivite->getUserPacks();
    }

    /**
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @return array
     * @Rest\View(serializerGroups={"userPack"})
     * @Rest\Get("s")
     */
    public function getAllAction()
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
     * @Rest\Delete("/{id}/{collectiviteId}")
     * @ParamConverter("UserPack", options={"mapping": {"id": "id"}})
     * @param UserPack $userpack
     * @param $collectiviteId
     */
    public function removeAction(UserPack $userpack, $collectiviteId)
    {
        $this->get('logger')->info('Remove group {name}', array('name' => $userpack->getNom()));
        if($userpack) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($userpack);
            $em->flush();
        }
        return $this->getDoctrine()->getManager()->getRepository('SesileUserBundle:UserPack')->findByCollectivite($collectiviteId);
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
