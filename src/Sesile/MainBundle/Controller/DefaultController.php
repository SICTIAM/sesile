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
        $session = new Session();
        //$session->start();
        $session->set('logo', 'f69fa0f49e660d38ccf33c6c5fa7a57a21ee3ca8.png');
        $Upload = $this->container->getParameter('upload');
        $DocPath = $Upload["msg_acc"];
        $handle = fopen($DocPath, 'a+');
        if (filesize($DocPath)) {
            $msg_accueil = fread($handle, filesize($DocPath));
            fclose($handle);

        } else {
            $msg_accueil = "Bienvenue sur le parapheur sesile";
        }
        $image = 'f69fa0f49e660d38ccf33c6c5fa7a57a21ee3ca8.png';
        return array('msg_acc' => $msg_accueil,
            'image' => $image);
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
