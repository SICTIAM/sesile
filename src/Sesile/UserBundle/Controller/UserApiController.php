<?php

namespace Sesile\UserBundle\Controller;

use Sesile\MainBundle\Entity\Collectivite;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sesile\UserBundle\Entity\User;
use Sesile\UserBundle\Form\UserType;
use Sesile\UserBundle\Form\UserEditType;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcher;

/**
 * @Rest\Route("/apirest/user", options = { "expose" = true })
 */
class UserApiController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @Rest\View(serializerGroups={"currentUser"})
     * @Rest\Get("/current")
     */
    public function getCurrentAction() {
        $user = $this->getUser();
        $user->setOzwillo($this->container->getParameter('ozwillo_portal'));
        $token = $this->container->get('security.token_storage')->getToken();
        if ($token->hasAttribute('orgId')){
            $user->setCurrentOrgId($token->getAttribute('orgId'));
        }
        return $user;
    }

    /**
     * @Rest\View()
     * @Rest\Get("/ozwillo/{id}")
     * @ParamConverter("Collectivite", options={"mapping": {"id": "id"}})
     * @param Collectivite $collectivite
     * @return array
     */
    public function ozwilloAction(Collectivite $collectivite) {
        return $this->getOzwilloUsers($collectivite);
    }

    /**
     * @Rest\View(serializerGroups={"listUsers"})
     * @Rest\Get("s/{id}")
     * @ParamConverter("Collectivite", options={"mapping": {"id": "id"}})
     * @param Collectivite $collectivite
     * @return array|\Doctrine\Common\Collections\Collection
     *
     * @todo à valider
     */
    public function usersCollectiviteAction(Collectivite $collectivite)
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') || $this->getUser()->getCollectivities()->contains($collectivite)) {
            return $collectivite->getUsers();
        }
//        else {
            //@todo refactor $this->getUser()->getCollectivite()
//            return $this->getUser()->getCollectivite()->getUsers();
//        }
        return [];
    }


    /**
     * @Rest\View(serializerGroups={"listUsers"})
     * @Rest\Get("s-select/{id}")
     * @ParamConverter("Collectivite", options={"mapping": {"id": "id"}})
     * @param Collectivite $collectivite
     * @return array|\Doctrine\Common\Collections\Collection
     *
     * @todo à valider
     */
    public function usersCollectiviteSelectAction(Collectivite $collectivite)
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') || $this->getUser()->getCollectivities()->contains($collectivite)) {
            $users = $collectivite->getUsers();
        } else {
            return [];
        }
        /**
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $users = $collectivite->getUsers();
        } else {
            $users = $this->getUser()->getCollectivite()->getUsers();
        }
         *
         */

        $users_select = [];
        foreach ($users as $user) {
            $users_select[] = array(
                'label' => $user->getPrenom() . " " . $user->getNom(),
                'value' => $user->getId()
            );
        }

        return $users_select;
    }

    /**
     * @Rest\View()
     * @Rest\Get("/roles")
     * @return object|\Symfony\Component\Security\Core\Role\RoleHierarchy
     */
    public function getRoles() {
        $roles = array();
        foreach ($this->getParameter('security.role_hierarchy.roles') as $key => $value) {
            $roles[] = $key;

            foreach ($value as $value2) {
                $roles[] = $value2;
            }
        }

        return array_unique($roles);
    }

    /**
     * @Rest\View()
     * @Rest\Get("/certificate")
     */
    public function getCertificate() {
        $request = Request::createFromGlobals()->server;

        if ($request->get('HTTP_X_SSL_CLIENT_SHA1')) {

            $certificate = array();
            $certificate['HTTP_X_SSL_CLIENT_M_SERIAL'] = $request->get('HTTP_X_SSL_CLIENT_M_SERIAL');
            $certificate['HTTP_X_SSL_CLIENT_I_DN'] = $request->get('HTTP_X_SSL_CLIENT_I_DN');
            $certificate['HTTP_X_SSL_CLIENT_S_DN_CN'] = $request->get('HTTP_X_SSL_CLIENT_S_DN_CN');
            $certificate['HTTP_X_SSL_CLIENT_S_DN_O'] = $request->get('HTTP_X_SSL_CLIENT_S_DN_O');
            $certificate['HTTP_X_SSL_CLIENT_S_DN_OU'] = $request->get('HTTP_X_SSL_CLIENT_S_DN_OU');
            $certificate['HTTP_X_SSL_CLIENT_S_DN_EMAIL'] = $request->get('HTTP_X_SSL_CLIENT_S_DN_EMAIL');
            $certificate['HTTP_X_SSL_CLIENT_I_DN_CN'] = $request->get('HTTP_X_SSL_CLIENT_I_DN_CN');
            $certificate['HTTP_X_SSL_CLIENT_I_DN_O'] = $request->get('HTTP_X_SSL_CLIENT_I_DN_O');
            $certificate['HTTP_X_SSL_CLIENT_I_DN_EMAIL'] = $request->get('HTTP_X_SSL_CLIENT_I_DN_EMAIL');

            $startDate = $this->convert_date_certificate($request->get('HTTP_X_SSL_CLIENT_NOT_BEFORE'));
            $endDate = $this->convert_date_certificate($request->get('HTTP_X_SSL_CLIENT_NOT_AFTER'));
            $certificate['HTTP_X_SSL_CLIENT_NOT_BEFORE'] = $startDate;
            $certificate['HTTP_X_SSL_CLIENT_NOT_AFTER'] = $endDate;

            return $certificate;
        } else {
            return false;
        }
    }

    /**
     * @return array
     * @Rest\View()
     * @Rest\Get("s")
     *
     * @todo valider que ceci n'est pas utilisé
     */
    public function listAction()
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->getDoctrine()->getManager()->getRepository('SesileUserBundle:User')->findAll();
        }
        else if($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            //@todo refactor $this->getUser()->getCollectivite()
            return $this->getDoctrine()->getManager()->getRepository('SesileUserBundle:User')->findByCollectivite($this->getUser()->getCollectivite()->getId());
        }
        else {
            return array();
        }
    }

    /**
     * @return array
     * @Rest\View(serializerGroups={"searchUser"})
     * @Rest\Get("/search")
     * @QueryParam(name="value")
     * @QueryParam(name="collectiviteId")
     */
     public function findByNomOrPrenomAction(ParamFetcher $paramFetcher)
     {
         //TODO: return just actif users
         $value = $paramFetcher->get('value');
         $collectiviteId = $paramFetcher->get('collectiviteId');
         if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') || $this->getUser()->hasCollectivity($collectiviteId)) {
            return $this->getDoctrine()->getManager()->getRepository('SesileUserBundle:User')->findByNameOrFirstName($value, $collectiviteId);
         }
         throw $this->createNotFoundException('No Users Found For Collectivity');
     }

    /**
     * @Rest\View(serializerGroups={"UserId"})
     * @Rest\Get("/{id}")
     * @ParamConverter("User", options={"mapping": {"id": "id"}})
     * @param User $user
     * @return User
     * @internal param $id
     */
    public function getAction(User $user)
    {
        return $user;
    }

    /**
     * @Rest\View("statusCode=Response::HTTP_CREATED")
     * @Rest\Post("/new/{id}/all")
     * @ParamConverter("Collectivite", options={"mapping": {"id": "id"}})
     * @param Collectivite $collectivite
     * @return User|JsonResponse
     */
    public function postUsersAction(Collectivite $collectivite)
    {
        $users = $this->getOzwilloUsers($collectivite);
        foreach ($users as $user) {
            $em = $this->getDoctrine()->getManager();
            $em->getRepository('SesileUserBundle:User')->addUser($collectivite, $user['user_email_address'], $user['user_name']);
            $em->flush();
        }

        return $this->getOzwilloUsers($collectivite);
    }

    /**
     * @Rest\View("statusCode=Response::HTTP_CREATED")
     * @Rest\Post("/new/{id}")
     * @ParamConverter("Collectivite", options={"mapping": {"id": "id"}})
     * @param Request $request
     * @param Collectivite $collectivite
     * @return User|JsonResponse
     */
    public function postUserAction(Request $request, Collectivite $collectivite)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->getRepository('SesileUserBundle:User')->addUser($collectivite, $request->request->get('email'), $request->request->get('username'));
            $em->flush();

            return new JsonResponse('Utilisateur créé', Response::HTTP_ACCEPTED);
        }
        else {
            return new JsonResponse('Impossiblde de créer l\'utilisateur', Response::HTTP_NOT_MODIFIED);
        }
    }


    /**
     * @Rest\View(serializerGroups={"UserId"})
     * @Rest\Delete("/{id}")
     * @ParamConverter("User", options={"mapping": {"id": "id"}})
     * @param User $user
     * @return JsonResponse
     * @internal param $id
     *
     * @todo must refactor this method. The user can't be deleted. If multi collectivie must remove from collectivity maybe?
     * @todo add the collectivityID parameter
     */
    public function removeAction(User $user)
    {
        //@todo refactor $this->getUser()->getCollectivite()
        if ($user
            && ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')
            || $this->getUser()->getCollectivite() == $user->getCollectivite())
        ) {
            $em = $this->getDoctrine()->getManager();
            $dirPath = $this->getParameter('upload')['path'];

            if ($user === $this->getUser()) {
                return new JsonResponse([
                    'status'    => 'error',
                    'message'   => 'Suppression impossible : vous ne pouvez pas vous supprimer vous-même'],
                    Response::HTTP_FORBIDDEN);
            }

            if ($em->getRepository('SesileUserBundle:User')->isUserInClasseurs($user)) {
                return new JsonResponse([
                    'status'    => 'error',
                    'message'   => 'Suppression impossible : utilisateur présent dans un ou plusieurs classeurs'],
                    Response::HTTP_FORBIDDEN);
            }

            if ($user->getPathSignature()) {
                $user->removeUploadSignature($this->getParameter('upload')['signatures']);
            }
            if ($user->getPath()) {
                $user->removeUpload($dirPath);
            }
            $em->remove($user);
            $em->flush();

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Utilisateur supprimé'
            ],
                Response::HTTP_ACCEPTED);
        }
    }


    /**
     * @Rest\View(serializerGroups={"UserId"})
     * @Rest\Post("/avatar/{id}")
     * @param Request $request
     * @param User $user
     * @return User|\Symfony\Component\Form\Form|JsonResponse
     * @ParamConverter("User", options={"mapping": {"id": "id"}})
     */
    public function uploadAvatarAction(Request $request, User $user) {

        if (empty($user)) {
            return new JsonResponse(['message' => 'Utilisateur inexistant'], Response::HTTP_NOT_FOUND);
        }

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('SesileUserBundle:User')->uploadFile(
            $request->files->get('path'),
            $user,
            $this->getParameter('upload')['path']
        );

        $em->persist($user);
        $em->flush();

        return $user;

    }

    /**
     * @Rest\View(serializerGroups={"UserId"})
     * @Rest\Delete("/avatar_remove/{id}")
     * @ParamConverter("User", options={"mapping": {"id": "id"}})
     * @param User $user
     * @return User
     * @internal param $id
     */
    public function deleteAvatarAction(User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $user->removeUpload($this->getParameter('upload')['path']);
        $user->setPath("");
        $em->flush();

        return $user;
    }

    /**
     * @Rest\View(serializerGroups={"UserId"})
     * @Rest\Post("/signature/{id}")
     * @param Request $request
     * @param User $user
     * @return User|\Symfony\Component\Form\Form|JsonResponse
     * @ParamConverter("User", options={"mapping": {"id": "id"}})
     */
    public function uploadSignatureAction(Request $request, User $user) {

        if (empty($user)) {
            return new JsonResponse(['message' => 'Utilisateur inexistant'], Response::HTTP_NOT_FOUND);
        }

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('SesileUserBundle:User')->uploadSignatureFile(
            $request->files->get('signatures'),
            $user,
            $this->getParameter('upload')['signatures']
        );

        $em->persist($user);
        $em->flush();

        return $user;

    }

    /**
     * @Rest\View(serializerGroups={"UserId"})
     * @Rest\Delete("/signature_remove/{id}")
     * @ParamConverter("User", options={"mapping": {"id": "id"}})
     * @param User $user
     * @return User
     * @internal param $id
     */
    public function deleteSignatureAction(User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $user->removeUploadSignature($this->getParameter('upload')['signatures']);
        $user->setPathSignature("");
        $em->flush();

        return $user;
    }

    /**
     * @Rest\View(serializerGroups={"UserId"})
     * @Rest\Put("/{id}")
     * @param Request $request
     * @param User $user
     * @return User|\Symfony\Component\Form\Form|JsonResponse
     * @ParamConverter("User", options={"mapping": {"id": "id"}})
     */
    public function updateUserAction(Request $request, User $user)
    {
        if (empty($user)) {
            return new JsonResponse(['message' => 'Utilisateur inexistant'], Response::HTTP_NOT_FOUND);
        }
        $form = $this->createForm(UserEditType::class, $user);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->merge($user);
            $em->flush();

            return $user;
        }
        else {
            return new JsonResponse(['message' => 'Impossible de mettre à jour l\'utilisateur'], Response::HTTP_NOT_MODIFIED);
        }
    }


    /**
     * Convertit la date du haproxy au format DateTime
     * @param $date
     * @return bool|\DateTime
     */
    private function convert_date_certificate($date) {
        return $validDate = \DateTime::createFromFormat('ymdHisT', $date);
    }

    /**
     * @param Collectivite $collectivite
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @todo La url de ozwillo est en dur! must refactor
     */
    private function getOzwilloUsers(Collectivite $collectivite) {

        $instanceId = $collectivite->getOzwillo()->getInstanceId();
        $accessToken = $this->get('security.token_storage')->getToken()->getAccessToken();

        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'https://kernel.ozwillo-preprod.eu/apps/acl/instance/' . $instanceId,
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                ],
            ]
        );

        $ozwilloUsers = json_decode($response->getBody(), true);
        $users = $collectivite->getUsers();
        $usersImport = [];

        foreach ($ozwilloUsers as $ozwilloUser) {
            if (!in_array($ozwilloUser['user_email_address'], $users->toArray())) {
                $usersImport[] = $ozwilloUser;
            }
        }

        return $usersImport;
    }

}
