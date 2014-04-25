<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sesile\MainBundle\Controller;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sesile\UserBundle\Entity\User;
use Symfony\Component\Yaml\Yaml;


/**
 * Controller managing the user profile
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class ProfileController extends ContainerAware
{

    /**
     * @Route("/profile/show", name="sesile_profile_show")
     *
     */
    public function showAction()
    {

        $user = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->container->get('templating')->renderResponse('SesileMainBundle:Profile:show.html.twig', array('user' => $user));
    }

    /**
     * @Route("/profile/edit", name="sesile_profile_edit")
     *
     */
    public function editAction(Request $request)
    {
        $user = new User();
        $user = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $upload = $this->container->getParameter('upload');
        $DirPath = $upload['path'];

        $LdapInfo = $this->container->getParameter('ldap');

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->container->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->container->get('fos_user.profile.form.factory');

        $form = $formFactory->createForm();
        $form->setData($user);
        //ancien
        $ExValues = array("mail" => $user->getUsername(),
            "Nom" => $user->getNom(),
            "Prenom" => $user->getPrenom()
        );


        if ('POST' === $request->getMethod()) {
            $form->bind($request);

            if ($form->isValid()) {
                /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
                $userManager = $this->container->get('fos_user.user_manager');

                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);
                $user->setEmail($form->get('username')->getData());
                if ($form->get('file')->getData()) {

                    if ($user->getPath()) {

                        $user->removeUpload($DirPath);
                    }
                    $user->preUpload();
                    $user->upload($DirPath);

                }
                $userManager->updateUser($user);
                //new
                $cas = $this->getCASParams();
                $ldapconn = ldap_connect($cas["cas_server"])
                or die("Could not connect to LDAP server."); //security
                ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

                if ($ldapconn) {

                    //binding au serveur LDAP
                    if (ldap_bind($ldapconn, $LdapInfo["dn_admin"], $LdapInfo["password"])) {
                        $entry["cn"] = $user->getUsername();
                        $entry["sn"] = $user->getNom() . ' ' . $user->getPrenom();
                        $entry["givenName"] = $user->getUsername();
                        $entry["displayName"] = $user->getNom() . ' ' . $user->getPrenom();
                        $pwd = trim($form->get('plainPassword')->getData());
                        if ($pwd) {

                            $user->setPlainPassword($pwd);
                            $entry["userPassword"] = "{MD5}" . base64_encode(pack('H*', md5($pwd)));
                        }
                        //création du Distinguished Name
                        $parent = $LdapInfo["dn_user"];
                        $dn = "mail=" . $ExValues["mail"] . "," . $parent;

                        if (ldap_rename($ldapconn, $dn, "mail=" . $user->getUsername(), $parent, true) && ldap_modify($ldapconn, "mail=" . $user->getUsername() . "," . $parent, $entry)) {


                            ldap_close($ldapconn);
                        } else {
                            ldap_close($ldapconn);
                            echo "pb rename ldap";
                            exit;
                        }

                    } else {
                        echo "LDAP bind failed...";
                        exit;
                    }
                    // var_dump($user);exit;
                    if (null === $response = $event->getResponse()) {
                        $url = $this->container->get('router')->generate('sesile_profile_show');
                        $response = new RedirectResponse($url);
                    }

                    $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

                    return $response;
                }
            }
        }

        return $this->container->get('templating')->renderResponse(
            'FOSUserBundle:Profile:edit.html.' . $this->container->getParameter('fos_user.template.engine'),
            array('form' => $form->createView())
        );
    }

    private function getCASParams()
    {
        $file = sprintf("%s/config/config.yml", $this->container->getParameter('kernel.root_dir'));
        $parsed = Yaml::parse(file_get_contents($file));

        $cas = $parsed['parameters'];
        return $cas;
    }
}
