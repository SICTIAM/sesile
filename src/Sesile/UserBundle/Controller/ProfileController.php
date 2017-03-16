<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sesile\UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller managing the user profile
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class ProfileController extends Controller
{

    /**
     * @Route("/profile/show", name="sesile_profile_show")
     *
     */
    public function showAction()
    {

        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            $this->container->get('session')->getFlashBag()->add(
                "error",
                "Votre compte n'a pas été déclaré dans SESILE."
            );
            return new RedirectResponse($this->container->get('router')->generate('index'));
//            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->container->get('templating')->renderResponse('SesileUserBundle:Profile:show.html.twig', array('user' => $user));
    }

    /**
     * @Route("/profile/edit", name="sesile_profile_edit")
     *
     */
    public function editAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        /** @var $formFactory FactoryInterface */
        $formFactory = $this->get('fos_user.profile.form.factory');

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $userManager UserManagerInterface */
            $userManager = $this->get('fos_user.user_manager');

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

            $userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('fos_user_profile_show');
                $response = new RedirectResponse($url);
            }

            // MAJ du CAS
            $this->majCas($form, $user);

            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        }

        return $this->render('@FOSUser/Profile/edit.html.twig', array(
            'form' => $form->createView(),'user' => $user
        ));
    }


    /**
     * @param $request
     * @param $user
     *
     *
     * Fonction mettant les données du CAS à jour
     */
    private function majCas ($request, $user) {

        $LdapInfo = $this->container->getParameter('ldap');
        $userName = $user->getUsername();
        //ancien
        $ExValues = array(
            "mail" => $userName,
            "Nom" => $user->getNom(),
            "Prenom" => $user->getPrenom()
        );

        //new
        $cas_server = $this->container->getParameter('cas_server');
        $ldapconn = ldap_connect($cas_server) or die("Could not connect to LDAP server."); // security

        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

        if ($ldapconn) {

            //binding au serveur LDAP
            if (ldap_bind($ldapconn, $LdapInfo["dn_admin"], $LdapInfo["password"])) {
                //@ldap_bind($ldapconn, $LdapInfo["dn_admin"], $LdapInfo["password"]);

                $oldPass = base64_encode(pack('H*', md5(trim($request->get('password')->getData()))));

                // Generation
                // Requete sur le LDAP pour le user
                $justthese = array("userpassword");
                $filter = "(|(mail=" . $userName . "*))";
                $sr = ldap_search($ldapconn, $LdapInfo["dn_user"], $filter, $justthese);
                $info = ldap_get_entries($ldapconn, $sr);

                $passwordLDAP = substr($info[0]['userpassword'][0], 5);
                // FIN test

                // $person est un nom ou une partie de nom (par exemple, "Jean")

                // On entre la meme valeur dans cn et sn pour eviter les problemes entre dev et demo
                $entry["cn"] = $userName;
                $entry["sn"] = $userName;
                $entry["givenName"] = $userName;
                $entry["displayName"] = $user->getNom() . ' ' . $user->getPrenom();

                $pwd = trim($request->get('plainPassword')->getData());

                if (($passwordLDAP == $oldPass) && $pwd) {

                    $user->setPlainPassword($pwd);
                    $entry["userPassword"] = "{MD5}" . base64_encode(pack('H*', md5($pwd)));
                    $this->get('session')->getFlashBag()->add('notice', 'Votre mot de passe a été modifié.');

                } elseif (($passwordLDAP != $oldPass) && $pwd) {

                    $this->get('session')->getFlashBag()->add('notice', 'Le mot de passe actuel ne correspond pas.');

                }

                //création du Distinguished Name
                $parent = $LdapInfo["dn_user"];
                $dn = "mail=" . $ExValues["mail"] . "," . $parent;

                if (ldap_rename($ldapconn, $dn, "mail=" . $userName, $parent, true) && ldap_modify($ldapconn, "mail=" . $userName . "," . $parent, $entry)) {

                    ldap_close($ldapconn);
                } else {
                    ldap_close($ldapconn);
                    $this->get('session')->getFlashBag()->add('notice', 'Problème de connexion au CAS. Veuillez réessayer ultérieurement.');
                }
                // FIN Test sur LDAP
            } else {
                $this->get('session')->getFlashBag()->add('notice', 'Problème de connexion au CAS. Veuillez réessayer ultérieurement.');
            }
        }
        else {
            $this->get('session')->getFlashBag()->add('notice', 'Merci de vérifier votre saisie');
        }

    }

}

