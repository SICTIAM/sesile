<?php

namespace Sesile\UserBundle\Controller;

use Sesile\MainBundle\Entity\Collectivite;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sesile\UserBundle\Entity\User;
use Sesile\UserBundle\Form\UserType;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcher;

/**
 * @Rest\Route("/apirest/user", options = { "expose" = true })
 */
class UserApiController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @Rest\View(serializerGroups={"currentUser"})
     * @Rest\Get("/current")
     */
    public function getCurrentAction() {
        return $this->getUser();
    }

    /**
     * @Rest\View()
     * @Rest\Get("/isauthenticated")
     */
    public function isauthenticatedAction() {
        return $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY');
    }

    /**
     * @Rest\Get("/current")
     * @Rest\View(serializerGroups={"userCurrentInfos"})
     */
    public function getCurrentUserAction() {
        return $this->getUser();
    }

    /**
     * @return array
     * @Rest\View()
     * @Rest\Get("s/by_collectivite")
     */
    public function listByCollectiviteAction()
    {
        return $this->get('session')->get("collectivite");
    }

    /**
     * @Rest\View()
     * @Rest\Get("s/{id}")
     * @ParamConverter("Collectivite", options={"mapping": {"id": "id"}})
     * @param Collectivite $collectivite
     * @return array|\Doctrine\Common\Collections\Collection
     */
    public function usersCollectiviteAction(Collectivite $collectivite)
    {

        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')
            || $this->getUser()->getCollectivite() == $collectivite) {

            return $collectivite->getUsers();
        } else {
            return $this->getUser()->getCollectivite()->getUsers();
        }
    }

    /**
     * @return array
     * @Rest\View()
     * @Rest\Get("s")
     */
    public function listAction()
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->getDoctrine()->getManager()->getRepository('SesileUserBundle:User')->findAll();
        }
        else if($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return $this->getDoctrine()->getManager()->getRepository('SesileUserBundle:User')->findByCollectivite($this->getUser()->getCollectivite()->getId());
        }
        else {
            return array();
        }
    }

    /**
     * @return array
     * @Rest\View(serializerGroups={"searchUser"})
     * @Rest\Get("/search")
     * @QueryParam(name="value")
     * @QueryParam(name="collectiviteId")
     */
     public function findByNomOrPrenomAction(ParamFetcher $paramFetcher)
     {
         $value = $paramFetcher->get('value');
         $collectiviteId = $paramFetcher->get('collectiviteId');
         if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') || $this->getUser()->getCollectivite()->getId() == $collectiviteId) {
            return $this->getDoctrine()->getManager()->getRepository('SesileUserBundle:User')->findByNameOrFirstName($value, $collectiviteId);
         }
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
     * @Rest\Delete("{id_collectivite}/user/{id}")
     * @ParamConverter("Collectivite", options={"mapping": {"id": "id_collectivite"}})
     * @ParamConverter("User", options={"mapping": {"id": "id"}})
     * @param Collectivite $collectivite
     * @param User $user
     * @return User
     * @internal param $id
     */
    public function removeAction(Collectivite $collectivite, User $user)
    {
        if ($user
            && $collectivite
            && ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')
            || $this->getUser()->getCollectivite() == $collectivite)
        ) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($user);
                $em->flush();

                return $user;
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
