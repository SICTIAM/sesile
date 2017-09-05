<?php
namespace Sesile\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\HttpException;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;


/**
 * Class CircuitController
 * @package Sesile\ApiBundle\Controller
 *
 * @Route("/circuit")
 */
class CircuitController extends FOSRestController implements TokenAuthenticatedController
{


    /**
     * Cette méthode permet de récupérer la liste des circuits de l'utilisateur déterminé par le token et le secret passés en headers
     *
     * Cette méthode est bosolète depuis la version 3.0 de SESILE
     *
     *
     * @var Request $request
     * @return array
     * @Route("/")
     * @Rest\View()
     * @Method("get")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Permet de récupérer la liste des circuits de l'utilisateur. Méthode obsolète"
     *
     * )
     */
    public function indexAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();


        $circuits_du_user = $em->getRepository('SesileCircuitBundle:Circuit')->findAll();

        $circuits = array();
        foreach ($circuits_du_user as $circuit) {
            $circuits[] = array("id" => $circuit->getId(), "name" => $circuit->getName(), "ordre" => $circuit->getOrdre());
        }


        return $circuits;

    }


}