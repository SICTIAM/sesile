<?php

namespace Sesile\UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sesile\UserBundle\Entity\Groupe;
use Sesile\MainBundle\Entity\Collectivite;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;

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
}