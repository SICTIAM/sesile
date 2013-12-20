<?php

namespace Sesile\CircuitBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class CircuitController extends Controller
{
    /**
     * @Route("/new/", name="new_circuit", options={"expose"=true})
     * @Template()
     */
    public function newAction()
    {
        // recup la liste des users en base
        $userManager = $this->container->get('fos_user.user_manager');
        $users = $userManager->findUsers();

        // recup la list des circuits
        // TODO recup uniquement pour le user connecté
        $em = $this->getDoctrine()->getManager();
        $circuits = $em->getRepository('SesileCircuitBundle:Circuit')->findAll();
        return array('users' => $users, "circuits" => $circuits);
    }

    /**
     * @Route("/nouveau/", name="nouveau_circuit")
     * @Template()
     */
    public function nouveauAction() {
        return $this->newAction();
    }

    /**
     * @Route("/", name="create_circuit")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        var_dump($request);exit();


        return new Response('OK');
    }

    /**
     * @Route("/liste", name="circuits_favoris", options={"expose"=true})
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();
        $circuits_du_user = $em->getRepository('SesileCircuitBundle:Circuit')->findAll();

        $circuits = array();
        foreach($circuits_du_user as $circuit) {
            $circuits[] = array("id" => $circuit->getId(), "name" => $circuit->getName(), "ordre" => $circuit->getOrdre());
        }

        $response = new Response(json_encode($circuits));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/delete", name="del_circuits_favoris", options={"expose"=true})
     * @Method("POST")
     */
    public function deleteAction(Request $request) {
        $id_circuit = $request->request->get('id');
        $em = $this->getDoctrine()->getManager();
        try {
            $circuit = $em->getRepository('SesileCircuitBundle:Circuit')->find($id_circuit);
            $em->remove($circuit);
            $em->flush();
        }
        catch(Exception $e) {
            return new Response("NOK");
        }

        return new Response("OK");
    }
}
