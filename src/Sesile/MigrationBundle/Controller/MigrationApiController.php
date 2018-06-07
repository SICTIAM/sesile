<?php

namespace Sesile\MigrationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 * @Rest\Route("/api/migration", options = { "expose" = true })
 */
class MigrationApiController extends Controller
{
    /**
     * @Rest\Route("/collectivity/list", options = { "expose" = true })
     * @return Response
     */
    public function indexAction()
    {
        return new JsonResponse('', Response::HTTP_OK);
    }
}
