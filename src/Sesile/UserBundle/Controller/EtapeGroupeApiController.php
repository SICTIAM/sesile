<?php

namespace Sesile\UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sesile\UserBundle\Entity\EtapeGroupe;
use Sesile\UserBundle\Entity\Groupe;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sesile\UserBundle\Entity\User;
use Sesile\UserBundle\Entity\UserPack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Rest\Route("/apirest/etape_groupe", options = { "expose" = true })
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class EtapeGroupeApiController extends FOSRestController implements ClassResourceInterface
{

    /**
     * @Rest\View()
     * @Rest\Post("/user/{id_etapeGroupe}/{id_user}")
     * @param EtapeGroupe $etapeGroupe
     * @param User $user
     * @internal param Request $request
     * @ParamConverter("user", options={"mapping": {"id_user" : "id"}})
     * @ParamConverter("etapeGroupe", options={"mapping": {"id_etapeGroupe" : "id"}})
     * @return \Sesile\ClasseurBundle\Entity\Groupe
     */
    public function addUserEtapeAction(EtapeGroupe $etapeGroupe, user $user)
    {
        $em = $this->getDoctrine()->getManager();
        $etapeGroupe->addUser($user);
        $user->addEtapeGroupe($etapeGroupe);
        $em->persist($etapeGroupe);
        $em->persist($user);
        $em->flush();

        return $etapeGroupe->getGroupe();
    }

    /**
     * @Rest\View()
     * @Rest\Delete("/user/{id_etapeGroupe}/{id_user}")
     * @internal param Request $request
     * @ParamConverter("user", options={"mapping": {"id_user" : "id"}})
     * @ParamConverter("etapeGroupe", options={"mapping": {"id_etapeGroupe" : "id"}})
     * @param EtapeGroupe $etapeGroupe
     * @param User $user
     * @return \Sesile\ClasseurBundle\Entity\Groupe
     */
    public function removeUserEtapeAction(EtapeGroupe $etapeGroupe, user $user)
    {
        $em = $this->getDoctrine()->getManager();
        $etapeGroupe->removeUser($user);
        $user->removeEtapeGroupe($etapeGroupe);
        $em->flush();

        return $etapeGroupe->getGroupe();
    }

    /**
     * @Rest\View()
     * @Rest\Post("/user_pack/{id_etapeGroupe}/{id_userPack}")
     * @param EtapeGroupe $etapeGroupe
     * @param userPack $userPack
     * @internal param Request $request
     * @ParamConverter("userPack", options={"mapping": {"id_userPack" : "id"}})
     * @ParamConverter("etapeGroupe", options={"mapping": {"id_etapeGroupe" : "id"}})
     * @return \Sesile\ClasseurBundle\Entity\Groupe
     */
    public function addUserPackEtapeAction(EtapeGroupe $etapeGroupe, userPack $userPack)
    {
        $em = $this->getDoctrine()->getManager();
        $etapeGroupe->addUserPack($userPack);
        $userPack->addEtapeGroupesUP($etapeGroupe);
        $em->persist($etapeGroupe);
        $em->persist($userPack);
        $em->flush();

        return $etapeGroupe->getGroupe();
    }

    /**
     * @Rest\View()
     * @Rest\Delete("/user_pack/{id_etapeGroupe}/{id_userPack}")
     * @ParamConverter("userPack", options={"mapping": {"id_userPack" : "id"}})
     * @ParamConverter("etapeGroupe", options={"mapping": {"id_etapeGroupe" : "id"}})
     * @param etapeGroupe $etapeGroupe
     * @param userPack $userPack
     * @return \Sesile\ClasseurBundle\Entity\Groupe
     */
    public function removeUserPackEtapeAction(EtapeGroupe $etapeGroupe, userPack $userPack)
    {
        $em = $this->getDoctrine()->getManager();
        $etapeGroupe->removeUserPack($userPack);
        $userPack->removeEtapeGroupesUP($etapeGroupe);
        $em->flush();

        return $etapeGroupe->getGroupe();
    }

    /**
     * @Rest\View()
     * @Rest\Put("/{id}/{ordre}")
     * @param EtapeGroupe $etape
     * @param $ordre
     * @return EtapeGroupe
     * @ParamConverter("EtapeGroupe", options={"mapping": {"id" : "id"}})
     */
    public function updateEtapeAction(EtapeGroupe $etape, $ordre)
    {
        $em = $this->getDoctrine()->getManager();
        $etape->setOrdre($ordre);
        $em->persist($etape);
        $em->flush();

        return $etape;
    }

    /**
     * @Rest\View()
     * @Rest\Post("/{id_groupe}/{ordre}")
     * @param Groupe $groupe
     * @param $ordre
     * @return Groupe|\Symfony\Component\Form\Form|JsonResponse
     * @ParamConverter("groupe", options={"mapping": {"id_groupe" : "id"}})
     */
    public function addEtapeAction(Groupe $groupe, $ordre)
    {
        if (empty($groupe)) {
            return new JsonResponse(['message' => 'Circuit de validation inexistant'], Response::HTTP_NOT_FOUND);
        }

        $em = $this->getDoctrine()->getManager();

        $etape = new EtapeGroupe();
        $etape->setOrdre($ordre);
        $etape->setGroupe($groupe);

        $em->persist($etape);

        $groupe->addEtapeGroupe($etape);
        $em->persist($groupe);

        $em->flush();

        return $groupe;
    }

    /**
     * @Rest\View()
     * @Rest\Delete("/{id_groupe}/{id_etape}")
     * @param Groupe $groupe
     * @param EtapeGroupe $etape
     * @return Groupe|\Symfony\Component\Form\Form|JsonResponse
     * @ParamConverter("etape", options={"mapping": {"id_etape" : "id"}})
     * @ParamConverter("groupe", options={"mapping": {"id_groupe" : "id"}})
     */
    public function removeEtapeAction(Groupe $groupe, EtapeGroupe $etape)
    {
        if (empty($groupe) || empty($etape)) {
            return new JsonResponse(['message' => 'Circuit de validation inexistant'], Response::HTTP_NOT_FOUND);
        }

        $em = $this->getDoctrine()->getManager();

        $groupe->removeEtapeGroupe($etape);
        $em->remove($etape);

        $em->flush();

        return $groupe;
    }
}