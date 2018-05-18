<?php
namespace Sesile\ApiBundle\Controller;

use Sesile\MainBundle\Entity\Collectivite;
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
 * @Route("/user")
 */
class UserController extends FOSRestController implements TokenAuthenticatedController
{


    /**
     * Cette méthode permet de récupérer un les détails de l'utilisateur courant en fonction des headers
     *
     *
     * @var Request $request
     * @return array
     * @Route("/")
     * @Rest\View()
     * @Method("get")
     *
     *
     * @param ParamFetcher $param
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Permet de récupérer l'utilisateur courant"
     * )
     */
    public function getUserAction(Request $request)
    {


        $em = $this->getDoctrine()->getManager();


        $entity = $em->getRepository('SesileUserBundle:User')->findOneBy(array('apitoken' => $request->headers->get('token'), 'apisecret' => $request->headers->get('secret')));;
        $array = array();
        $array['id'] = $entity->getId();
        $array['username'] = $entity->getUsername();
        $array['email'] = $entity->getEmail();
        $array['prenom'] = $entity->getPrenom();
        $array['nom'] = $entity->getNom();


        return $array;


    }


    /**
     * Cette méthode permet de récupérer la liste des utilisateurs de la collectivité de l'utilisateur courant
     *
     *
     * @var Request $request
     * @return array
     * @Route("/all")
     * @Rest\View()
     * @Method("get")
     *
     *
     * @param ParamFetcher $param
     *
     * @ApiDoc(
     *  resource=false,
     *  description="Permet de récupérer la liste des utilisateurs de la collectivité de l'utilisateur courant. "
     * )
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('SesileUserBundle:User')->findOneBy(array('apitoken' => $request->headers->get('token'), 'apisecret' => $request->headers->get('secret')));

        //pour compatibilité on a pas d'autre option que prendre la premiere collectivite du user.
        //@todo controller si les editeurs utilisent ce endpoint
        $users = array();
        if(count($user->getCollectivities()) > 0 && $user->getCollectivities()->first() instanceof Collectivite) {
            $collectivite = $user->getCollectivities()->first();
            $users = $em->getRepository('SesileUserBundle:User')->getUsersByCollectivityId($collectivite->getId());
        }

        return $users;

    }

    /**
     * Cette méthode permet de récupérer les groupes ayant accès au type de classeur spécifié
     *
     *
     * @var Request $request
     * @return array
     * @Route("/services/types/{type}")
     * @Rest\View()
     * @Method("get")
     *
     *
     * @param ParamFetcher $param
     *
     * @ApiDoc(
     *  resource=false,
     *  description="Permet de récupérer la liste des services organisationnels ayant accès à un type de classeur",
     *  requirements={
     *      {"name"="type", "dataType"="integer", "description"="id du type de classeur"}
     *  }
     * )
     */
    public function getServicesOrganisationnelsAction(Request $request, $type)
    {
        $em = $this->getDoctrine()->getManager();


        $type = $em->getRepository('SesileClasseurBundle:TypeClasseur')->findOneById($type);

        $groupes = $type->getGroupes();
        $tabGroupes = array();
        foreach ($groupes as $groupe) {
            $tabGroupes[] = array('id' => $groupe->getId(), 'nom' => $groupe->getNom());
        }

        return $tabGroupes;
    }

    /**
     * Cette méthode permet de récupérer les groupes auxquels a accès l'utilisateur dont l'email est resenseigné
     *
     *
     * @var Request $request
     * @return array
     * @Route("/services/{email}")
     * @Rest\View()
     * @Method("get")
     *
     *
     * @param ParamFetcher $param
     *
     * @ApiDoc(
     *  resource=false,
     *  description="Permet de récupérer ma liste des services organisationnels ayant accès à un type de classeur",
     *  requirements={
     *      {"name"="email", "dataType"="string", "description"="email de l'utilisateur"}
     *  }
     * )
     */
    public function getServicesOrganisationnelsForUserAction(Request $request, $email)
    {
        //@todo refactor!!!!

        $em = $this->getDoctrine()->getManager();
        $tabGroupes = array();
        $theUser = $em->getRepository('SesileUserBundle:User')->findOneByEmail($email);
        //@todo jamais un findAll!
        $groupes = $em->getRepository('SesileUserBundle:Groupe')->findAll();

        foreach ($groupes as $groupe) {
            $users = array();
            $usersId = array();
            $etapesGroupes = $groupe->getEtapeGroupes();
            foreach ($etapesGroupes as $etapeGroupe) {
                $users = array_merge($users, $etapeGroupe->getUsers()->toArray());

                $usersPacks = $etapeGroupe->getUserPacks();
                foreach ($usersPacks as $usersPack) {
                    $users = array_merge($users, $usersPack->getUsers()->toArray());
                }
            }
            foreach ($users as $user) {
                $usersId[] = $user->getId();
            }
            $usersId = array_unique($usersId);
            if (in_array($theUser->getId(), $usersId)) {
                // Ajout des types de classeur associés au SO
                $typeClasseur = array();
                foreach ($groupe->getTypes() as $type) {
                    $typeClasseur[] = $type->getId();
                }

                $tabGroupes[] = array('id' => $groupe->getId(), 'nom' => $groupe->getNom(), 'type_classeur' => $typeClasseur);
            }
        }


        return $tabGroupes;
    }
}
