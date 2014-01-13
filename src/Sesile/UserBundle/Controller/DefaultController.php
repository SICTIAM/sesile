<?php

namespace Sesile\UserBundle\Controller;

use Sesile\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\Request;
use Sesile\UserBundle\Form\UserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Yaml\Yaml;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="liste_users")
     * @Template("SesileUserBundle:Default:index.html.twig")
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
            if (ldap_bind($ldapconn, 'cn=admin,dc=sictiam,dc=local', 'WcJa37BI')) {
                echo "LDAP bind successful...";
            } else {
                echo "LDAP bind failed...";
            }
            if ($form->isValid()) {

                $entity->setEmail($form->get('username')->getData());
                $em = $this->getDoctrine()->getManager();
                $em->persist($entity);
                $em->flush();

                $plainpwd = $form->get('plainPassword')->getData();
                $email = $entity->getUsername();
//création du tableau d'attributs
                $entry["objectClass"][0] = "inetOrgPerson";
                $entry["objectClass"][1] = "organizationalPerson";
                $entry["objectClass"][2] = "person";
                $entry["objectClass"][3] = "shadowAccount";
                $entry["cn"] = $email;
                $entry["sn"] = $entity->getPrenom() . ' ' . $entity->getNom();
                $entry["userPassword"] = "{MD5}" . base64_encode(pack('H*', md5($plainpwd)));
                $entry["givenName"] = $email;
                $entry["shadowInactive"] = -1;
                $entry["uid"] = $entity->getId();
                $entry["displayName"] = $entity->getNom() . " " . $entity->getPrenom();

                //création du Distinguished Name
                $dn = "mail=" . $email . ",cn=Users,dc=sictiam,dc=local";
                ldap_add($ldapconn, $dn, $entry);
                ldap_close($ldapconn);


                //envoi d'un mail à l'utilisateur nouvellement créé
                $message = \Swift_Message::newInstance()
                    ->setContentType('text/html')
                    ->setSubject('Nouvel utilisateur')
                    ->setFrom('j.mercier@sictiam.fr')
                    ->setTo($email)
                    ->setBody('Bienvenue dans Sesile ' . $entity->getUsername());
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

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SesileUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        //$deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $cas = $this->getCASParams();
            $ldapconn = ldap_connect($cas['cas_server'])
            or die("Could not connect to LDAP server."); //security
            ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

            if ($ldapconn) {

                //binding au serveur LDAP
                if (ldap_bind($ldapconn, 'cn=admin,dc=sictiam,dc=local', 'WcJa37BI')) {
                    echo "LDAP bind successful...";
                } else {
                    echo "LDAP bind failed...";
                }
                //   $entry["userPassword"] = "{MD5}".base64_encode(pack('H*',md5($plainpwd)));

                $em->flush();
                return $this->redirect($this->generateUrl('user_edit', array('id' => $id)));
            }
        }
        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
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
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SesileUserBundle:User')->findOneById($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find User entity.');
            }

            $em->remove($entity);
            $em->flush();

        }
        return $this->redirect($this->generateUrl('classeur')); // rediriger vers liste_user non?
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

        $form->add('submit', 'submit', array('label' => 'Create'));


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

    private function getCASParams () {
        $file   = sprintf("%s/config/security.yml", $this->container->getParameter('kernel.root_dir'));
        $parsed = Yaml::parse(file_get_contents($file));

        $cas = $parsed['security']['firewalls']['secured_area']['cas'];
        return $cas;
    }
}

