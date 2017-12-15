<?php

namespace Sesile\ClasseurBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sesile\ClasseurBundle\Entity\Action;
use Sesile\ClasseurBundle\Entity\Classeur as Classeur;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sesile\ClasseurBundle\Form\ClasseurType;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Rest\Route("/apirest/classeur/action", options = { "expose" = true })
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
     * @Rest\View("statusCode=Response::HTTP_CREATED")
     * @Rest\Post("/new/{id}")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param Classeur $classeur
     * @param $comment
     * @internal param Request $request
     */
    public function postAction (Classeur $classeur, $comment)
    {
        // TODO security improve : user must have the good right to post
        $this->getDoctrine()->getManager()->getRepository('SesileClasseurBundle:Action')
            ->addDocumentAction($classeur, "Commentaire", "", $comment, $this->getUser());
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
