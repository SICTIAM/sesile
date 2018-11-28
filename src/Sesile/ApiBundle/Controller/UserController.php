<?php
namespace Sesile\ApiBundle\Controller;

use phpDocumentor\Reflection\Types\Array_;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\MainBundle\Entity\CollectiviteOzwillo;
use Sesile\UserBundle\Entity\Groupe;
use Sesile\UserBundle\Entity\User;
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
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;


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
     *  description="Permet de récupérer ma liste des services organisationnels ayant accès à un type de classeur. DEPRICATED! USE: api/user/{userEmail}/org/{SIREN}/circuits",
     *  requirements={
     *      {"name"="email", "dataType"="string", "description"="email de l'utilisateur"}
     *  }
     * )
     */
    public function getServicesOrganisationnelsForUserAction(Request $request, $email)
    {
        $message = sprintf('ENDPOINT DEPRICATED. USE:  /api/user/%s/org/%s/circuits', $email, 'SIREN');
        return new JsonResponse($message, Response::HTTP_MOVED_PERMANENTLY);
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

    /**
     * Cette méthode permet de récupérer les circuit des validation dans les quelles l'utilisateurs est present, d'un
     * collectivité specifique
     *
     *
     * @var Request $request
     * @return JsonResponse
     * @Route("/{email}/org/{siren}/circuits")
     * @Rest\View()
     * @Method("get")
     *
     *
     * @param ParamFetcher $param
     *
     * @ApiDoc(
     *  resource=false,
     *  description="Permet de récupérer la liste des circuits organisationnels par utilisateur at par colelctivité/organisation",
     *  requirements={
     *      {"name"="sirent", "dataType"="string", "description"="Siren identifiant de neuf chiffres attribué à l'organisation/collectivité.ex: 123456789"},
     *      {"name"="email", "dataType"="string", "description"="email de l'utilisateur"}
     *  }
     * )
     */
    public function getCircuitByCollectiviteAndUserAction(Request $request, $email, $siren)
    {
        $result = $this->get('collectivite.manager')->getCollectiviteBySiren($siren);
        if (false === $result->isSuccess() || $result->getData() == null) {
            return new JsonResponse("No Organisation found with the given SIREN", Response::HTTP_NOT_FOUND);
        }
        $collectivite = $result->getData();
        $result = $this->get('circuit.manager')->getCircuitDataByUserAndCollectivite($email, $collectivite);
        if (false === $result->isSuccess()) {

            return new JsonResponse("An Error occurred, could not get requested data", Response::HTTP_SERVICE_UNAVAILABLE);
        }
        return new JsonResponse($result->getData(), Response::HTTP_OK);
    }


    /**
     * Cette méthode permet de créer un nouvelle utilisateur ou de l'ajouter à une collectivité si celui-ci existe déjà
     * (à condition que la colléctivité (organisation) soit déjà instancier dans Ozwillo)
     *
     * @var Request $request
     * @return JsonResponse
     * @Route("s/ozwillo/{userOzwilloId}")
     * @Rest\View()
     * @Method("POST")
     *
     *
     * @param ParamFetcher $param
     *
     * @ApiDoc(
     *  resource=false,
     *  description="Cette méthode permet de créer un nouvelle utilisateur ou de l'ajouter à une collectivité si celui-ci existe déjà",
     *  requirements={
     *      {"name"="userOzwilloId", "dataType"="integer", "user id"}
     *  },
     *  parameters={
     *      {"name"="instance_id", "dataType"="string", "required"=true, "Ozwillo application instance id"},
     *      {"name"="client_id", "dataType"="string", "required"=true, "description"="used for authentication purposes"},
     *      {"name"="organization", "dataType"="object", "required"=true, "a description of the organization, containing at least Ozwillo organization id and name"},
     *      {"name"="user", "dataType"="object", "required"=true, "user"}
     *  }
     * )
     */
    public function createNewUserOrAddItToCollectivityFromOzwilloAction(Request $request, $userOzwilloId) {
        $validator = Validation::createValidator();

        $constraint = new Assert\Collection(array(
            'instance_id' => new Assert\Uuid(),
            'client_id' => new Assert\Uuid(),
            'organization' => new Assert\Collection(array(
                'id' => new Assert\Uuid(),
                'name' => new Assert\Optional()
            )),
            'user' => new Assert\Collection(array(
                'email_address' => new Assert\Email(),
                'family_name' => new Assert\Optional(),
                'given_name' => new Assert\Optional(),
                'gender' => new Assert\Optional(),
                'phone_number' => new Assert\Optional()
            ))
        ));

        $violations = $validator->validate($request->request->all(), $constraint);

        if($violations->count() > 0) {
            $messageViolations =  array_map(function ($violation) {return $violation->getMessage() . " " . $violation->getInvalidValue();}, iterator_to_array($violations));
            $this->get('logger')->error(sprintf("StatusCode : %s, Errors : %s", Response::HTTP_BAD_REQUEST, $messageViolations));
            return new JsonResponse($messageViolations, Response::HTTP_BAD_REQUEST);
        }

        $result = $this->get('collectivite.manager')->getOzwilloCollectivityByClientId($request->request->get('client_id'));
        if (false === $result->isSuccess() || $result->getData() == null) {
            return new JsonResponse(sprintf('No Collectivity found with the given Client_id %s', $request->request->get('client_id')), Response::HTTP_NOT_FOUND);
        }
        $collectivteOzwillo = $result->getData();

        if(!$collectivteOzwillo instanceof CollectiviteOzwillo && $collectivteOzwillo->getOrganizationId() !== $request->request->get('organization')["id"]) {
            return new JsonResponse(sprintf('The organization id don\'t match  %s with organization id of collectivity', $request->request->get('organization')["id"]), Response::HTTP_NOT_ACCEPTABLE);
        }

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('SesileUserBundle:User')->findOneByEmail($request->request->get('user')['email_address']);
        if(!$user instanceof User) {
            $user = $em->getRepository('SesileUserBundle:User')->addOzwilloUser($collectivteOzwillo->getCollectivite(), $request->request->get('user'), $request->request->get('organization'), $userOzwilloId);
            $this->get('logger')->info('New user {email} created', array('email' => $user->getEmail()));
            return new JsonResponse('', Response::HTTP_CREATED);
        } else  {
            if ($this->get('collectivite.manager')->userHasOzwilloCollectivity($user->getId(), $request->request->get('client_id'))->getData()) {
                return new JsonResponse(sprintf('The user %s exist and already have collectivity with ozwillo client_id %s', $user->getEmail(), $request->request->get('client_id')), Response::HTTP_CONFLICT);
            } else {
                $em->getRepository('SesileUserBundle:User')->addCollectiviteToUser($user, $collectivteOzwillo->getCollectivite());
                $this->get('logger')->info('Collectivity {collectiviteId} added to user {email}', array('email' => $user->getEmail(), 'collectiviteId' => $collectivteOzwillo->getCollectivite()->getId()));
                return new JsonResponse('', Response::HTTP_OK);
            }
        }
    }
}
