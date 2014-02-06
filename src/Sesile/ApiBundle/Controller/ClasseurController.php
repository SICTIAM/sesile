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
 * Class UserController
 * @package Sesile\ApiBundle\Controller
 *
 * @Route("/classeur")
 */
class ClasseurController extends FOSRestController implements TokenAuthenticatedController
{


    /**
     * Cette méthode permet de récupérer la liste des classeurs
     *
     * Si aucun paramètre renvoie tous les classeurs visibles par l'utilisateur courant,
     * Sinon renvoie une partie de cette liste en fonction du filtre
     *
     * @var Request $request
     * @return array
     * @Route("/")
     * @Rest\View()
     * @Method("get")
     *
     * @QueryParam(name="filtre", requirements="(a_valider|a_signer|termine|prive)", strict=false, nullable=true, description="Filtre optionnel ( exemple: ?filtre=a_valider )")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Permet de récupérer la liste des classeurs"
     * )
     */
    public function indexAction()
    {


        return array();

    }

    /**
     * Cette méthode permet de récupérer un classeur
     *
     * Si l'utilisateur courant n'as pas accès au classeur, un 403 not allowed sera renvoyé
     *
     * @var Request $request
     * @return array
     * @Route("/{id}")
     * @Rest\View()
     * @Method("get")
     *
     * @ApiDoc(
     *  resource=false,
     *  description="Permet de récupérer un classeur",
     *  requirements={
     *      {"name"="id", "dataType"="integer", "required"=true, "description"="Id du classeur à obtenir"}
     *  }
     * )
     */
    public function getAction($id)
    {


        return array();

    }


    /**
     * Cette méthode permet de récupérer un classeur
     *
     * Si l'utilisateur courant n'as pas accès au classeur, un 403 not allowed sera renvoyé
     *
     * @var Request $request
     * @return array
     * @Route("/")
     * @Rest\View()
     * @Method("post")
     *
     * @ApiDoc(
     *  resource=false,
     *  description="Permet de créer un nouveau classeur",
     *  requirements={
     *
     *  }
     * )
     */
    public function newAction($id)
    {


        return array();

    }


    /**
     * Cette méthode permet d'editer un classeur
     *
     * Si l'utilisateur courant n'as pas accès au classeur, un 403 not allowed sera renvoyé
     *
     * @var Request $request
     * @return array
     * @Route("/{id}")
     * @Rest\View()
     * @Method("put")
     *
     * @ApiDoc(
     *  resource=false,
     *  description="Permet d'editer un classeur",
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
     * Cette méthode permet de supprimer un classeur
     *
     * Si l'utilisateur courant n'as pas accès au classeur, un 403 not allowed sera renvoyé
     *
     * @var Request $request
     * @return array
     * @Route("/{id}")
     * @Rest\View()
     * @Method("delete")
     *
     * @ApiDoc(
     *  resource=false,
     *  description="Permet de supprimer un classeur",
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
     * Cette méthode permet de créer un document un document et de l'associer à un classeur
     *
     * Si l'utilisateur courant n'as pas accès au classeur, un 403 not allowed sera renvoyé
     *
     * @var Request $request
     * @return array
     * @Route("/{id}/newDocument")
     * @Rest\View()
     * @Method("post")
     *
     * @ApiDoc(
     *  resource=false,
     *  description="Permet d'ajouter un document à un classeur",
     *  requirements={
     *
     *  }
     * )
     */
    public function newDocumentAction($id)
    {


        return array();

    }

}