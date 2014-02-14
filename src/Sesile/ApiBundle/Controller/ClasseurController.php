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
     * @QueryParam(name="filtre", requirements="(a_valider|a_signer|finalise|termine|prive)", strict=false, nullable=true, description="Filtre optionnel ( exemple: ?filtre=a_valider )")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Permet de récupérer la liste des classeurs"
     * )
     */
    public function indexAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('SesileUserBundle:User')->findOneBy(array('apitoken' => $request->headers->get('token'), 'apisecret' => $request->headers->get('secret')));

        $classeur = array();

        if ($request->query->has('filtre')) {

            switch ($request->query->get('filtre')) {

                case 'a_valider':

                    $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->findBy(
                        array(
                            "validant" => $user ? $user->getId() : 0,
                            "status" => 1
                        ));

                    break;

                case 'a_signer':

                    $classeurtosort = $em->getRepository('SesileClasseurBundle:Classeur')->findBy(
                        array(
                            "validant" => $user ? $user->getId() : 0,
                            "status" => 1
                        ));

                    $classeur = array();

                    foreach ($classeurtosort as $class) {
                        if ($class->isSignable($em)) {
                            $classeur[] = $class;
                        }
                    }

                    break;

                case 'finalise':

                    $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->findBy(
                        array(

                            "status" => 2
                        ));

                    break;

                case 'retire':

                    $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->findBy(
                        array(

                            "status" => 3
                        ));

                    break;

                case 'prive':

                    break;
                default:
                    break;


            }


        } else {
            $classeur = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->getClasseursVisibles($user->getId());
        }


        return $classeur;


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
    public function getAction(Request $request, $id)
    {


        $em = $this->getDoctrine()->getManager();


        $user = $em->getRepository('SesileUserBundle:User')->findOneBy(array('apitoken' => $request->headers->get('token'), 'apisecret' => $request->headers->get('secret')));
        $classeur = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->getClasseurByUser($id, $user->getId());


        return $classeur[0];


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