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
     *
     * @Route("/domain/main")
     * @Template()
     */
    public function mainDomainAction(Request $request)
    {
        $url = $request->server->get('HTTP_HOST');
        $parsedUrl = parse_url($url);
        if ((isset($parsedUrl['path']) && $this->getParameter('domain') == $parsedUrl['path']) || isset($parsedUrl['host']) && $this->getParameter('domain') == $parsedUrl['host']) {
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
