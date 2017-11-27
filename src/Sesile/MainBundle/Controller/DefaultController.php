<?php

namespace Sesile\MainBundle\Controller;

use Sesile\MainBundle\Entity\Aide;
use Sesile\MainBundle\Entity\Patch;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
     * @Route("/dashboard", options={"expose"=true},name="dashboard")
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

    /**
     * @Route("/download/patch/{id}", name="download_patch",  options={"expose"=true})
     * @ParamConverter("Patch", options={"mapping": {"id": "id"}})
     * @param Patch $patch
     * @return Response
     */
    public function downloadPatchAction(Patch $patch)
    {
        $path = $this->container->getParameter('upload')['fics'] . $patch->getPath();

        $file = new SplFileInfo($path);

        $response = new Response();

        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($path));
        $response->headers->set('Content-Disposition', 'inline; filename="' . $patch->getDescription() . "." . $file->getExtension() . '"');
        $response->headers->set('Content-Length', filesize($path));

        $response->setContent(file_get_contents($path));

        return $response;
    }

    /**
     * @Route("/download/aide/{id}", name="download_aide",  options={"expose"=true})
     * @ParamConverter("Aide", options={"mapping": {"id": "id"}})
     * @param Aide $aide
     * @return Response
     */
    public function downloadAideAction(Aide $aide)
    {
        $path = $this->container->getParameter('upload')['fics'] . $aide->getPath();

        $file = new SplFileInfo($path);

        $response = new Response();

        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($path));
        $response->headers->set('Content-Disposition', 'inline; filename="' . $aide->getDescription() . "." . $file->getExtension() . '"');
        $response->headers->set('Content-Length', filesize($path));

        $response->setContent(file_get_contents($path));

        return $response;
    }
}
