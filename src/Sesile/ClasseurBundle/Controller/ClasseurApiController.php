<?php

namespace Sesile\ClasseurBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sesile\ClasseurBundle\Entity\Classeur as Classeur;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sesile\ClasseurBundle\Form\ClasseurType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Rest\Route("/apirest/classeur", options = { "expose" = true })
 */
class ClasseurApiController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @return array
     * @Rest\View(serializerGroups={"listClasseur"})
     * @Rest\Get("s/list/all")
     */
    public function listAllAction()
    {

        $em = $this->getDoctrine()->getManager();
        $classeurs = $em->getRepository('SesileClasseurBundle:Classeur')->getAllClasseursVisibles($this->getUser()->getId());

        return $classeurs;
    }

    /**
     * @param null $sort
     * @param null $order
     * @param int $limit
     * @param int $start
     * @param null $userId
     * @return array
     * @Rest\View(serializerGroups={"listClasseur"})
     * @Rest\Get("s/list/{sort}/{order}/{limit}/{start}/{userId}", requirements={"limit" = "\d+", "start" = "\d+"}, defaults={"sort" = "creation", "order"="DESC", "limit" = 10, "start" = 0})
     */
    public function listAction($sort = null, $order = null, $limit, $start, $userId = null)
    {
        if (
            $userId === null
            || !($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') || $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
        ) $userId = $this->getUser()->getId();

        $em = $this->getDoctrine()->getManager();
        $classeurs = $em->getRepository('SesileClasseurBundle:Classeur')->getClasseursVisibles($userId, $sort, $order, $limit, $start);

        return $classeurs;
    }

    /**
     * @param null $sort
     * @param null $order
     * @param int $limit
     * @param int $start
     * @param null $userId
     * @return array
     * @Rest\View(serializerGroups={"listClasseur"})
     * @Rest\Get("s/valid/{sort}/{order}/{limit}/{start}/{userId}", requirements={"limit" = "\d+", "start" = "\d+"}, defaults={"sort" = "creation", "order"="DESC", "limit" = 10, "start" = 0})
     */
    public function validAction($sort = null, $order = null, $limit, $start, $userId = null)
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $classeursId = $em->getRepository('SesileUserBundle:User')->getClasseurIdValidableForUser($user);
        $classeurs = $em->getRepository('SesileClasseurBundle:Classeur')->getClasseursValidable($classeursId, $sort, $order, $limit, $start, $user->getId());


        return $classeurs;

    }

    /**
     * @param null $sort
     * @param null $order
     * @param int $limit
     * @param int $start
     * @param null $userId
     * @return array
     * @Rest\View(serializerGroups={"listClasseur"})
     * @Rest\Get("s/retract/{sort}/{order}/{limit}/{start}/{userId}", requirements={"limit" = "\d+", "start" = "\d+"}, defaults={"sort" = "creation", "order"="DESC", "limit" = 10, "start" = 0})
     */
    public function listRetractAction($sort = null, $order = null, $limit, $start, $userId = null)
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $classeursId = $em->getRepository('SesileUserBundle:User')->getClasseurIdRetractableForUser($user);
        $classeurs = $em->getRepository('SesileClasseurBundle:Classeur')->getClasseursRetractable($classeursId, $sort, $order, $limit, $start, $user->getId());

        return $classeurs;

    }

    /**
     * @param null $sort
     * @param null $order
     * @param int $limit
     * @param int $start
     * @param null $userId
     * @return array
     * @Rest\View(serializerGroups={"listClasseur"})
     * @Rest\Get("s/remove/{sort}/{order}/{limit}/{start}/{userId}", requirements={"limit" = "\d+", "start" = "\d+"}, defaults={"sort" = "creation", "order"="DESC", "limit" = 10, "start" = 0})
     */
    public function listRemovableAction($sort = null, $order = null, $limit, $start, $userId = null)
    {
        if (
            $userId === null
            || !($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') || $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
        ) $userId = $this->getUser()->getId();

        $em = $this->getDoctrine()->getManager();
        $classeurs = $em->getRepository('SesileClasseurBundle:Classeur')->getClasseursremovable($userId, $sort, $order, $limit, $start);

        return $classeurs;

    }

    /**
     * @Rest\View(serializerGroups={"classeurById"})
     * @Rest\Get("/{id}")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param Classeur $classeur
     * @return Classeur
     * @internal param $id
     */
    public function getByIdAction (Classeur $classeur)
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
     * @Rest\Patch("/{id}")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param Request $request
     * @param Classeur $classeur
     */
    public function updateAction (Request $request, Classeur $classeur)
    {
        if (empty($classeur)) {
            return new JsonResponse(['message' => 'classeur inexistant'], Response::HTTP_NOT_FOUND);
        }

        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') ||
            $this->getUser()->getId() == $classeur->getUser()) {

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
        } else {
            return new JsonResponse(['message' => "Denied Access"], Response::HTTP_FORBIDDEN);
        }
    }
}
