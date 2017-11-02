<?php

namespace Sesile\UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sesile\ClasseurBundle\Entity\TypeClasseur;
use Sesile\UserBundle\Entity\Groupe;
use Sesile\MainBundle\Entity\Collectivite;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sesile\UserBundle\Form\GroupeType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Rest\Route("/apirest/circuit_validation", options = { "expose" = true })
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class CircuitValidationApiController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @Rest\Get("s/{collectiviteId}")
     * @Rest\View(serializerGroups={"listCircuitByCollectivite"})
     * @ParamConverter("collectivite", options={"mapping": {"collectiviteId": "id"}})
     * @return array|\Doctrine\Common\Collections\Collection
     */
    public function listByCollectiviteAction(Collectivite $collectivite)
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') ||
            $this->getUser()->getCollectivite() == $collectivite) {
            $em = $this->getDoctrine()->getManager();
            $circuits = $em->getRepository('SesileUserBundle:Groupe')->findByCollectivite($collectivite);
            return $circuits;
        } else {
            return new JsonResponse(['message' => "Denied Access"], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @Rest\Get("/{id}")
     * @Rest\View(serializerGroups={"getByIdCircuit"})
     * @return Groupe
     * @ParamConverter("Groupe", options={"mapping": {"id": "id"}})
     * @param Groupe $groupe
     * @return Groupe
     * @internal param $id
     */
    public function getByIdAction(Groupe $groupe)
    {
        return $groupe;
    }

    /**
     * @Rest\View()
     * @Rest\Put("/{id}")
     * @param Request $request
     * @param Groupe $groupe
     * @return Groupe|\Symfony\Component\Form\Form|JsonResponse
     * @ParamConverter("TypeClasseur", options={"mapping": {"id": "id"}})
     */
    public function updateAction(Request $request, Groupe $groupe)
    {
        if (empty($groupe)) {
            return new JsonResponse(['message' => 'Circuit de validation inexistant'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(GroupeType::class, $groupe);
        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->merge($groupe);
            $em->flush();
        }

        return $groupe;
    }

    /**
     * @Rest\View()
     * @Rest\Post("/")
     * @param Request $request
     * @return Groupe|\Symfony\Component\Form\Form|JsonResponse
     */
    public function postAction(Request $request)
    {
        $groupe = new Groupe();
        $form = $this->createForm(GroupeType::class, $groupe);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($groupe);
            $em->flush();
        }

        return $groupe;
    }

    /**
     * @Rest\View()
     * @Rest\Delete("/{id_groupe}")
     * @param Groupe $groupe
     * @return bool|JsonResponse
     * @ParamConverter("groupe", options={"mapping": {"id_groupe" : "id"}})
     */
    public function removeAction(Groupe $groupe)
    {
        if (empty($groupe)) {
            return new JsonResponse(['message' => 'Circuit de validation inexistant'], Response::HTTP_NOT_FOUND);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($groupe);
        $em->flush();

        return true;
    }

    /**
     * @Rest\View()
     * @Rest\Post("/types/{id_groupe}/{id_type}")
     * @param TypeClasseur $typeClasseur
     * @param Groupe $groupe
     * @return Groupe|\Symfony\Component\Form\Form|JsonResponse
     * @internal param Request $request
     * @ParamConverter("typeClasseur", options={"mapping": {"id_type" : "id"}})
     * @ParamConverter("groupe", options={"mapping": {"id_groupe" : "id"}})
     */
    public function addTypesAction(TypeClasseur $typeClasseur, Groupe $groupe)
    {
        if (empty($groupe) || empty($typeClasseur)) {
            return new JsonResponse(['message' => 'Circuit de validation inexistant'], Response::HTTP_NOT_FOUND);
        }

        $em = $this->getDoctrine()->getManager();

        $typeClasseur->addGroupe($groupe);
        $groupe->addType($typeClasseur);

        $em->persist($groupe);
        $em->persist($typeClasseur);
        $em->flush();

        return $groupe;
    }

    /**
     * @Rest\View()
     * @Rest\Delete("/types/{id_groupe}/{id_type}")
     * @param TypeClasseur $typeClasseur
     * @param Groupe $groupe
     * @return Groupe|\Symfony\Component\Form\Form|JsonResponse
     * @internal param Request $request
     * @ParamConverter("typeClasseur", options={"mapping": {"id_type" : "id"}})
     * @ParamConverter("groupe", options={"mapping": {"id_groupe" : "id"}})
     */
    public function removeTypesAction(TypeClasseur $typeClasseur, Groupe $groupe)
    {
        if (empty($groupe) || empty($typeClasseur)) {
            return new JsonResponse(['message' => 'Circuit de validation inexistant'], Response::HTTP_NOT_FOUND);
        }

        $em = $this->getDoctrine()->getManager();

        $typeClasseur->removeGroupe($groupe);
        $groupe->removeType($typeClasseur);

        $em->flush();

        return $groupe;
    }

}