<?php

namespace Sesile\DelegationsBundle\Controller;

use Sesile\DelegationsBundle\Entity\Delegations;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="delegations_list")
     * @Template()
     */
    public function indexAction()
    {
        $repository = $this->getDoctrine()->getRepository('SesileDelegationsBundle:delegations');

        $query = $repository->createQueryBuilder('p')
            ->where('p.delegant = :delegant')
            ->setParameter('delegant', $this->getUser())
            ->andWhere('p.fin >= :fin')
            ->setParameter('fin', new \DateTime())
            ->orderBy('p.debut', 'ASC')
            ->getQuery();

        $delegations = $query->getResult();

        return array(
            'delegations' => $delegations
        );
    }

    /**
     * @Route("/recues", name="delegations_recues")
     * @Template()
     */
    public function index_recuesAction()
    {
        return $this->recuesAction();
    }

    /**
     * @Route("/liste_recues", name="delegations_recues_list")
     * @Template("SesileDelegationsBundle:Default:liste.html.twig")
     */
    public function recuesAction()
    {
        $repository = $this->getDoctrine()->getRepository('SesileDelegationsBundle:delegations');

        $query = $repository->createQueryBuilder('p')
            ->where('p.user = :user')
            ->setParameter('user', $this->getUser())
            ->andWhere('p.fin >= :fin')
            ->setParameter('fin', new \DateTime())
            ->orderBy('p.debut', 'ASC')
            ->getQuery();

        $delegations = $query->getResult();

        return array(
            'delegations' => $delegations
        );
    }


    /**
     * @Route("/ajout", name="delegation_new")
     * @Template()
     * @method("GET")
     */
    public function ajoutAction()
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $users = $userManager->findUsers();
        foreach ($users as $index => $user) {
            if ($user->getId() == $this->getUser()->getId()) {
                unset($users[$index]);
            }
        }


        $delegsdonnees=$this->getDoctrine()->getRepository('SesileDelegationsBundle:delegations')->getDelegationsGivenFromNow($this->getUser());

        $delegs = array();


        foreach($delegsdonnees as $d){

            $delegs[]=array("debut"=>$d->getDebut()->getTimestamp(), "fin"=>$d->getFin()->getTimestamp());
        }




        return array("users" => $users, "delegs"=>$delegs);
    }

    /**
     * @Route("/create", name="delegation_create")
     * @method("POST")
     */
    public function createAction(Request $request)
    {

        $delegation = new Delegations();
        $delegation->setDelegant($this->getUser());
        $userManager = $this->container->get('fos_user.user_manager');
        $delegation->setUser($userManager->findUserBy(array('id' => $request->request->get('user'))));
        list($d, $m, $a) = explode("/", $request->request->get('debut'));
        $debut = new \DateTime($m . "/" . $d . "/" . $a);
        $delegation->setDebut($debut);
        list($d, $m, $a) = explode("/", $request->request->get('fin'));
        $fin = new \DateTime($m . "/" . $d . "/" . $a);
        $delegation->setFin($fin);


        $repository = $this->getDoctrine()->getRepository('SesileDelegationsBundle:delegations');


       $repository->addDelegationWithFusion($delegation);


        $this->get('session')->getFlashBag()->add(
            'success',
            'Délégations ajoutée avec succès !'
        );


        return $this->redirect($this->generateUrl('delegations_list'));
    }

    /**
     * @Route("/delete", name="delegation_delete")
     * @method("POST")
     */
    public function deleteAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $delegation = $em->getRepository('SesileDelegationsBundle:Delegations')->find($request->request->get("id"));
        if (!$delegation) {
            throw $this->createNotFoundException('Unable to find Classeur entity.');
        }
        $em->remove($delegation);
        $em->flush();


        return $this->redirect($this->generateUrl('delegations_list'));
    }

    /**
     * @Route("/edit/{id}", name="delegation_edit")
     * @method("GET")
     * @template("SesileDelegationsBundle:Default:edit.html.twig")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $delegation = $em->getRepository('SesileDelegationsBundle:Delegations')->find($id);

        $userManager = $this->container->get('fos_user.user_manager');
        $users = $userManager->findUsers();
        foreach ($users as &$user) {
            if ($user->getId() == $this->getUser()->getId()) {

            }
        }

        if (!$delegation) {
            throw $this->createNotFoundException('Unable to find délégation entity.');
        }

        return array("delegation" => $delegation, "users" => $users);
    }

    /**
     * @Route("/update", name="delegation_update")
     * @method("POST")
     */
    public function updateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $delegation = $em->getRepository('SesileDelegationsBundle:Delegations')->find($request->request->get("id"));
        if (!$delegation) {
            throw $this->createNotFoundException('Unable to find délégation entity.');
        }
        $userManager = $this->container->get('fos_user.user_manager');
        $delegation->setUser($userManager->findUserBy(array('id' => $request->request->get('user'))));
        list($d, $m, $a) = explode("/", $request->request->get('debut'));
        $debut = new \DateTime($m . "/" . $d . "/" . $a);
        $delegation->setDebut($debut);
        list($d, $m, $a) = explode("/", $request->request->get('fin'));
        $fin = new \DateTime($m . "/" . $d . "/" . $a);
        $delegation->setFin($fin);


        $repository = $this->getDoctrine()->getRepository('SesileDelegationsBundle:delegations');


        $repository->modifyDelegationWithFusion($delegation);


        $this->get('session')->getFlashBag()->add(
            'success',
            'Délégations modifiée avec succès !'
        );



        return $this->redirect($this->generateUrl('delegations_list'));
    }
}
