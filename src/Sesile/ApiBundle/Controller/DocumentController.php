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
 * Class DocumentController
 * @package Sesile\ApiBundle\Controller
 *
 * @Route("/document")
 */
class DocumentController extends FOSRestController implements TokenAuthenticatedController
{


    /**
     * Cette méthode permet de récupérer un document
     *
     * Si l'utilisateur courant n'as pas accès au classeur associé au document, un 403 not allowed sera renvoyé
     *
     * @var Request $request
     * @return array
     * @Route("/{id}")
     * @Rest\View()
     * @Method("get")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Permet de récupérer un document",
     *  requirements={
     *      {"name"="id", "dataType"="integer", "required"=true, "description"="Id du document à obtenir"}
     *  }
     * )
     */
    public function getAction($id)
    {


        return array();

    }


    /**
     * Cette méthode permet d'editer un document
     *
     * Si l'utilisateur courant n'as pas accès au classeur associé au document, un 403 not allowed sera renvoyé
     *
     * @var Request $request
     * @return array
     * @Route("/{id}/edit")
     * @Rest\View()
     * @Method("post")
     *
     * @ApiDoc(
     *  resource=false,
     *  description="Permet d'editer un document",
     *  requirements={
     *
     *  }
     * )
     */
    public function updateAction($id)
    {


        return array();

    }


    /**
     * Cette méthode permet de supprimer un document
     *
     * Si l'utilisateur courant n'as pas accès au classeur associé au document, un 403 not allowed sera renvoyé
     *
     * @var Request $request
     * @return array
     * @Route("/{id}")
     * @Rest\View()
     * @Method("delete")
     *
     * @ApiDoc(
     *  resource=false,
     *  description="Permet de supprimer un document",
     *  requirements={
     *
     *  }
     * )
     */
    public function deleteAction($id)
    {


        return array();

    }

    /**
     * Cette méthode permet récupérer le contenu d'un document
     *
     * Si l'utilisateur courant n'as pas accès au classeur associé au document, un 403 not allowed sera renvoyé
     *
     * @var Request $request
     * @return array
     * @Route("/{id}/content")
     * @Rest\View()
     * @Method("get")
     *
     * @ApiDoc(
     *  resource=false,
     *  description="Permet de récupérer le contenu d'un document",
     *  requirements={
     *
     *  }
     * )
     */
    public function downloadAction($id)
    {


        return array();

    }


}