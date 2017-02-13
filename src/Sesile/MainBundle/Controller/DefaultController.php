<?php

namespace Sesile\MainBundle\Controller;

use Sesile\CircuitBundle\Controller\CircuitController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DefaultController extends Controller
{
    /**
     * @Route("/",name="index")
     * @Template()
     */
    public function indexAction() {
        //throw $this->createNotFoundException('Bienvenue les Men In Black');
        $em = $this->getDoctrine()->getManager();
        $CollecEntity = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));
        $msg_accueil = $CollecEntity->getMessage();

        // Si l utilisateur n es pas sur ca collectivite
        if (is_object($this->getUser()) && $this->get('session')->get('nocoll')) {
            //var_dump($this->get('session')->get('nocoll'));
            $msg_err = 'vous n\'êtes pas connecté à l\'interface SESILE de votre collectivité de rattachement';
            return array('msg_err' => $msg_err);
        }

        // Si l utlisateur n est pas actif
        if (is_object($this->getUser()) && !$this->getUser()->isEnabled()) {

            $this->get('security.context')->setToken(null);
            $this->get('request')->getSession()->invalidate();
            $this->get('session')->getFlashBag()->add(
                'warning',
                'Votre compte n\'a pas été validé dans SESILE.'
            );
            return $this->redirect($this->generateUrl('_default'));
            //$this->get('security.logout.handler.session')->logout($reque‌​st, $response, $token);
            //throw new AccessDeniedHttpException("Votre compte n'a pas été validé dans SESILE.");
        }

        return array('msg_acc' => $msg_accueil);

    }

    /**
     * @Route("/apropos",name="apropos")
     * @Template()
     */
    public function aproposAction()
    {
        $tabversion = $this->container->getParameter('build');
        $major = $this->container->getParameter('majorversion');

        return array('majorversion' => $major, 'commit' => $tabversion['commit'], 'buildnumber' => $tabversion['buildnumber']);
    }

}
