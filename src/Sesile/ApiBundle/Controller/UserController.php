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
     * ACTUELLEMENT INDISPONNIBLE - Cette méthode permet de récupérer la liste des utilisateurs de la collectivité de l'utilisateur courant
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
     *  description="ACTUELLEMENT INDISPONNIBLE - Permet de récupérer la liste des utilisateurs de la collectivité de l'utilisateur courant. "
     * )
     */
    public function indexAction(Request $request)
    {


        $em = $this->getDoctrine()->getManager();


        $entity = $em->getRepository('SesileUserBundle:User')->findAll();
        $users = array();
        foreach ($entity as $e) {
            $array = array();
            $array['id'] = $entity->getId();
            $array['username'] = $entity->getUsername();
            $array['email'] = $entity->getEmail();
            $array['prenom'] = $entity->getPrenom();
            $array['nom'] = $entity->getNom();
            $users[] = $array;

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
     *  description="Permet de récupérer la liste des services organisationnels ayant accès à un type de classeur"
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
     *  description="Permet de récupérer ma liste des services organisationnels ayant accès à un type de classeur"
     * )
     */
    public function getServicesOrganisationnelsForUserAction(Request $request, $email)
    {


        $em = $this->getDoctrine()->getManager();
        $tabGroupes = array();
        $user = $em->getRepository('SesileUserBundle:User')->findOneByEmail($email);
        $users = array();
        $usersId = array();

        $groupes = $em->getRepository('SesileUserBundle:Groupe')->findAll();

        foreach ($groupes as $groupe) {
            $etapesGroupes = $groupe->getEtapeGroupes();
            foreach ($etapesGroupes as $etapesGroupe) {
                $users = array_merge($users, $etapesGroupe->getUsers()->toArray());

                $usersPacks = $etapesGroupe->getUserPacks();
                foreach ($usersPacks as $usersPack) {
                    $users = array_merge($users, $usersPack->getUsers()->toArray());
                }
            }
            foreach ($users as $user) {
                $usersId[] = $user->getId();
            }
            $usersId = array_unique($usersId);
            if (in_array($user->getId(), $usersId)) {
                $tabGroupes[] = array('id' => $groupe->getId(), 'nom' => $groupe->getNom());
            }
        }


        return $tabGroupes;

        /*
                $em = $this->getDoctrine()->getManager();



                $usergroups = $em->getRepository('SesileUserBundle:UserGroupe')->findByUser($user);
                foreach ($usergroups as $ugroup) {
                    $groupe = $ugroup->getGroupe();
                    $tabGroupes[] = array('id' => $groupe->getId(), 'nom' => $groupe->getNom());
                }
                return $tabGroupes;
        */
    }
}
