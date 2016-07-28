<?php

namespace Sesile\UserBundle\Controller;

use Sesile\UserBundle\Entity\User;
use Sesile\UserBundle\Entity\UserPack;
use Sesile\UserBundle\Entity\UserRole;
use Sesile\UserBundle\Form\UserRoleType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
use vendor\symfony\src\Symfony\Bundle\TwigBundle\Extension\AssetsExtension;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="liste_users")
     * @Template("SesileUserBundle:Default:index.html.twig")
     */
    public function listAction() {
        if ($this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            $userManager = $this->get('fos_user.user_manager');
            $users = $userManager->findUsers();
        }
        else if($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $em = $this->getDoctrine()->getManager();
            $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->find($this->getRequest()->getSession()->get("collectivite"));
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

        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            // Sinon on déclenche une exception « Accès interdit »
            return $this->render('SesileMainBundle:Default:errorrestricted.html.twig');
        }
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
                    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
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

                    //envoi d'un mail à l'utilisateur nouvellement créé
                    // Fonction mis en commentaire suite a la demande de CB
                    /*$message = \Swift_Message::newInstance()
                        ->setContentType('text/html')
                        ->setSubject('Nouvel utilisateur')
                        ->setFrom("sesile@sictiam.fr")
                        ->setTo($entity->getUsername())
                        ->setBody('Bienvenue dans Sesile ' . $entity->getPrenom() . ' ' . $entity->getNom());
                    $this->get('mailer')->send($message);*/
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
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            // Sinon on déclenche une exception « Accès interdit »
            return $this->render('SesileMainBundle:Default:errorrestricted.html.twig');
        }
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SesileUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);


        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            "menu_color" => "vert"
        );
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

                    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
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
        if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
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

        // On Supprime l'utilisateur de tous les UserPacks
        $em->getRepository('SesileUserBundle:UserPack')->deleteUserFromUserPacks($id);

        // On supprime l'utilisateur de tous les Service organisationnel (EtapeGroupe)
        $em->getRepository('SesileUserBundle:EtapeGroupe')->deleteUserFromEtapeGroupes($id);

        // On récupère le dossier des avatar
        $upload = $this->container->getParameter('upload');
        $DirPath = $upload['path'];

        // On récupère l'entity utilisateur à supprimer
        $entity = $em->getRepository('SesileUserBundle:User')->findOneById($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        // On supprime tous les roles de l'utilisateur (UserRole)
        $entity->removeAllUserRole();

        // Si elle a bien un répertoire pour son avatar
        if ($entity->getPath()) {
            $entity->removeUpload($DirPath);
        }

        // On supprime l'user et on flush
        $em->remove($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            'L\'utilisateur a bien été supprimé'
        );

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

        // fonction pour la securite
        if(!$this->securityContext()) { return new RedirectResponse($this->container->get('router')->generate('index')); }

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
        // fonction pour la securite
        if(!$this->securityContext()) { return new RedirectResponse($this->container->get('router')->generate('index')); }

        $em = $this->getDoctrine()->getManager();
        $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));
//        $users = $em->getRepository('SesileUserBundle:User')->findByCollectivite($collectivite);
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

        // fonction pour la securite
        if(!$this->securityContext()) { return new RedirectResponse($this->container->get('router')->generate('index')); }

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

        // fonction pour la securite
        if(!$this->securityContext()) { return new RedirectResponse($this->container->get('router')->generate('index')); }

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


    private function securityContext() {
        if ($this->get('security.context')->isGranted('ROLE_SUPER_ADMIN') || $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return true;
        }
        else {
            return false;
        }
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
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('ajout_user'),
            'method' => 'POST',
        ));
        $form->add('plainPassword', 'repeated', array(
            'type' => 'password',
            'required' => true,
            'options' => array('translation_domain' => 'FOSUserBundle', 'always_empty' => 'true'),
            'first_options' => array('label' => 'form.password'),
            'second_options' => array('label' => 'form.password_confirmation'),
            'invalid_message' => 'fos_user.password.mismatch',
        ));
        if ($this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            $form->add('roles', 'choice', array(
                'choices' => array(
                    'ROLE_USER' => 'Utilisateurs',
                    'ROLE_ADMIN' => 'Admin',
                    'ROLE_SUPER_ADMIN' => 'Super admin'
                ),
                'multiple' => true
            ));
        } else {
            $form->add('roles', 'choice', array(
                'choices' => array(
                    'ROLE_USER' => 'Utilisateurs',
                    'ROLE_ADMIN' => 'Admin',
                ),
                'multiple' => true
            ));
        }

        // liste des collectivités
        if ($this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            $form->add('collectivite', 'entity', array(
                'class' => "SesileMainBundle:Collectivite",
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('p')
                        ->where('p.active = 1')
                        ->orderBy('p.nom', 'asc');
                },
                'property' => 'Nom',
            ));
        }

        $form->add('submit', 'submit', array('label' => 'Enregistrer'));


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


        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        $form->add('plainPassword', 'repeated', array(
            'type' => 'password',
            'required' => false,
            'options' => array('translation_domain' => 'FOSUserBundle', 'always_empty' => 'true'),
            'first_options' => array('label' => 'form.password'),
            'second_options' => array('label' => 'form.password_confirmation'),
            'invalid_message' => 'fos_user.password.mismatch',
        ));
        if ($this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            $form->add('roles', 'choice', array(
                'choices' => array(
                    'ROLE_USER' => 'Utilisateurs',
                    'ROLE_ADMIN' => 'Admin',
                    'ROLE_SUPER_ADMIN' => 'Super admin'
                ),
                'multiple' => true
            ));
        } else {
            $form->add('roles', 'choice', array(
                'choices' => array(
                    'ROLE_USER' => 'Utilisateurs',
                    'ROLE_ADMIN' => 'Admin',
                ),
                'multiple' => true
            ));
        }

        // liste des collectivités
        if ($this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            $form->add('collectivite', 'entity', array(
                'class' => "SesileMainBundle:Collectivite",
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('p')
                        ->where('p.active = 1')
                        ->orderBy('p.nom', 'asc');
                },
                'property' => 'Nom',
            ));
        }



        $form->add('submit', 'submit', array('label' => 'Enregistrer'));

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
            ->add('submit', 'submit', array('label' => 'Supprimer'))
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
}
