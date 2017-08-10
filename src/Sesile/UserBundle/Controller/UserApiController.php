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
     * @Rest\View()
     * @Rest\Get("isauthenticated")
     */
    public function isauthenticatedAction() {
        return $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY');
    }

    /**
     * @return array
     * @Rest\View()
     * @Rest\Get("/list")
     */
    public function listAction()
    {
        return $this->getDoctrine()->getManager()->getRepository('SesileUserBundle:User')->findByCollectivite($this->get('session')->get("collectivite"));
    }

    /**
     * @return array
     * @Rest\View()
     * @Rest\Get("/listadmin")
     */
    public function listadminAction()
    {
        return $this->getDoctrine()->getManager()->getRepository('SesileUserBundle:User')->findAll();
    }

    /**
     * @Rest\View()
     * @Rest\Get("/{id}")
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
     * @Rest\Post("/new")
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
     * @Rest\Delete("/{id}")
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
     * @Rest\Put("/{id}")
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
