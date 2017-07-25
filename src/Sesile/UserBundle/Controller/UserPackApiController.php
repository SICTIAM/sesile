<?php

namespace Sesile\UserBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sesile\UserBundle\Entity\UserPack;
use Sesile\UserBundle\Form\UserType;
use Symfony\Component\HttpFoundation\Response;


class UserPackApiController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @return array
     * @Rest\View("/userpacks/apis/list")
     * @Method("get")
     *
     */
    public function listAction()
    {
        return $this->getDoctrine()->getManager()->getRepository('SesileUserBundle:UserPack')->findByCollectivite($this->get('session')->get("collectivite"));
    }

    /**
     * @return array
     * @Rest\View("/userpacks/apis/listadmin")
     * @Method("get")
     *
     */
    public function listadminAction()
    {
        return $this->getDoctrine()->getManager()->getRepository('SesileUserBundle:UserPack')->findAll();
    }

    /**
     * @Rest\View()
     * @Rest\Get("/userpacks/apis/{id}")
     * @ParamConverter("UserPack", options={"mapping": {"id": "id"}})
     * @param UserPack $userpack
     * @return UserPack
     * @internal param $id
     */
    public function getAction(UserPack $userpack)
    {
        return $userpack;
    }


    /**
     * @Rest\View("statusCode=Response::HTTP_CREATED")
     * @Rest\Post("/userpacks/apis/new")
     * @param Request $request
     * @return UserPack|\Symfony\Component\Form\Form
     */
    public function postUserpackAction(Request $request)
    {
        $userpack = new UserPack();
        $form = $this->createForm(UserPackType::class, $userpack);

        $form->submit($request->request->all());

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
     * @Rest\View()
     * @Rest\Put("/userpacks/apis/{id}")
     * @param Request $request
     * @param User $user
     * @return User|\Symfony\Component\Form\Form|JsonResponse
     * @ParamConverter("User", options={"mapping": {"id": "id"}})
     */
    public function updateUserpackAction(Request $request, UserPack $userpack)
    {
        if (empty($userpack)) {
            return new JsonResponse(['message' => 'Utilisateur inexistant'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(UserPackType::class, $userpack);
        $form->submit($request->request->all());

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
