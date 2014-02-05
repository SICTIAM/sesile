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

use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;

/**
 * Class AuthentificationController
 * @package Sesile\ApiBundle\Controller
 *
 * @Route("/token/")
 */
class AuthentificationController extends FOSRestController
{


    /**
     * This the documentation description of your method, it will appear
     * on a specific pane. It will read all the text until the first
     * annotation.
     *
     * @var Request $request
     * @return array
     * @Route("validate/")
     * @Rest\View()
     * @Method("post")
     *
     * @RequestParam(name="token", description="Votre token")
     * @RequestParam(name="secret", description="Le secret associé")
     *
     * @param ParamFetcher $param
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Permet de savoir si un couple Token - Secret est valide"
     * )
     */
    public function validateAction(ParamFetcher $params)
    {


        /* $view = View::create()
             ->setStatusCode(200)
             ->setData(array('moncul'=>'lacommode'))
             ->setFormat('json');*/

        return array('moncul' => 'lacommode', 'tata' => 'tonton');

    }


}