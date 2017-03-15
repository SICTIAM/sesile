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
//use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
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
    public function loginAction(Request $request, AuthenticationException $exception = null)
    {
        if($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            return $this->redirect($this->generateUrl('sesile_profile_show'));
        }
        if($exception === null)
        {
            return $this->redirect($this->generateUrl('index'));
        }
        $manager = $this->get('be_simple.sso_auth.factory')->getManager("annuaire_sso", "/");

        return array(
            'manager' => $manager,
            'request' => $request,
            'exception' => $exception
        );

        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
//        $session = $request->getSession();


//        return $this->redirect($this->generateUrl('sesile_profile_show'));

    }

    private function getCASParams()
    {
        $file = sprintf("%s/config/config_" . $this->container->getParameter('kernel.environment') . ".yml", $this->container->getParameter('kernel.root_dir'));

        $parsed = Yaml::parse(file_get_contents($file));

        $cas = $parsed['parameters'];
        return $cas;
    }
}
