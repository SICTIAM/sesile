<?php

namespace Sesile\UserBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sesile\UserBundle\Entity\EtapeClasseur;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Rest\Route("/apirest/etape_classeur", options = { "expose" = true })
 */
class EtapeClasseurApiController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @param $classeur
     * @return array
     * @internal param EtapeClasseur $etapeClasseur
     * @Rest\View(serializerGroups={"listEtapeClasseur"})
     * @Rest\Get("s/{classeur}", requirements={"classeur" = "\d+"})
     */
    public function listAction($classeur)
    {
        return $this->getDoctrine()->getRepository('SesileUserBundle:EtapeClasseur')->findBy(
            array('classeur'    => $classeur),
            array('ordre'   => 'ASC')
        );
    }

    /**
     * @Rest\View()
     * @Rest\Get("/{id}")
     * @ParamConverter("EtapeClasseur", options={"mapping": {"id": "id"}})
     * @param EtapeClasseur $etapeClasseur
     * @return EtapeClasseur
     * @internal param Classeur $classeur
     * @internal param $id
     */
    public function getAction (EtapeClasseur $etapeClasseur)
    {
        return $etapeClasseur;
    }

    /**
     * @param string $orgId collectivite id
     * @return array
     * @internal param EtapeClasseur $etapeClasseur
     * @Rest\View(serializerGroups={"listClasseurStats"})
     * @Rest\Get("s/org/{orgId}/classeur_stats", requirements={"classeur" = "\d+"})
     *
     * @todo adapt the frontend for collectiviteId route: sesile_user_etapeclasseurapi_getclasseursvalidatebytype
     */
    public function getClasseursValidateByTypeAction($orgId)
    {
        $em = $this->getDoctrine()->getManager();
        $types = $em->getRepository('SesileClasseurBundle:TypeClasseur')->findBy(
            array('collectivites'   => $orgId)
        );

        $stats[] = array("nom", "total");
        foreach ($types as $type) {
            $stats[] = array($type->getNom(), intval($this->getDoctrine()->getRepository('SesileUserBundle:EtapeClasseur')->getClasseurValidate($this->getUser(), $type)));
        }

        return $stats;
    }

    /**
     * @Rest\View("statusCode=Response::HTTP_CREATED")
     * @Rest\Post("/new")
     * @param Request $request
     */
    public function postAction (Request $request)
    {

    }

    /**
     * @Rest\View()
     * @Rest\Delete("/{id}")
     * @ParamConverter("EtapeClasseur", options={"mapping": {"id": "id"})
     * @param EtapeClasseur $etapeClasseur
     */
    /*public function removeAction (EtapeClasseur $etapeClasseur)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($etapeClasseur);
        $em->flush();
    }*/

    /**
     * @Rest\View()
     * @Rest\Put("/{id}")
     * @ParamConverter("EtapeClasseur", options={"mapping": {"id": "id"}})
     * @param Request $request
     * @param EtapeClasseur $etapeClasseur
     */
    public function updateAction (Request $request, EtapeClasseur $etapeClasseur)
    {

    }



}
