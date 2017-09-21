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
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @Rest\View(serializerGroups={"getAllCollectivite"})
     * @Rest\Get("s")
     * @return array
     */
    public function getAllAction()
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {

            return $this->getDoctrine()
                ->getManager()
                ->getRepository('SesileMainBundle:Collectivite')
                ->findAll();
        } else {
            return array($this->getUser()->getCollectivite());
        }

    }
}
