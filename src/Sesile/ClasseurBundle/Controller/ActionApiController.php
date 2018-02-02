<?php

namespace Sesile\ClasseurBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sesile\ClasseurBundle\Entity\Action;
use Sesile\ClasseurBundle\Entity\Classeur as Classeur;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sesile\ClasseurBundle\Form\ActionType;
use Sesile\ClasseurBundle\Form\ClasseurType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Rest\Route("/apirest/action", options = { "expose" = true })
 */
class ActionApiController extends FOSRestController implements ClassResourceInterface
{

    /**
     * @Rest\View(serializerGroups={"classeurById"})
     * @Rest\Get("/{id}")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param Classeur $classeur
     * @return \Doctrine\Common\Collections\Collection
     * @internal param $id
     */
    public function getByClasseurAction (Classeur $classeur)
    {
        return $classeur->getActions();
    }

    /**
     * @Rest\View("statusCode=Response::HTTP_CREATED", serializerGroups={"classeurById"})
     * @Rest\Post("/new/{id}")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param Request $request
     * @param Classeur $classeur
     * @internal param Request $request
     * @return Action|JsonResponse
     */
    public function postAction (Request $request, Classeur $classeur)
    {
        $action = new Action();

        $form = $this->createForm(ActionType::class, $action);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $action->setClasseur($classeur);
            $action->setUser($this->getUser());
            $action->setUsername($this->getUser()->getPrenom() . " " . $this->getUser()->getNom());
            $em->persist($action);
            $em->flush();

            return $action;
        }
        else {
            return new JsonResponse(['message' => 'Impossible d\'ajouter un commentaire'], Response::HTTP_NOT_MODIFIED);
        }
    }


    /**
     * @Rest\View()
     * @Rest\Patch("/{id}")
     * @ParamConverter("Action", options={"mapping": {"id": "id"}})
     * @param Request $request
     * @param Action $action
     */
    public function updateAction (Request $request, Classeur $classeur)
    {

        $form = $this->createForm(ClasseurType::class, $classeur);
        $form->submit($request->request->all(), false);

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $classeur;
        }
        else {
            return $form;
        }

    }
}
