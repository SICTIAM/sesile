<?php

namespace Sesile\UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sesile\UserBundle\Entity\Groupe;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;

/**
 * @Rest\Route("/apirest/circuit_validation", options = { "expose" = true })
 */
class CircuitValidationApiController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @Rest\Get("")
     * @return array
     */
    public function listByCollectiviteAction()
    {
        $em = $this->getDoctrine()->getManager();
        $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->findOneById(1);
        $circuits = $em->getRepository('SesileUserBundle:Groupe')->findByCollectivite($collectivite);
        return $circuits;
    }

    /**
     * @Rest\Get("/{id}")
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