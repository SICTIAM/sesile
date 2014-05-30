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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Sesile\UserBundle\Entity\User;
use Symfony\Component\Yaml\Yaml;

/**
 * Controller managing the user profile
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class LoginHandlerController extends Controller
{

    /**
     * @Route("/login", name="sesile_login")
     *
     */
    public function loginAction(Request $request)
    {

        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $request->getSession();

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(SecurityContext::AUTHENTICATION_ERROR)) {
            /* Call CAS API to do authentication */
            $upload = $this->container->getParameter('gorg');
            $DirPath = $upload['path'];

            $LdapInfo = $this->container->getParameter('ldap');

            $cas = $this->getCASParams();
            //  var_dump($DirPath);exit;
            require_once($DirPath . 'CAS.php');
            \phpCAS::client($cas["cas_protocol"], $cas["cas_server"], $cas["cas_port"], $cas['cas_path'], false);
            \phpCAS::forceAuthentication();
            $user = \phpCAS::getUser();
            $dn = $LdapInfo["dn_user"];
            $filter = "(|(mail=" . $user . "))";
            $justthese = array("cn", "mail", "userPassword");

            $ldapconn = ldap_connect($cas["cas_server"])
            or die("Could not connect to LDAP server."); //security
            ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

            if ($ldapconn) {

                //binding au serveur LDAP
                if (@ldap_bind($ldapconn, $LdapInfo["dn_admin"], $LdapInfo["password"])) {
                    echo "LDAP bind successful...";
                    $sr = ldap_search($ldapconn, $dn, $filter, $justthese);
                    $info = ldap_get_entries($ldapconn, $sr);
                    //    var_dump($info);exit;
                    //echo "mail = ".$info[0]["mail"][0]." cn = ".$info[0]["cn"][0]." pwd = ".$info[0]["userpassword"][0];exit;
                    var_dump($info[0]);

                    $nom = $prenom = '';
                    if (array_key_exists('displayName', $info[0]) && stripos($info[0]["displayName"][0], ' ') === false) {
                        $nom = $info[0]["displayName"][0];
                        $prenom = ' ';
                    }

                    //  echo "nom = ".$nom." prenom = ".$prenom;exit;
                    $entity = new User();
                    $entity->setNom($nom);
                    $entity->setPrenom($prenom);
                    $entity->setUsername($info[0]["mail"][0]);
                    $entity->setEmail($info[0]["mail"][0]);
                    $entity->setPlainPassword("sictiam");
                    $entity->setEnabled(true);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($entity);
                    $em->flush();

                } else {
                    echo "LDAP bind failed...";
                }
            }
            $error = '';
            //   $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            //   $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        if ($error) {
            // TODO: this is a potential security risk (see http://trac.symfony-project.org/ticket/9523)
            $error = $error->getMessage();
        }
        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(SecurityContext::LAST_USERNAME);

        $csrfToken = $this->container->has('form.csrf_provider')
            ? $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate')
            : null;
        return $this->redirect($this->generateUrl('sesile_profile_show'));

    }

    private function getCASParams()
    {
        $file = sprintf("%s/config/config.yml_" . $this->getEnvironment() . ".yml", $this->container->getParameter('kernel.root_dir'));
        $parsed = Yaml::parse(file_get_contents($file));

        $cas = $parsed['parameters'];
        return $cas;
    }
}
