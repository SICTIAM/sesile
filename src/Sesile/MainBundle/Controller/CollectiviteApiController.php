<?php

namespace Sesile\MainBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


/**
 * @Rest\Route("/apirest/collectivite", options = { "expose" = true })
 */
class CollectiviteApiController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @Rest\View(serializerGroups={"getAllCollectivite"})
     * @Rest\Get("s")
     * @return array
     */
    public function getAllAction()
    {
        return $this->getDoctrine()
            ->getManager()
            ->getRepository('SesileMainBundle:Collectivite')
            ->findAll();
    }
}
