<?php

namespace Sesile\UserBundle\Controller;

use Sesile\UserBundle\Entity\User;
use Sesile\UserBundle\Entity\UserPack;
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
                    //envoi d'un mail à l'utilisateur nouvellement créé
                    $message = \Swift_Message::newInstance()
                        ->setContentType('text/html')
                        ->setSubject('Nouvel utilisateur')
                        ->setFrom("sesile@sictiam.fr")
                        ->setTo($entity->getUsername())
                        ->setBody('Bienvenue dans Sesile ' . $entity->getPrenom() . ' ' . $entity->getNom());
                    $this->get('mailer')->send($message);
                } else {
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        'l\'utilisateur existe déjà'
                    );
                    return $this->redirect($this->generateUrl('ajout_user'));
                }
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
        $upload = $this->container->getParameter('upload');
        $DirPath = $upload['path'];
        $cas = $this->getCASParams();

        $LdapInfo = $this->container->getParameter('ldap');
        $cas_server = $this->container->getParameter('cas_server');

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SesileUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }
        $ExValues = array("mail" => $entity->getUsername(),
            "Nom" => $entity->getNom(),
            "Prenom" => $entity->getPrenom()
        );

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $ldapconn = ldap_connect($cas_server) or die("Connexion impossible au serveur LDAP [".$cas_server."]"); //security
            ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

            if ($ldapconn) {
                //binding au serveur LDAP

                if (ldap_bind($ldapconn, $LdapInfo["dn_admin"], $LdapInfo["password"])) {
                    $entry["cn"] = $entity->getUsername();
                    $pwd = trim($editForm->get('plainPassword')->getData());

                    if ($pwd) {
                        $entity->setPlainPassword($pwd);
                        $entry["userPassword"] = "{MD5}" . base64_encode(pack('H*', md5($pwd)));
                    }
                    $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));

                    if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
                        $entity->setCollectivite($collectivite);
                    }


                    $entity->setEmail($editForm->get('username')->getData());
                    $entry["givenName"] = $entity->getUsername();
                    $entry["displayName"] = $entity->getNom() . ' ' . $entity->getPrenom();

                    //création du Distinguished Name
                    $parent = $LdapInfo["dn_user"];
                    $dn = "mail=" . $ExValues["mail"] . "," . $parent;

                    if (ldap_rename($ldapconn, $dn, "mail=" . $entity->getUsername(), $parent, true) && ldap_modify($ldapconn, "mail=" . $entity->getUsername() . "," . $parent, $entry)) {
                        ldap_close($ldapconn);
                        if ($editForm->get('file')->getData()) {
                            // echo "true";exit;
                            if ($entity->getPath()) {
                                $entity->removeUpload($DirPath);
                            }
                            $entity->preUpload();
                            $entity->upload($DirPath);

                        }
                        //echo "false";exit;
                        $em->flush();
                    } else {
                        ldap_close($ldapconn);
                        echo "pb rename ldap";
                        exit;
                    }

                    return $this->redirect($this->generateUrl('liste_users', array('id' => $id)));
                } else {
                    ldap_close($ldapconn);
                    exit("Authentification au serveur LDAP impossible");
                }

                return $this->redirect($this->generateUrl('liste_users', array('id' => $id)));
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
     * @Route("/{id}", name="user_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $upload = $this->container->getParameter('upload');
        $DirPath = $upload['path'];

        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SesileUserBundle:User')->findOneById($id);
            if ($entity->getPath()) {
                $entity->removeUpload($DirPath);
            }

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find User entity.');
            }

            $em->remove($entity);
            $em->flush();

        }
        return $this->redirect($this->generateUrl('liste_users')); // rediriger vers liste_user non?
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
        $users = $em->getRepository('SesileUserBundle:User')->findByCollectivite($collectivite);
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
        $users = $em->getRepository('SesileUserBundle:User')->findByCollectivite($collectivite);

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
        return new RedirectResponse($this->container->get('router')->generate('edit_userpack',array('id'=>$id)));

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
                    return $repository->createQueryBuilder('p');
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
                    return $repository->createQueryBuilder('p');
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
