<?php

namespace Sesile\MigrationBundle\Controller;

use Sesile\MainBundle\Entity\Collectivite;
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
     * @Rest\Get("/collectivity/list", options = { "expose" = true }, name="v3v4_migrate_org_list")
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
     * @Rest\Get("/collectivity/legacy/list", options = { "expose" = true }, name="v3v4_migrate_legacy_list")
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

    /**
     * @Rest\Get("/org/check/siren/{siren}", options = { "expose" = true }, name="v3v4_migrate_check_siren")
     * @return Response
     */
    public function checkOrgSirenAvailabilityAction($siren)
    {
        $result = $this->get('collectivite.manager')->getCollectiviteBySiren($siren);
        if (false === $result->isSuccess()) {
            return new JsonResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        if($result->getData() instanceof Collectivite) {
            return new JsonResponse(['success' => 0, 'siren' => $siren, 'orgName' => $result->getData()->getNom()], Response::HTTP_OK);
        }
        return new JsonResponse(['success' => 1, 'siren' => $siren], Response::HTTP_OK);
    }

    /**
     * @Rest\Post("/org/migrate/init", options = { "expose" = true }, name="v3v4_migrate_init")
     * @return Response
     */
    public function initCollectivityMigrationAction(Request $request)
    {
        if (!$request->request->has('siren') || !$request->request->has('orgId')) {

            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }
        $collectivityId = $request->request->get('orgId');
        $siren = $request->request->get('siren');
        $result = $this->get('sesile.migrator')->hanldeNewMigration($collectivityId, $siren);
        if (false === $result->isSuccess()) {
            return new JsonResponse([], Response::HTTP_BAD_GATEWAY);
        }

        return new JsonResponse([], Response::HTTP_CREATED);
    }
}
