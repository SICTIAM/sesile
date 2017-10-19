<?php

namespace Sesile\MainBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


/**
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 * @Rest\Route("/apirest/documentation", options = { "expose" = true })
 */
class DocumentationApiController extends Controller
{
    /**
     * @Rest\View()
     * @Rest\Get("/aides")
     * @return array
     */
    public function getAllAidesAction()
    {
        return $this->getDoctrine()
            ->getManager()
            ->getRepository('SesileMainBundle:Aide')
            ->findAll();
    }

    /**
     * @Rest\View()
     * @Rest\Get("/patchs")
     * @return array
     */
    public function getAllPatchAction()
    {
        return $this->getDoctrine()
            ->getManager()
            ->getRepository('SesileMainBundle:Patch')
            ->findAll();
    }
}
