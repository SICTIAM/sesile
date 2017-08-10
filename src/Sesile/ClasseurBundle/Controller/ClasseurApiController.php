<?php

namespace Sesile\ClasseurBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sesile\ClasseurBundle\Entity\Classeur as Classeur;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class ClasseurApiController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @param int $limit
     * @param int $start
     * @return array
     * @Rest\View()
     * @Rest\Get("list/{limit}/{start}", requirements={"limit" = "\d+", "start" = "\d+"}, defaults={"limit" = 10, "start" = 0})
     */
    public function listAction($limit, $start)
    {
        return $this->getDoctrine()
            ->getManager()
            ->getRepository('SesileClasseurBundle:Classeur')
            ->getClasseursVisibles($this->getUser()->getId(), $limit, $start);

        /*return $this->getDoctrine()->getManager()->getRepository('SesileClasseurBundle:Classeur')->findBy(
            array("user"    => $this->getUser()),
            array("creation"    => "DESC"),
            $limit,
            $start
        );*/
    }

    /**
     * @Rest\View()
     * @Rest\Get("/{id}")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param Classeur $classeur
     * @return Classeur
     * @internal param $id
     */
    public function getAction (Classeur $classeur)
    {
        return $classeur;
    }

    /**
     * @Rest\View("statusCode=Response::HTTP_CREATED")
     * @Rest\Post("/new")
     * @param Request $request
     */
    public function postAction (Request $request)
    {

    }

    /**
     * @Rest\View()
     * @Rest\Delete("/{id}")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"})
     * @param Classeur $classeur
     */
    /*public function removeAction (Classeur $classeur)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($classeur);
        $em->flush();
    }*/

    /**
     * @Rest\View()
     * @Rest\Put("/{id}")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param Request $request
     * @param Classeur $classeur
     */
    public function updateAction (Request $request, Classeur $classeur)
    {

    }



}
