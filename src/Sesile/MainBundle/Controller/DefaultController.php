<?php

namespace Sesile\MainBundle\Controller;

use Sesile\CircuitBundle\Controller\CircuitController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller
{
    /**
     * @Route("/",name="index")
     * @Template()
     */
    public function indexAction()
    {

        $image = "/images/Logo-Officiel-SICTIAM_taille250px.jpg";

        $em = $this->getDoctrine()->getManager();
        if (isset($_SESSION["phpCAS"])) {
            $session = new Session();
            $tab = $_SESSION["phpCAS"];
            $username = $tab["user"];
            $UserEntity = $em->getRepository('SesileUserBundle:User')->findOneByUsername($username);

            $CollecEntity = $em->getRepository('SesileMainBundle:Collectivite')->findOneById(1);

            if ($CollecEntity != null) {
                $image = $CollecEntity->getImage();
            }

            $session->set('logo', $image);
            $session->set('idCollec', 1);
        } else {
            $CollecEntity = $em->getRepository('SesileMainBundle:Collectivite')->findOneById(1);
        }

        if ($CollecEntity != null) {
            $msg_accueil = $CollecEntity->getMessage();
        } else {
            $msg_accueil = "Bienvenue sur le parapheur sesile";
        }
        return array('msg_acc' => $msg_accueil, "img_accueil" => $image);
    }

    /**
     * @Route("/accueil", name="accueil")
     * @Template()
     */
    public function accueilAction()
    {
        $bienvenue = "a";
        return array("bienvenue" => $bienvenue);
    }
}
