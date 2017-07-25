<?php

namespace Sesile\ClasseurBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
//use FOS\RestBundle\Controller\Annotations\RequestParam;
//use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use FOS\RestBundle\View\RouteRedirectView;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sesile\ClasseurBundle\Entity\TypeClasseur;
use Sesile\ClasseurBundle\Form\TypeClasseurType;
use Symfony\Component\HttpFoundation\Response;


class TypeClasseurApiController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @return array
     * @Rest\View("/typeclasseurs/apis/list")
     * @Method("get")
     *
     */
    public function listAction()
    {
        $typeClasseurs = $this->getDoctrine()->getManager()->getRepository('SesileClasseurBundle:TypeClasseur')->findAll();

        return $typeClasseurs;
    }

    /**
     * @Rest\View()
     * @Rest\Get("/typeclasseurs/apis/{id}")
     * @ParamConverter("TypeClasseur", options={"mapping": {"id": "id"}})
     * @param TypeClasseur $typeClasseur
     * @return TypeClasseur
     * @internal param $id
     */
    public function getAction(TypeClasseur $typeClasseur)
    {
        return $typeClasseur;
    }


    /**
     * @Rest\View("statusCode=Response::HTTP_CREATED")
     * @Rest\Post("/typeclasseurs/apis/new")
     * @param Request $request
     * @return TypeClasseur|\Symfony\Component\Form\Form
     */
    public function postTypeClasseurAction(Request $request)
    {
        $typeClasseur = new TypeClasseur();
        $form = $this->createForm(TypeClasseurType::class, $typeClasseur);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($typeClasseur);
            $em->flush();

            return $typeClasseur;
        }
        else {
            return $form;
        }
    }


    /**
     * @Rest\View()
     * @Rest\Delete("/typeclasseurs/apis/{id}")
     * @ParamConverter("TypeClasseur", options={"mapping": {"id": "id"}})
     * @param TypeClasseur $typeClasseur
     * @return TypeClasseur
     * @internal param $id
     */
    public function removeAction(TypeClasseur $typeClasseur)
    {
        if($typeClasseur) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($typeClasseur);
            $em->flush();
        }
    }

    /**
     * @Rest\View()
     * @Rest\Put("/typeclasseurs/apis/{id}")
     * @param Request $request
     * @param TypeClasseur $typeClasseur
     * @return TypeClasseur|\Symfony\Component\Form\Form|JsonResponse
     * @ParamConverter("TypeClasseur", options={"mapping": {"id": "id"}})
     */
    public function updateTypeClasseurAction(Request $request, TypeClasseur $typeClasseur)
    {
        if (empty($typeClasseur)) {
            return new JsonResponse(['message' => 'type de classeur inexistant'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(TypeClasseurType::class, $typeClasseur);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->merge($typeClasseur);
            $em->flush();

            return $typeClasseur;
        }
        else {
            return $form;
        }
    }

}
