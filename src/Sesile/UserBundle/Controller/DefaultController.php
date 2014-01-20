<?php

namespace Sesile\UserBundle\Controller;

use Sesile\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;
use Sesile\UserBundle\Form\UserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Yaml\Yaml;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use vendor\symfony\src\Symfony\Bundle\TwigBundle\Extension\AssetsExtension;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="liste_users")
     * @Template("SesileUserBundle:Default:index.html.twig")
     *
     */
    public function listAction()
    {


        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();
        return array(
            "users" => $users
        );
    }

    /**
     * @Route("/creation/", name="ajout_user")
     * @Template("SesileUserBundle:Default:ajout.html.twig")
     */
    public function ajoutAction(Request $request)
    {

        $upload = $this->container->getParameter('upload');
        $DirPath = $upload['path'];

        $LdapInfo = $this->container->getParameter('ldap');

        //  var_dump($this->container->get('twig.extension.assets')->getAssetUrl(''));exit;
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            // Sinon on déclenche une exception « Accès interdit »
            return $this->render('SesileMainBundle:Default:errorrestricted.html.twig');
        }
        $entity = new User();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        //connexion au serveur LDAP
        $cas = $this->getCASParams();
        $ldapconn = ldap_connect($cas['cas_server'])
        or die("Could not connect to LDAP server."); //security
        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

        if ($ldapconn) {

            //binding au serveur LDAP
            if (ldap_bind($ldapconn, $LdapInfo["dn_admin"], $LdapInfo["password"])) {

            } else {

            }


            if ($form->isValid()) {

                $entity->setEmail($form->get('username')->getData());
                $em = $this->getDoctrine()->getManager();

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
                $entry["sn"] = $res["prenom"] . ' ' . $res["nom"];
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

                if ($info["count"]) {
                    ldap_close($ldapconn);
                    return $this->redirect($this->generateUrl('error'));
                } else {
                    //  var_dump($dn);exit;
                    ldap_add($ldapconn, "mail=" . $res["email"] . "," . $LdapInfo["dn_user"], $entry);
                    $em->flush();
                }
                ldap_close($ldapconn);

                //envoi d'un mail à l'utilisateur nouvellement créé
                $message = \Swift_Message::newInstance()
                    ->setContentType('text/html')
                    ->setSubject('Nouvel utilisateur')
                    ->setFrom('j.mercier@sictiam.fr')
                    ->setTo($entity->getUsername())
                    ->setBody('Bienvenue dans Sesile ' . $entity->getPrenom() . ' ' . $entity->getNom());
                $this->get('mailer')->send($message);


                return $this->redirect($this->generateUrl('liste_users', array('id' => $entity->getId())));

            }

        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );


    }

    /**
     * Displays a form to edit an existing Classeur entity.
     *
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
        );
    }

    /**
     * Update an existing User entity.
     *
     * @Route("/{id}", name="user_update")
     * @Method("PUT")
     * @Template("SesileUserBundle:Default:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $upload = $this->container->getParameter('upload');
        $DirPath = $upload['path'];
        $cas = $this->getCASParams();

        $LdapInfo = $this->container->getParameter('ldap');

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

            $ldapconn = ldap_connect($cas["cas_server"])
            or die("Could not connect to LDAP server."); //security
            ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

            if ($ldapconn) {

                //binding au serveur LDAP
                if (ldap_bind($ldapconn, $LdapInfo["dn_admin"], $LdapInfo["password"])) {
                    $entry["cn"] = $entity->getUsername();
                    $entry["sn"] = $entity->getNom() . ' ' . $entity->getPrenom();
                    $pwd = trim($editForm->get('plainPassword')->getData());
                    if ($pwd) {

                        $entity->setPlainPassword($pwd);
                        $entry["userPassword"] = "{MD5}" . base64_encode(pack('H*', md5($pwd)));
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
                    echo "LDAP bind failed...";
                }
                //   $entry["userPassword"] = "{MD5}".base64_encode(pack('H*',md5($plainpwd)));

                return $this->redirect($this->generateUrl('liste_users', array('id' => $id)));
            }
        }
        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
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

        $form->add('roles', 'choice', array(
            'choices' => array(
                'ROLE_USER' => 'Utilisateurs',
                'ROLE_ADMIN' => 'Admin',
                'ROLE_SUPER_ADMIN' => 'Super admin'
            ),
            'multiple' => true
        ));

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

        $form->add('roles', 'choice', array(
            'choices' => array(
                'ROLE_USER' => 'Utilisateurs',
                'ROLE_ADMIN' => 'Admin',
                'ROLE_SUPER_ADMIN' => 'Super admin'
            ),
            'multiple' => true
        ));
        $form->add('submit', 'submit', array('label' => 'Enregistrer'));

        return $form;
    }

    /**
     * Creates a form to delete a User entity by id.
     *
     * @param mixed $id The entity id
     *
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
        $file = sprintf("%s/config/security.yml", $this->container->getParameter('kernel.root_dir'));
        $parsed = Yaml::parse(file_get_contents($file));

        $cas = $parsed['security']['firewalls']['secured_area']['cas'];
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

}

