<?php

namespace Sesile\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * Return true if on main domain name (from config.yml parameters.domain)
     * returns false if on subdomain
     *
     * @Route("/domain/control")
     * @Template()
     */
    public function mainDomainAction(Request $request)
    {
//        $url = parse_url($request->server->get('HTTP_HOST'));
        $url = $request->server->get('HTTP_HOST');
        $subdomain = str_replace('.'. $this->getParameter('domain'), '' , $url);
        if ($subdomain == $url) {
            return new JsonResponse(['main' => true, 'mainDomain' => $this->getParameter('domain'), 'currentDomain' => $request->server->get('HTTP_HOST')], Response::HTTP_OK);
        }

        return new JsonResponse(['main' => false, 'mainDomain' => $this->getParameter('domain'), 'currentDomain' => $request->server->get('HTTP_HOST')], Response::HTTP_OK);
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
