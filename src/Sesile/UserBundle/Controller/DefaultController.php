<?php

namespace Sesile\UserBundle\Controller;

use Sesile\UserBundle\Entity\User;
use Sesile\UserBundle\Entity\UserPack;
use Sesile\UserBundle\Entity\UserRole;
use Sesile\UserBundle\Form\UserRoleType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;
use Sesile\UserBundle\Form\UserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="liste_users")
     * @Template("SesileUserBundle:Default:index.html.twig")
     */
    public function listAction() {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $userManager = $this->get('fos_user.user_manager');
            $users = $userManager->findUsers();
        }
        else if($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $em = $this->getDoctrine()->getManager();
            $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->find($this->get('session')->get("collectivite"));
            $users = $collectivite->getUsers();
        }
        else {
            return $this->render('SesileMainBundle:Default:errorrestricted.html.twig');
        }

        return array(
            "users" => $users,
            "menu_color" => "vert"
        );
    }

    /**
     * @Route("/creation/", name="ajout_user")
     * @Template("SesileUserBundle:Default:ajout.html.twig")
     */
    public function ajoutAction(Request $request) {
        $upload = $this->container->getParameter('upload');
        $DirPath = $upload['path'];

        $LdapInfo = $this->container->getParameter('ldap');

        $entity = new User();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        //connexion au serveur LDAP

        $cas_server = $this->container->getParameter('cas_server');

        $ldapconn = ldap_connect($cas_server) or die("Could not connect to LDAP server."); // security
        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

        if ($ldapconn) {
            //binding au serveur LDAP
            @ldap_bind($ldapconn, $LdapInfo["dn_admin"], $LdapInfo["password"]);

            if ($form->isValid()) {

                $em = $this->getDoctrine()->getManager();

                $userObj = $em->getRepository('SesileUserBundle:User')->findOneByUsername($form->get('username')->getData());

                if (empty($userObj)) {
                    $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));
                    if (!$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
                        $entity->setCollectivite($collectivite);
                    }
                    $entity->setEmail($form->get('username')->getData());
                    $em->persist($entity);
                    $entity->upload($DirPath);


                    $res = array("nom" => $form->get('Nom')->getData(),
                        "prenom" => $form->get('Prenom')->getData(),
                        "email" => $form->get('username')->getData(),
                        "plainpassword" => $form->get('plainPassword')->getData());


                    //création du tableau d'attributs
                    $entry["objectClass"][0] = "inetOrgPerson";
                    $entry["objectClass"][1] = "organizationalPerson";
                    $entry["objectClass"][2] = "person";
                    $entry["objectClass"][3] = "shadowAccount";
                    $entry["cn"] = $res["email"];
                    $entry["sn"] = $res["email"];
                    $entry["userPassword"] = "{MD5}" . base64_encode(pack('H*', md5($res["plainpassword"])));
                    $entry["givenName"] = $res["email"];
                    $entry["shadowInactive"] = -1;
                    $entry["uid"] = "100";
                    $entry["displayName"] = $res["nom"] . " " . $res["prenom"];

                    //création du Distinguished Name
                    $dn = "mail=" . $res["email"] . "," . $LdapInfo["dn_user"];
                    $justthese = array("sn", "givenname", "mail");
                    $sr = ldap_search($ldapconn, $LdapInfo["dn_user"], "(|(mail=" . $res["email"] . "*))");

                    $info = ldap_get_entries($ldapconn, $sr);

                    if (!$info["count"]) {
                        ldap_add($ldapconn, "mail=" . $res["email"] . "," . $LdapInfo["dn_user"], $entry);
                    }
                    ldap_close($ldapconn);

                    $em->flush();

                    // modification des roles
                    // C est moche mais ca marche : je ne sais pas pouquoi le user n est pas prise lors de l insertion en BDD
                    $rolesUsers = $em->getRepository('SesileUserBundle:UserRole')->findByUser(null);
                    foreach ($rolesUsers as $rolesUser) {
                        $rolesUser->setUser($entity);
                    }
                    $em->flush();
                    // fin de la modification des roles

                } else {
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        'l\'utilisateur existe déjà'
                    );
                    return $this->redirect($this->generateUrl('ajout_user'));
                }
                $this->get('session')->getFlashBag()->add(
                    'success',
                    "L'utilisateur a bien été enregistré"
                );
                return $this->redirect($this->generateUrl('liste_users', array('id' => $entity->getId())));
            }
        }

        return array (
            'entity' => $entity,
            'form' => $form->createView(),
            "menu_color" => "vert"
        );
    }

    /**
     * Displays a form to edit an existing user entity.
     * @Route("/edit/{id}/", name="user_edit", options={"expose"=true})
     * @ParamConverter("User", options={"mapping": {"id": "id"}})
     * @Method("GET")
     * @Template()
     * @param User $user
     * @return array
     */
    public function editAction(User $user)
    {
        $editForm = $this->createEditForm($user);
        $deleteForm = $this->createDeleteForm($user->getId());

        return array(
            'entity' => $user,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            "menu_color" => "vert"
        );
    }

    /**
     * Displays a form to edit an existing user entity.
     * @Route("/certificate/{id}/", name="user_certificate")
     * @ParamConverter("User", options={"mapping": {"id": "id"}})
     * @Template()
     * @param User $user
     * @return array
     */
    public function certificateAction(User $user)
    {
        $request = Request::createFromGlobals();
        $certificateValue = $this->cas_ssl_infos($request->server->get('HTTP_X_SSL_CLIENT_M_SERIAL'),$request->server->get('HTTP_X_SSL_CLIENT_I_DN'));
        $startDate = $this->convert_date_certificate($request->server->get('HTTP_X_SSL_CLIENT_NOT_BEFORE'));
        $endDate = $this->convert_date_certificate($request->server->get('HTTP_X_SSL_CLIENT_NOT_AFTER'));
        $physicaldeliveryofficename = $this->getUserInfosFromCas($user, "physicaldeliveryofficename");

        $saveForm = $this->certificateAppairForm($user->getId());
        $removeForm = $this->certificateDeleteForm($user->getId());

        return array(
            'user'              => $user,
            'certificatevalue'  => $certificateValue,
            'startDate'         => $startDate,
            'endDate'           => $endDate,
            'certifCAS'         => $physicaldeliveryofficename,
            'save_form'         => $saveForm->createView(),
            'remove_form'       => $removeForm->createView(),
            "menu_color"        => "vert"
        );
    }

    /**
     * Add certificate to user.
     * @Route("/certificate_appair/{id}", name="certificate_appair")
     * @ParamConverter("User", options={"mapping": {"id": "id"}})
     * @param Request $request
     * @param User $user
     * @return RedirectResponse
     */
    public function certificateAppairAction (Request $request, User $user) {

        $form = $this->certificateAppairForm($user->getId());
        $form->handleRequest($request);
        if ($form->isValid()) {

            $request = Request::createFromGlobals();
            $certificateValue = $this->cas_ssl_infos($request->server->get('HTTP_X_SSL_CLIENT_M_SERIAL'),$request->server->get('HTTP_X_SSL_CLIENT_I_DN'));

            if($this->setUserInfosInCas($user, "physicaldeliveryofficename", $certificateValue)) {
                $this->addFlash(
                    'success',
                    "Certificat appairé pour " . $user->getPrenom() . " " . $user->getNom()
                );
            } else {
                $this->addFlash(
                    'error',
                    "Impossible d'appairer le certificat"
                );
            }
        }

        return $this->redirect($this->generateUrl('user_certificate', array('id' => $user->getId())));
    }

    /**
     * Remove certificate to user.
     * @Route("/certificate_delete/{id}", name="certificate_delete")
     * @ParamConverter("User", options={"mapping": {"id": "id"}})
     * @param Request $request
     * @param User $user
     * @return RedirectResponse
     */
    public function certificateRemoveAction(Request $request, User $user) {

        $form = $this->certificateDeleteForm($user->getId());
        $form->handleRequest($request);
        if($form->isValid()) {
            if ($this->removeUserInfosInCas($user, "physicaldeliveryofficename")) {
                $this->addFlash(
                    'success',
                    "Certificat désappairé pour " . $user->getPrenom() . " " . $user->getNom()
                );
            } else {
                $this->addFlash(
                    'error',
                    "Impossible de désappairer ce certificat"
                );
            }
        }

        return $this->redirect($this->generateUrl('user_certificate', array('id' => $user->getId())));

    }


    /**
     * Update an existing User entity.
     * @Route("/{id}", name="user_update")
     * @Method("PUT")
     * @Template("SesileUserBundle:Default:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        //var_dump($request->request->get('sesile_userbundle_user')['userRole']); die();
        $upload = $this->container->getParameter('upload');

        $DirPath = $upload['path'];
        $DirPathSign = $upload['signatures'];
        $cas = $this->getCASParams();

        $LdapInfo = $this->container->getParameter('ldap');
        $cas_server = $this->container->getParameter('cas_server');

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SesileUserBundle:User')->find($id);

        // On recupere le username car il ne doit pas etre modifié -> il est bloqué en front
        $userName = $entity->getUsername();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }
        $ExValues = array("mail" => $userName,
            "Nom" => $entity->getNom(),
            "Prenom" => $entity->getPrenom()
        );

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        // Suppression de tous les roles avant de les rajouter
        $userRoles = $em->getRepository('SesileUserBundle:UserRole')->findByUser($entity);
        foreach ($userRoles as $userRole) {
            $em->remove($userRole);
        }
        $em->flush();

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $ldapconn = ldap_connect($cas_server) or die("Connexion impossible au serveur LDAP [".$cas_server."]"); //security
            ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

            if ($ldapconn) {
                //binding au serveur LDAP

                if (ldap_bind($ldapconn, $LdapInfo["dn_admin"], $LdapInfo["password"])) {
                    $entry["cn"] = $userName;
                    $pwd = trim($editForm->get('plainPassword')->getData());

                    if ($pwd) {
                        $entity->setPlainPassword($pwd);
                        $entry["userPassword"] = "{MD5}" . base64_encode(pack('H*', md5($pwd)));
                    }
                    $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));

                    if (!$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
                        $entity->setCollectivite($collectivite);
                    }


//                    $entity->setEmail($editForm->get('username')->getData());
                    $entry["givenName"] = $userName;
                    $entry["displayName"] = $entity->getNom() . ' ' . $entity->getPrenom();

                    //création du Distinguished Name
                    $parent = $LdapInfo["dn_user"];
                    $dn = "mail=" . $ExValues["mail"] . "," . $parent;

//                    var_dump("Username : " . $userName); die();

                    if (ldap_rename($ldapconn, $dn, "mail=" . $userName, $parent, true) && ldap_modify($ldapconn, "mail=" . $userName . "," . $parent, $entry)) {
                        ldap_close($ldapconn);
                        if ($editForm->get('file')->getData()) {
                            // echo "true";exit;
                            if ($entity->getPath()) {
                                $entity->removeUpload($DirPath);
                            }
                            $entity->preUpload();
                            $entity->upload($DirPath);

                        }
                        if ($editForm->get('fileSignature')->getData()) {
                            // echo "true";exit;
                            if ($entity->getPathSignature()) {
                                $entity->removeUploadSignature($DirPathSign);
                            }
                            $entity->preUploadSignature();
                            $entity->uploadSignature($DirPathSign);

                        }
                        //echo "false";exit;
                        $em->persist($entity);
                        $em->flush();
                        $this->get('session')->getFlashBag()->add(
                            'success',
                            "L'utilisateur a bien été modifié"
                        );
                    } else {
                        ldap_close($ldapconn);
//                        echo "pb rename ldap 2";
                        $this->get('session')->getFlashBag()->add(
                            'error',
                            "Problème de CAS avec l'utilisateur"
                        );
//                        exit;
                    }

                    return $this->redirect($this->generateUrl('liste_users', array('id' => $id)));
                } else {
                    ldap_close($ldapconn);
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        "Authentification au serveur LDAP impossible"
                    );
//                    exit("Authentification au serveur LDAP impossible");
                    return $this->redirect($this->generateUrl('liste_users', array('id' => $id)));
                }

            }
        }
        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            "menu_color" => "vert"
        );
    }

    /**
     * Deletes a User entity.
     *
     * @Route("/delete/{id}", name="user_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, $id)
    {
        // On vérifie si l'utitilateur est bien admin
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            // Sinon on déclenche une exception « Accès interdit »
            return $this->render('SesileMainBundle:Default:errorrestricted.html.twig');
        }

        // On vérifie si l'utilisateur qui doit etre supprimé ne soit pas l'utilisateur courant
        if($this->getUser()->getId() == $id) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                'Suppression impossible : vous ne pouvez pas vous supprimer vous-même'
            );
            // Si oui, on redirige et on ne supprime pas l'user
            return $this->redirect($this->generateUrl('liste_users'));
        }

        $em = $this->getDoctrine()->getManager();

        // On vérifie si le user est dans des classeurs
        if ($em->getRepository('SesileUserBundle:User')->isUserInClasseurs($id)) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                'Suppression impossible : utilisateur présent dans un ou plusieurs classeurs'
            );
            // Si oui, on redirige et on ne supprime pas l'user
            return $this->redirect($this->generateUrl('liste_users'));
        }

        // On recupere le user
        $user = $em->getRepository('SesileUserBundle:User')->findOneById($id);

        // Si l utilisateur existe bien
        if($user !== null) {

            // On récupère le dossier des avatar
            $upload = $this->container->getParameter('upload');
            $DirPath = $upload['path'];


            // Si elle a bien un répertoire pour son avatar
            if ($user->getPath()) {
                $user->removeUpload($DirPath);
            }

            // On supprime l'user et on flush
            $em->remove($user);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                'L\'utilisateur a bien été supprimé'
            );
        }
        else {
            $this->get('session')->getFlashBag()->add(
                'warning',
                'L\'utilisateur n\'existe pas.'
            );
        }


        // On redirige vers liste_user
        return $this->redirect($this->generateUrl('liste_users'));
    }



    /**
     *
     *
     * @Route("/groupes/", name="userpacks")
     * @Method("GET")
     * @Template("SesileUserBundle:UserPack:index.html.twig")
     */
    public function userPackIndexAction()
    {


        $em = $this->getDoctrine()->getManager();
        $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));
        $userPacks = $em->getRepository('SesileUserBundle:UserPack')->findByCollectivite($collectivite);
        return array('userPacks'=>$userPacks, "menu_color" => "vert");
    }

    /**
     *
     *
     * @Route("/groupes/new", name="new_userpack")
     * @Method("GET")
     * @Template("SesileUserBundle:UserPack:new.html.twig")
     */
    public function userPackNewAction()
    {
        $em = $this->getDoctrine()->getManager();
        $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));
        $users = $em->getRepository('SesileUserBundle:User')->findBy(array('collectivite' => $collectivite), array('Nom' => 'ASC'));
        return array('users'=>$users, "menu_color" => "vert");

    }

    /**
     *
     *
     * @Route("/groupes/new", name="create_userpack")
     * @Method("POST")
     *
     */
    public function userPackCreateAction(Request $request)
    {
/*
 * On check si le nom est rempli et si on a des users dans le groupe
 * */
        if(!$request->request->get('nom'))
        {
            $this->get('session')->getFlashBag()->add(
                'error',
                'Le groupe doit porter un nom'
            );
            return new RedirectResponse($this->container->get('router')->generate('new_userpack'));
        }

        if(!$request->request->get('valeurs'))
        {
            $this->get('session')->getFlashBag()->add(
                'error',
                'Le groupe doit comporter des utilisateurs'
            );
            return new RedirectResponse($this->container->get('router')->generate('new_userpack'));
        }

        $em = $this->getDoctrine()->getManager();
        $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));
        $testUserPack = $em->getRepository('SesileUserBundle:UserPack')->findBy(array('collectivite'=>$collectivite,'nom'=>$request->request->get('nom') ));

        /*
         * si on trouve un userPack avec ce nom alors il est déjà pris : ça dégage
         * */

        if(count($testUserPack))
        {
            $this->get('session')->getFlashBag()->add(
                'error',
                'Ce nom de groupe existe déjà'
            );
            return new RedirectResponse($this->container->get('router')->generate('new_userpack'));
        }
        /*
         * valeurs contient les ids des users sélectionnés séparés par des virgules
         */

        $idUsers = explode(',',$request->request->get('valeurs'));



        $nom = $request->request->get('nom');

        $em = $this->getDoctrine()->getManager();

        $userPack = new UserPack();
        $userPack->setNom($nom);
        $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));
        $userPack->setCollectivite($collectivite);
        foreach($idUsers as $idUser)
        {
            $user = $em->getRepository('SesileUserBundle:User')->findOneById($idUser);
            $userPack->addUser($user);
        }


        $em->persist($userPack);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            'Le groupe d\'utilisateurs a bien été enregistré'
        );
        return new RedirectResponse($this->container->get('router')->generate('userpacks'));


    }


    /**
     *
     *
     * @Route("/groupes/edit/{id}", name="edit_userpack")
     * @Method("GET")
     * @Template("SesileUserBundle:UserPack:edit.html.twig")
     */
    public function userPackEditAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $userPack = $em->getRepository('SesileUserBundle:UserPack')->findOneById($id);

        $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));
        // $users = $em->getRepository('SesileUserBundle:User')->findByCollectivite($collectivite);
        $users = $em->getRepository('SesileUserBundle:User')->findBy(array('collectivite' => $collectivite), array('Nom' => 'ASC'));

        return array('userPack'=>$userPack,'users'=>$users, "menu_color" => "vert");

    }

    /**
     *
     *
     * @Route("/groupes/modify/{id}", name="modify_userpack")
     * @Method("POST")
     *
     */
    public function userPackModifyAction(Request $request,$id)
    {
        if(!$request->request->get('nom'))
        {
            $this->get('session')->getFlashBag()->add(
                'error',
                'Le groupe doit porter un nom'
            );
            return new RedirectResponse($this->container->get('router')->generate('edit_userpack',array('id'=>$id)));
        }

        if(!$request->request->get('valeurs'))
        {
            $this->get('session')->getFlashBag()->add(
                'error',
                'Le groupe doit comporter des utilisateurs'
            );
            return new RedirectResponse($this->container->get('router')->generate('edit_userpack',array('id'=>$id)));
        }


        $em = $this->getDoctrine()->getManager();
        $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));
        $testUserPack = $em->getRepository('SesileUserBundle:UserPack')->findBy(array('collectivite'=>$collectivite,'nom'=>$request->request->get('nom') ));
/*
 * Si on trouve un userPack à ce nom, on vérifie que c'est pas nous, si ce n'est pas nous on dégage!
 * */
        if(count($testUserPack))
        {
            foreach($testUserPack as $pack)
            {
                if($pack->getId() != $id)
                {
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        'Ce nom de groupe existe déjà'
                    );
                    return new RedirectResponse($this->container->get('router')->generate('edit_userpack',array('id'=>$id)));
                }
            }

        }

        $userPack = $em->getRepository('SesileUserBundle:UserPack')->findOneById($id);
        $userPack->setNom($request->request->get('nom'));
        $idUsers = explode(',',$request->request->get('valeurs'));
        foreach($userPack->getUsers() as $user)
        {
            $userPack->removeUser($user);
        }

        foreach($idUsers as $idUser)
        {
            $user = $em->getRepository('SesileUserBundle:User')->findOneById($idUser);
            $userPack->addUser($user);
        }
        $em->flush();
        $this->get('session')->getFlashBag()->add(
            'success',
            'Le groupe d\'utilisateurs a bien été modifié'
        );
        return new RedirectResponse($this->container->get('router')->generate('userpacks'));

    }

    private function getUserPackSelection($userPack)
    {
        $em = $this->getDoctrine()->getManager();

        $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));
        /*
         * On récupère tout les users de la collectivité et tous ceux qui sont dans le UserPack
         * */
        $usersFromCollectivite = $em->getRepository('SesileUserBundle:User')->findByCollectivite($collectivite);

        $usersFromPack = $userPack->getUsers();

        $tabIdUFC = array();
        $tabIdUFP = array();
        $tabRetour = array();
        /*
         * On crée un tableau où on met les ids des users de la collec
         */
        foreach( $usersFromCollectivite as $ufc)
        {
            $tabIdUFC[] = $ufc->getId();
        }
        /*
         * On crée un tableau où on met les ids des users du Pack
         */
        foreach($usersFromPack as $ufp)
        {
            $tabIdUFP[] = $ufp->getId();
        }

        /*
         * Pour chaque user de la collectivité on regarde s'il est dans le pack et on crée un tableau ou on met ce booléen(dans le pack ou pas)et le user
         * On trasforme ce tableau en objet (plus facile à manipuler dans le twig) et on ajoute ça a un tableau contenant un objet pour chaque user de la collec
         */
        foreach($tabIdUFC as $idUFC)
        {
            if(in_array($idUFC,$tabIdUFP))
            {
                $tabRetour[] = (object)array('inPack'=>true,'user'=>$em->getRepository('SesileUserBundle:User')->findOneById($idUFC));
            }
            else{
                $tabRetour[] = (object)array('inPack'=>false,'user'=>$em->getRepository('SesileUserBundle:User')->findOneById($idUFC));
            }

        }
        return $tabRetour;
    }


    /**
     * Delete an existing userPack.
     *
     * @Route("groupes/delete/{id}", name="delete_userpack")
     * @Method("get")
     */
    public function deleteUserPackAction($id) {

        // On recupere l enregistrement a supprimer
        $em = $this->getDoctrine()->getManager();
        $userPack = $em->getRepository('SesileUserBundle:UserPack')->findOneById($id);

        // On supprime l enregistrement
        $em->remove($userPack);
        $em->flush();

        // on redirige avec un petit message
        $this->get('session')->getFlashBag()->add('success', 'Le groupe d\'utilisateurs a bien été supprimé');
        return $this->redirect($this->generateUrl('userpacks'));
    }

    /**
     * Show users in  an existing userPack.
     *
     * @Route("groupes/show/{id}", name="show_userpack", options={"expose"=true})
     * @Method("get")
     *
     */
    public function getUsersFromUserPackAction($id){

        $em = $this->getDoctrine()->getManager();
        $userPack = $em->getRepository('SesileUserBundle:UserPack')->findOneById($id);
        $users = $userPack->getUsers();

        $tabUsers = array();
        foreach($users as $user)
        {
            $tabUsers[] = $user->getNom().' '.$user->getPrenom();
        }
        sort($tabUsers);

        //var_dump($tabUsers);
        return new JsonResponse($tabUsers);

    }




    /**
     * Creates a form to create a User entity.
     *
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(User $entity)
    {
        $form = $this->createForm(UserType::class, $entity, array(
            'action' => $this->generateUrl('ajout_user'),
            'method' => 'POST',
        ));
        $form->add('plainPassword', RepeatedType::class, array(
            'type' => PasswordType::class,
            'required' => true,
            'options' => array('translation_domain' => 'FOSUserBundle', 'always_empty' => 'true'),
            'first_options' => array('label' => 'form.password'),
            'second_options' => array('label' => 'form.password_confirmation'),
            'invalid_message' => 'fos_user.password.mismatch',
        ));
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            /*$form->add('roles', ChoiceType::class, array(
                'choices' => array(
                    'ROLE_USER' => 'Utilisateurs',
                    'ROLE_ADMIN' => 'Admin',
                    'ROLE_SUPER_ADMIN' => 'Super admin'
                ),
                'multiple' => true
            ));*/
            $form->add('roles', ChoiceType::class, array(
                'translation_domain' => 'FOSUserBundle',
                'label' => 'profile.roles',
                'choices' => array(
                    'profile.roles_choice.user' => 'ROLE_USER',
                    'profile.roles_choice.admin'=> 'ROLE_ADMIN',
                    'profile.roles_choice.super_admin' => 'ROLE_SUPER_ADMIN'
                ),
//                'choices_as_values' => true,
                'multiple' => true
            ));
        } else {
            $form->add('roles', ChoiceType::class, array(
                'translation_domain' => 'FOSUserBundle',
                'label' => 'profile.roles',
                'choices' => array(
                    'profile.roles_choice.user' => 'ROLE_USER',
                    'profile.roles_choice.admin'=> 'ROLE_ADMIN',
                ),
//                'choices_as_values' => true,
                'multiple' => true
            ));
        }

        // liste des collectivités
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $form->add('collectivite', EntityType::class, array(
                'translation_domain' => 'FOSUserBundle',
                'label' => 'profile.local_authoritie',
                'class' => "SesileMainBundle:Collectivite",
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('p')
                        ->where('p.active = 1')
                        ->orderBy('p.nom', 'asc');
                },
                'choice_label' => 'Nom',
            ));
        }

        $form->add('submit', SubmitType::class, array(
            'translation_domain' => 'FOSUserBundle',
            'label' => 'label.save',
        ));


        return $form;
    }

    /**
     * Creates a form to edit a User entity.
     *
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(User $entity)
    {


        $form = $this->createForm(UserType::class, $entity, array(
            'action' => $this->generateUrl('user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        $form->add('plainPassword', RepeatedType::class, array(
            'type' => PasswordType::class,
            'required' => false,
            'options' => array('translation_domain' => 'FOSUserBundle', 'always_empty' => 'true'),
            'first_options' => array('label' => 'form.password'),
            'second_options' => array('label' => 'form.password_confirmation'),
            'invalid_message' => 'fos_user.password.mismatch',
        ));
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $form->add('roles', ChoiceType::class, array(
                'translation_domain' => 'FOSUserBundle',
                'label' => 'profile.roles',
                'choices' => array(
                    'profile.roles_choice.user' => 'ROLE_USER',
                    'profile.roles_choice.admin'=> 'ROLE_ADMIN',
                    'profile.roles_choice.super_admin' => 'ROLE_SUPER_ADMIN'
                ),
//                'choices_as_values' => true,
                'choice_label' => function ($value, $key, $index) {
                    return $key;
                },
                'multiple' => true
            ));
        } else {
            $form->add('roles', ChoiceType::class, array(
                'translation_domain' => 'FOSUserBundle',
                'label' => 'profile.roles',
                'choices' => array(
                    'profile.roles_choice.user' => 'ROLE_USER',
                    'profile.roles_choice.admin'=> 'ROLE_ADMIN'
                ),
//                'choices_as_values' => true,
                'choice_label' => function ($value, $key, $index) {
                    return $key;
                },
                'multiple' => true
            ));
        }

        // liste des collectivités
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $form->add('collectivite', EntityType::class, array(
                'translation_domain' => 'FOSUserBundle',
                'label' => 'profile.local_authoritie',
                'class' => "SesileMainBundle:Collectivite",
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('p')
                        ->where('p.active = 1')
                        ->orderBy('p.nom', 'asc');
                },
                'choice_label' => 'Nom',
            ));
        }



        $form->add('submit', SubmitType::class, array(
            'translation_domain' => 'FOSUserBundle',
            'label' => 'profile.edit.submit'
        ));

        return $form;
    }

    /**
     * Creates a form to delete a User entity by id.
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('user_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, array('label' => 'Supprimer'))
            ->getForm();
    }
    /**
     * Creates a form to delete a User entity by id.
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function certificateAppairForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('certificate_appair', array('id' => $id)))
            ->setMethod('POST')
            ->add('submit', SubmitType::class, array(
                    'translation_domain' => 'FOSUserBundle',
                    'label' => 'certificate.submit',
                'attr' => array('class' => 'btn btn-success btn-lg btn-block'))
            )
            ->getForm();
    }
    /**
     * Creates a form to delete a User entity by id.
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function certificateDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('certificate_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, array(
                    'translation_domain' => 'FOSUserBundle',
                    'label' => 'certificate.delete',
                'attr' => array('class' => 'btn btn-danger'))
            )
            ->getForm();
    }

    private function getCASParams()
    {
        $file = sprintf("%s/config/config_" . $this->get('kernel')->getEnvironment() . ".yml", $this->container->getParameter('kernel.root_dir'));
        $parsed = Yaml::parse(file_get_contents($file));

        $cas = $parsed['parameters'];

        return $cas;
    }


    /**
     * @Route("/error", name="error")
     * @Template()
     */
    public function errorAction()
    {
        return array();
    }


    /**
     * @Route("/cas/list", name="user_list_cas")
     * @Method("POST")
     */
    public function getUserListFromCasAction(Request $request) {
        $mail = $request->request->get("mail");
        $LdapInfo = $this->container->getParameter('ldap');
        $cas_server = $this->container->getParameter('cas_server');
        $ldapconn = ldap_connect($cas_server) or die("Connexion impossible au serveur LDAP [$cas_server]"); //security

        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

        if ($ldapconn) {
            if (ldap_bind($ldapconn, $LdapInfo["dn_admin"], $LdapInfo["password"])) {
                $justthese = array("displayName", "mail");

                $sr = ldap_search($ldapconn, $LdapInfo["dn_user"], "(mail=".$mail."*)");
                $data = ldap_get_entries($ldapconn, $sr);
                $ret = array();
                for ($i=0; $i<$data["count"]; $i++) {
                    $ret[] = $data[$i]["mail"][0];
                }
                return new JsonResponse($ret);

            } else {
                ldap_close($ldapconn);
                exit("Authentification au serveur LDAP impossible");
            }
        }
    }

    /**
     * @Route("/cas/infos", name="user_infos_cas")
     * @Method("POST")
     */
    public function getUserInfosFromCasAction(Request $request) {
        $mail = $request->request->get("mail");
        $LdapInfo = $this->container->getParameter('ldap');
        $cas_server = $this->container->getParameter('cas_server');
        $ldapconn = ldap_connect($cas_server) or die("Connexion impossible au serveur LDAP [$cas_server]"); //security

        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

        if ($ldapconn) {
            if (ldap_bind($ldapconn, $LdapInfo["dn_admin"], $LdapInfo["password"])) {
                $justthese = array("displayName", "mail");


                // TODO
                $sr = ldap_search($ldapconn, $LdapInfo["dn_user"], "(mail=".$mail.")", $justthese);
                $data = ldap_get_entries($ldapconn, $sr);
                $ret = array();
                if($data["count"] > 0) {
                    $ret[] = $data[0]["mail"][0];
                }
                return new JsonResponse($ret);
            } else {
                ldap_close($ldapconn);
                exit("Authentification au serveur LDAP impossible");
            }
        }
    }


    private function connexionLdap() {
        $cas_server = $this->container->getParameter('cas_server');

        $ldapconn = ldap_connect($cas_server) or die("Could not connect to LDAP server."); // security
        $LdapInfo = $this->container->getParameter('ldap');
        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

        if ($ldapconn) {
            if (ldap_bind($ldapconn, $LdapInfo["dn_admin"], $LdapInfo["password"])) {
                return $ldapconn;
            } else {
                ldap_close($ldapconn);
                exit("Authentification au serveur LDAP impossible");
            }
        }
        return $ldapconn;
    }

    /**
     * @Route("/cas/infos", name="user_infos_cas")
     * @param User $user
     * @param string $attribute
     * @return array
     */
    private function getUserInfosFromCas(User $user, $attribute = "mail") {
        $LdapInfo = $this->container->getParameter('ldap');
        $userName = $user->getUsername();
        $ldapconn = $this->connexionLdap();
        $justthese = array($attribute);

        $sr = ldap_search($ldapconn, $LdapInfo["dn_user"], "(mail=".$userName.")", $justthese);
        $data = ldap_get_entries($ldapconn, $sr);

        if($data["count"] > 0 && isset($data[0][$attribute][0])) {
            $ret = $data[0][$attribute][0];
        } else {
            $ret = null;
        }
        return $ret;
    }


    /**
     * @param $user
     * @param $attribute
     * @param $value
     * @return bool
     */
    private function setUserInfosInCas($user, $attribute, $value) {

        $LdapInfo = $this->container->getParameter('ldap');
        $ldapconn = $this->connexionLdap();
        $parent = $LdapInfo["dn_user"];
        $entry[$attribute] = $value;

        return ldap_modify($ldapconn, "mail=" . $user->getUsername() . "," . $parent, $entry);

    }

    /**
     * @param $user
     * @param $attribute
     * @return bool
     */
    private function removeUserInfosInCas($user, $attribute) {

        $LdapInfo = $this->container->getParameter('ldap');
        $ldapconn = $this->connexionLdap();
        $parent = $LdapInfo["dn_user"];

        $search = ldap_search($ldapconn, $LdapInfo["dn_user"], "(mail=" . $user->getUsername() . ")");
        $entries = ldap_get_entries($ldapconn,$search);

        if (isset($entries[0][$attribute][0])) {
            $entry[$attribute] = $entries[0][$attribute][0];
            return ldap_mod_del($ldapconn, "mail=" . $user->getUsername() . "," . $parent, $entry);
        } else {
            return false;
        }


    }

    /**
     * Convertit une chaine hexadécimale en valeur décimale en utiliant les fonctions calcul de précision (BCmath)
     * @param string $hex
     * @return number|string
     */
    private function bchexdec($hex) {
        if(strlen($hex) == 1) {
            return hexdec($hex);
        } else {
            $remain = substr($hex, 0, -1);
            $last = substr($hex, -1);
            return bcadd(bcmul(16, $this->bchexdec($remain)), hexdec($last));
        }
    }

    /**
     * @param $serial
     * @param $vendor
     * @return string
     */
    private function cas_ssl_infos($serial, $vendor) {
        // conversion des infos serial + vendor pour correspondre à ce qu'attend CAS
        $SSLCertificatSerial = $this->bchexdec($serial);
        $utf8_dict = array('\xC3\x80' => "A", // Ã€
            '\xC3\x81' => "Á", // Ã
            '\xC3\x82' => "Â", // Ã‚
            '\xC3\x83' => "Ã", // Ãƒ
            '\xC3\x84' => "Ä", // Ã„
            '\xC3\x85' => "Å", // Ã…
            '\xC3\x86' => "Æ", // Ã†
            '\xC3\x9E' => "Þ", // Ãž
            '\xC3\x87' => "Ç", // Ã‡
            '\xC4\x86' => "C", // Ä†
            '\xC4\x8C' => "C", // ÄŒ
            '\xC4\x90' => "Dj", // Ä
            '\xC3\x88' => "È", // Ãˆ
            '\xC3\x89' => "É", // Ã‰
            '\xC3\x8A' => "Ê", // ÃŠ
            '\xC3\x8B' => "Ë", // Ã‹
            '\xC4\x9E' => "Þ", // Äž
            '\xC3\x8C' => "Ì", // ÃŒ
            '\xC3\x8D' => "Í", // Ã
            '\xC3\x8E' => "Î", // ÃŽ
            '\xC3\x8F' => "Ï", // Ã
            '\xC4\xB0' => "I", // Ä°
            '\xC3\x91' => "Ñ", // Ã‘
            '\xC3\x92' => "Ò", // Ã’
            '\xC3\x93' => "Ó", // Ã“
            '\xC3\x94' => "Ô", // Ã”
            '\xC3\x95' => "Õ", // Ã•
            '\xC3\x96' => "Ö", // Ã–
            '\xC3\x98' => "Ø", // Ã˜
            '\xC3\x9F' => "ß", // ÃŸ
            '\xC3\x99' => "Ù", // Ã™
            '\xC3\x9A' => "Ú", // Ãš
            '\xC3\x9B' => "Û", // Ã›
            '\xC3\x9C' => "Ü", // Ãœ
            '\xC3\x9D' => "Ý", // Ã
            '\xC3\xA0' => "à", // Ã 
            '\xC3\xA1' => "á", // Ã¡
            '\xC3\xA2' => "â", // Ã¢
            '\xC3\xA3' => "ã", // Ã£
            '\xC3\xA4' => "ä", // Ã¤
            '\xC3\xA5' => "å", // Ã¥
            '\xC3\xA6' => "æ", // Ã¦
            '\xC3\xBE' => "b", // Ã¾
            '\xC3\xA7' => "ç", // Ã§
            '\xC4\x87' => "Ç", // Ä‡
            '\xC4\x8D' => "Í", // Ä
            '\xC4\x91' => "Ñ", // Ä‘
            '\xC3\xA8' => "è", // Ã¨
            '\xC3\xA9' => "é", // Ã©
            '\xC3\xAA' => "ê", // Ãª
            '\xC3\xAB' => "ë", // Ã«
            '\xC3\xAC' => "ì", // Ã¬
            '\xC3\xAD' => "í", // Ã­
            '\xC3\xAE' => "î", // Ã®
            '\xC3\xAF' => "ï", // Ã¯
            '\xC3\xB0' => "ð", // Ã°
            '\xC3\xB1' => "ñ", // Ã±
            '\xC3\xB2' => "ò", // Ã²
            '\xC3\xB3' => "ó", // Ã³
            '\xC3\xB4' => "ô", // Ã´
            '\xC3\xB5' => "õ", // Ãµ
            '\xC3\xB6' => "ö", // Ã¶
            '\xC3\xB8' => "ø", // Ã¸
            '\xC5\x94' => "R", // Å”
            '\xC5\x95' => "r", // Å•
            '\xC5\xA0' => "S", // Å 
            '\xC5\x9E' => "S", // Åž
            '\xC5\xA1' => "s", // Å¡
            '\xC3\xB9' => "ù", // Ã¹
            '\xC3\xBA' => "ú", // Ãº
            '\xC3\xBB' => "û", // Ã»
            '\xC3\xBC' => "ü", // Ã¼
            '\xC3\xBD' => "ý", // Ã½
            '\xC3\xBF' => "ÿ", // Ã¿
            '\xC5\xBD' => "Z", // Å½
            '\xC5\xBE' => "z"); // Å¾


        $vendor = strtr(trim($vendor), $utf8_dict);
        $str = "SERIALNUMBER=".$SSLCertificatSerial.", ".implode(", ", array_reverse(explode("/", trim($vendor, '/'))));
        return $str;
    }

    /**
     * Convertit la date du haproxy au format DateTime
     * @param $date
     * @return bool|\DateTime
     */
    private function convert_date_certificate($date) {
        return $validDate = \DateTime::createFromFormat('ymdHisT', $date);
    }


}
