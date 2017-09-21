<?php

namespace Sesile\UserBundle\Controller;

use Sesile\UserBundle\Form\UserRoleType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sesile\UserBundle\Entity\UserRole;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Rest\Route("/apirest/user_role", options = { "expose" = true })
 * @Security("has_role('ROLE_SUPER_ADMIN') or has_role('ROLE_ADMIN')")
 */
class UserRoleApiController extends FOSRestController implements ClassResourceInterface
{

    /**
     * @return array
     * @Rest\View(serializerGroups={"userRole"})
     * @Rest\Get("s")
     *
     */
    public function getAllAction()
    {
        return $this->getDoctrine()->getManager()->getRepository('SesileUserBundle:UserRole')->findAll();
    }

    /**
     * @Rest\View(serializerGroups={"userRole"})
     * @Rest\Get("/{id}")
     * @ParamConverter("UserRole", options={"mapping": {"id": "id"}})
     * @param UserRole $userrole
     * @return UserRole
     * @internal param $id
     */
    public function getByIdAction(UserRole $userrole)
    {
        return $userrole;
    }


    /**
     * @Rest\View("statusCode=Response::HTTP_CREATED", serializerGroups={"userRole"})
     * @Rest\Post("")
     * @ParamConverter("UserRole")
     * @param Request $request
     * @return UserRole|\Symfony\Component\Form\Form
     */
    public function postUserroleAction(Request $request)
    {
        $userrole = new UserRole();
        $form = $this->createForm(UserRoleType::class, $userrole);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($userrole);
            $em->flush();

            return $userrole;
        }
        else {
            return $form;
        }
    }


    /**
     * @Rest\View()
     * @Rest\Delete("/{id}")
     * @ParamConverter("UserRole", options={"mapping": {"id": "id"}})
     * @param UserRole $userrole
     */
    public function removeAction(UserRole $userrole)
    {
        if($userrole) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($userrole);
            $em->flush();
        }
    }

    /**
     * @Rest\View(serializerGroups={"userRole"})
     * @Rest\Put("/{id}")
     * @param Request $request
     * @param UserRole $userrole
     * @return UserRole|\Symfony\Component\Form\Form|JsonResponse
     * @ParamConverter("UserRole", options={"mapping": {"id": "id"}})
     */
    public function updateUserroleAction(Request $request, UserRole $userrole)
    {
        if (empty($userrole)) {
            return new JsonResponse(['message' => 'User group not found'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(UserRoleType::class, $userrole);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->merge($userrole);
            $em->flush();

            return $userrole;
        }
        else {
            return $form;
        }
    }

}
