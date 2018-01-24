<?php

namespace Sesile\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Rest\Route("", options = { "expose" = true })
 */
class DefaultController extends Controller
{

    /**
     * @Route("/")
     * @Template()
     */
    public function appAction()
    {
        return $this->render('app.html.twig');
    }

    /**
     * @Route("/tableau-de-bord", options={"expose"=true},name="dashboard")
     * @Template()
     */
    public function dashboardAction()
    {
        return $this->render('app.html.twig');
    }

    /**
     * @Rest\Get("/informations")
     * @return JsonResponse
     */
    public function getAppInformationAction()
    {
        return new JsonResponse($this->getParameter('informations'));
    }
}
