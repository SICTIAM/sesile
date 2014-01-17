<?php

namespace Sesile\MainBundle\Controller;

use Sesile\CircuitBundle\Controller\CircuitController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/",name="index")
     * @Template()
     */
    public function indexAction()
    {
        $Upload = $this->container->getParameter('upload');
        $DocPath = $Upload["doc_path"];
        $handle = fopen($DocPath . "msg_accueil.txt", 'a+');
        $msg_accueil = fread($handle, filesize($DocPath . "msg_accueil.txt"));
        fclose($handle);
        return array('msg_acc' => $msg_accueil,);
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
