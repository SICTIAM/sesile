<?php

namespace Sesile\MigrationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("is_granted('ROLE_SUPER_ADMIN')")
 * @Rest\Route("/api/migration/v3v4", options = { "expose" = true })
 */
class MigrationApiController extends Controller
{
    /**
     * @Rest\Route("/collectivity/list", options = { "expose" = true })
     * @return Response
     */
    public function getCollectivityListAction()
    {
        $result = $this->get('collectivite.manager')->getMigrationCollectivityList();
        if (false === $result->isSuccess()) {
            return new JsonResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new JsonResponse($result->getData(), Response::HTTP_OK);
    }
    /**
     * @Rest\Route("/collectivity/legacy/list", options = { "expose" = true })
     * @return Response
     */
    public function getLegacyCollectivityListAction()
    {
        $result = $this->get('legacy.collectivity.manager')->getLegacyCollectivityList();
        if (false === $result->isSuccess()) {
            return new JsonResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new JsonResponse($result->getData(), Response::HTTP_OK);
    }
}
