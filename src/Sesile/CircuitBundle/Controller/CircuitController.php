<?php

namespace Sesile\CircuitBundle\Controller;

use Sesile\CircuitBundle\Entity\Circuit;
use Sesile\UserBundle\Entity\Groupe;
use Sesile\UserBundle\Entity\User;
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
    public function newAction($forClasseur = true)
    {
        // recup la liste des users en base
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('SesileUserBundle:User')->findBy(array(
            "collectivite" => $this->get("session")->get("collectivite")
        ));

        // recup la list des circuits
        // TODO recup uniquement pour le user connecté
        $circuits = array();
        $em = $this->getDoctrine()->getManager();
        $circuits_du_user = $em->getRepository('SesileCircuitBundle:Circuit')->findByUser_id($this->getUser()->getId());

        foreach ($circuits_du_user as $circuit) {
            $circuits[] = array("id" => $circuit->getId(), "name" => $circuit->getName(), "ordre" => $circuit->getOrdre(), "groupe" => false);
        }

        if($forClasseur) {
            $groupes_du_user = $em->getRepository('SesileUserBundle:UserGroupe')->findByUser($this->getUser());
            foreach($groupes_du_user as $group) {
                $circuits[] = array(
                    "id" => $group->getGroupe()->getId(),
                    "name" => $group->getGroupe()->getNom(),
                    "ordre" => $this->getCircuitFromgroupForUser($this->getUser(), $group->getgroupe()),
                    "groupe" => true
                );
            }
        }


        return array('users' => $users, "circuits" => $circuits);
    }

    /**
     * @Route("/gerer/", name="gestion_circuit")
     * @Template()
     */
    public function indexAction()
    {
        return $this->newAction(false);
    }

    /**
     * @Route("/", name="create_circuit")
     * @Method("POST")
     */
    public function createAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $circuit = new Circuit();
        $circuit->setName($request->request->get('circuit_name'));
        $circuit->setOrdre($request->request->get('circuit'));
        $circuit->setUserId($this->getUser()->getId());

        $em->persist($circuit);
        $em->flush();

        $error = false;
        if (!$error) {
            $this->get('session')->getFlashBag()->add(
                'success',
                'Circuit créé avec succès !'
            );
        }

        return $this->redirect($this->generateUrl('gestion_circuit'));
    }

    /**
     * @Route("/liste", name="circuits_favoris", options={"expose"=true})
     */
    public function listAction() {
        $em = $this->getDoctrine()->getManager();
        $circuits_du_user = $em->getRepository('SesileCircuitBundle:Circuit')->findAll();

        $circuits = array();
        foreach ($circuits_du_user as $circuit) {
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
    public function deleteAction(Request $request)
    {
        $id_circuit = $request->request->get('id');
        $em = $this->getDoctrine()->getManager();
        try {
            $circuit = $em->getRepository('SesileCircuitBundle:Circuit')->find($id_circuit);
            $em->remove($circuit);
            $em->flush();
        } catch (Exception $e) {
            return new Response("NOK");
        }

        return new Response("OK");
    }

    /**
     * Retourne le circuit associé à un groupe pour un user donné
     * @param Groupe $group
     * @param User $user
     * @return string les id user du circuit dans l'ordre
     */
    private function getCircuitFromgroupForUser(User $user, Groupe $group) {
        ini_set("memory_limit", 1024);

        $em = $this->getDoctrine()->getManager();
        $hierarchie = $em->getRepository('SesileUserBundle:UserGroupe')->findBy(array("groupe", $group), array("user", "DESC"));

        $parent = false;
        $ordre = array();

        $parent = $hierarchie->getParent();
        $user_id = $hierarchie->getUser()->getId();

        $ordre = $user_id.",";
/*
        while($parent !== 0) {
            $userobj = $em->getRepository('SesileUserBundle:User')->findById($user_id);
            $query = $repo->createQueryBuilder('p')
                ->where('p.groupe = :groupe')
                ->andWhere('p.user = :user')
                ->setParameter('groupe', $group)
                ->setParameter('user', $userobj)
                ->getQuery();

            $hierarchie = $query->getResult();
            $hierarchie = $hierarchie[0];
            $parent = $hierarchie->getParent();
            $user_id = $hierarchie->getUser()->getId();
            $ordre = $user_id.",";
        }
*/
        var_dump($ordre);exit;

        return $ordre;
    }
}