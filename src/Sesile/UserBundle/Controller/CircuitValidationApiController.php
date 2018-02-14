<?php

namespace Sesile\UserBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @param Collectivite $collectivite
     * @return array|\Doctrine\Common\Collections\Collection
     */
    public function listByCollectiviteAction(Collectivite $collectivite)
    {
        if ($this->authorize($collectivite)) {
            $em = $this->getDoctrine()->getManager();
            $circuits = $em->getRepository('SesileUserBundle:Groupe')->findByCollectivite($collectivite);
            return $circuits;
        } else {
            return new JsonResponse(['message' => "Denied Access"], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @Rest\Get("s_user/")
     * @Rest\View(serializerGroups={"listCircuitByUser"})
     * @return array|\Doctrine\Common\Collections\Collection
     */
    public function listByUserAction()
    {
        $em = $this->getDoctrine()->getManager();
        $circuits_id = $em->getRepository('SesileUserBundle:EtapeGroupe')->findByUsers($this->getUser()->getId());
        $circuits = $em->getRepository('SesileUserBundle:Groupe')->getCircuits($circuits_id, $this->getUser());
        return $circuits;
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
        if ($this->authorize($groupe->getCollectivite())){
            return $groupe;
        } else {
            return new JsonResponse(['message' => "Denied Access"], Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * @Rest\PUT("/{id}")
     * @Rest\View(serializerGroups={"getByIdCircuit"})
     * @param Request $request
     * @param Groupe $groupe
     * @return Groupe|\Symfony\Component\Form\Form|JsonResponse
     * @ParamConverter("Groupe", options={"mapping": {"id": "id"}})
     */
    public function updateAction(Request $request, Groupe $groupe)
    {
        if (empty($groupe)) {
            return new JsonResponse(['message' => 'Circuit de validation inexistant'], Response::HTTP_NOT_FOUND);
        }

        if ($this->authorize($groupe->getCollectivite())){

            $em = $this->getDoctrine()->getManager();
            $etapeGroupes = new ArrayCollection();

            foreach ($groupe->getEtapeGroupes() as $etapeGroupe) {
                $etapeGroupes->add($etapeGroupe);
            }

            $form = $this->createForm(GroupeType::class, $groupe);
            $form->submit($request->request->all());

            if ($form->isValid()) {

                foreach ($groupe->getTypes() as $type) {
                    $type->addGroupe($groupe);
                    $em->persist($type);
                }
                foreach ($groupe->getEtapeGroupes() as $etapeGroupe) {
                    $etapeGroupe->setGroupe($groupe);
                    $em->persist($etapeGroupe);
                }
                foreach ($etapeGroupes as $etapeGroupe) {
                    if ($groupe->getEtapeGroupes()->contains($etapeGroupe) === false) {
                        $groupe->removeEtapeGroupe($etapeGroupe);
                        $etapeGroupe->setGroupe();
                        $em->remove($etapeGroupe);
                    }
                }
                $em->persist($groupe);
                $em->flush();
                return $groupe;
            } else {
                return $form;
            }
        } else {
            return new JsonResponse(['message' => "Denied Access"], Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * @Rest\View(serializerGroups={"getByIdCircuit"})
     * @Rest\Post("/")
     * @param Request $request
     * @return Groupe|\Symfony\Component\Form\Form|JsonResponse
     */
    public function postAction(Request $request)
    {
        $groupe = new Groupe();
        $form = $this->createForm(GroupeType::class, $groupe);
        $form->submit($request->request->all());
        if ($this->authorize($groupe->getCollectivite())){
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                foreach ($groupe->getEtapeGroupes() as $etapeGroupe) {
                    $etapeGroupe->setGroupe($groupe);
                    $em->persist($etapeGroupe);
                }
                $em->persist($groupe);
                $em->flush();
            }
        } else {
            return new JsonResponse(['message' => "Denied Access"], Response::HTTP_FORBIDDEN);
        }

        return $groupe;
    }

    /**
     * @Rest\View()
     * @Security("has_role('ROLE_SUPER_ADMIN') or has_role('ROLE_ADMIN')")
     * @Rest\Delete("/{id}/{collectiviteId}")
     * @param Groupe $groupe
     * @return bool|JsonResponse
     * @ParamConverter("groupe", options={"mapping": {"id" : "id"}})
     * @ParamConverter("collectivite", options={"mapping": {"collectiviteId": "id"}})
     * @param Collectivite $collectivite
     */
    public function removeAction(Groupe $groupe, Collectivite $collectivite)
    {
        if (empty($groupe)) {
            return new JsonResponse(['message' => 'Circuit de validation inexistant'], Response::HTTP_NOT_FOUND);
        }
        if ($this->authorize($groupe->getCollectivite())){
            $em = $this->getDoctrine()->getManager();
            $etapeGroupes = new ArrayCollection();

            foreach ($groupe->getEtapeGroupes() as $etapeGroupe) {
                $etapeGroupes->add($etapeGroupe);
            }

            foreach ($etapeGroupes as $etapeGroupe) {
                $groupe->removeEtapeGroupe($etapeGroupe);
                $em->remove($etapeGroupe);
            }

            $em->remove($groupe);
            $em->flush();

            $circuits = $em->getRepository('SesileUserBundle:Groupe')->findByCollectivite($collectivite);
            return $circuits;
        } else {
            return new JsonResponse(['message' => "Denied Access"], Response::HTTP_FORBIDDEN);
        }
    }

    private function authorize(Collectivite $collectivite) {
        return $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') ||
                ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') &&
                $this->getUser()->getCollectivite() == $collectivite);
    }

}