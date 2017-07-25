<?php

namespace Sesile\UserBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sesile\UserBundle\Entity\User;
use Sesile\UserBundle\Form\UserType;
use Symfony\Component\HttpFoundation\Response;


class UserApiController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @return array
     * @Rest\View("/users/apis/list")
     * @Method("get")
     *
     */
    public function listAction()
    {
        return $this->getDoctrine()->getManager()->getRepository('SesileUserBundle:User')->findByCollectivite($this->get('session')->get("collectivite"));
    }

    /**
     * @return array
     * @Rest\View("/users/apis/listadmin")
     * @Method("get")
     *
     */
    public function listadminAction()
    {
        return $this->getDoctrine()->getManager()->getRepository('SesileUserBundle:User')->findAll();
    }

    /**
     * @Rest\View()
     * @Rest\Get("/users/apis/{id}")
     * @ParamConverter("User", options={"mapping": {"id": "id"}})
     * @param User $user
     * @return User
     * @internal param $id
     */
    public function getAction(User $user)
    {
        return $user;
    }


    /**
     * @Rest\View("statusCode=Response::HTTP_CREATED")
     * @Rest\Post("/users/apis/new")
     * @param Request $request
     * @return User|\Symfony\Component\Form\Form
     */
    public function postUserAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $user;
        }
        else {
            return $form;
        }
    }


    /**
     * @Rest\View()
     * @Rest\Delete("/users/apis/{id}")
     * @ParamConverter("User", options={"mapping": {"id": "id"}})
     * @param User $user
     * @return User
     * @internal param $id
     */
    public function removeAction(User $user)
    {
        if($user) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
        }
    }

    /**
     * @Rest\View()
     * @Rest\Put("/users/apis/{id}")
     * @param Request $request
     * @param User $user
     * @return User|\Symfony\Component\Form\Form|JsonResponse
     * @ParamConverter("User", options={"mapping": {"id": "id"}})
     */
    public function updateUserAction(Request $request, User $user)
    {
        if (empty($user)) {
            return new JsonResponse(['message' => 'Utilisateur inexistant'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(UserType::class, $user);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->merge($user);
            $em->flush();

            return $user;
        }
        else {
            return $form;
        }
    }

}
