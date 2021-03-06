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
            'delegations' => $delegations,
            "menu_color" => "jaune"
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
    public function recuesAction() {
        $repository = $this->getDoctrine()->getRepository('SesileDelegationsBundle:delegations');
        $delegations = $repository->getDelegationsWhoHasMeAsDelegateRecursively($this->getUser());
        return array(
            'delegations' => $delegations,
            "menu_color" => "jaune"
        );
    }


    /**
     * @Route("/liste_all", name="delegations_all_list")
     * @Template("SesileDelegationsBundle:Default:liste_all.html.twig")
     */
    public function recuesAllAction() {
        $repository = $this->getDoctrine()->getRepository('SesileDelegationsBundle:delegations');
        $delegations = $repository->getDelegationsWhoHasMeAsDelegateRecursively($this->getUser());
        $donnees=$this->getDoctrine()->getRepository('SesileDelegationsBundle:delegations')->getDelegationsGivenFromNow($this->getUser());

        return array(
            'delegations' => $delegations,
            'donnees' => $donnees,
            "menu_color" => "jaune"
        );
    }


    /**
     * @Route("/ajout", name="delegation_new")
     * @Template()
     * @method("GET")
     */
    public function ajoutAction() {
//        $userManager = $this->container->get('fos_user.user_manager');
//        $users = $userManager->findUsers();
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('SesileUserBundle:User')->findBy(
            array('collectivite' => $this->get("session")->get("collectivite"), 'enabled' => 1),
            array('Nom' => 'ASC')
        );
        foreach ($users as $index => $user) {
            if ($user->getId() == $this->getUser()->getId()) {
                unset($users[$index]);
            }
        }

        $delegsdonnees=$this->getDoctrine()->getRepository('SesileDelegationsBundle:delegations')->getDelegationsGivenFromNow($this->getUser());
        $delegsrecues = $this->getDoctrine()->getRepository('SesileDelegationsBundle:delegations')->getDelegationsWhoHasMeAsDelegateRecursively($this->getUser());
        $delegs = array();

        foreach($delegsdonnees as $d){
            $delegs[]=array("debut"=>$d->getDebut()->getTimestamp(), "fin"=>$d->getFin()->getTimestamp());
        }
        $delegsgiven = array();

        foreach($delegsrecues as $d) {
            $delegsgiven[]=array("userid"=>$d->getDelegant()->getId(), "debut"=>$d->getDebut()->getTimestamp(), "fin"=>$d->getFin()->getTimestamp());
        }

        return array("users" => $users, "delegs"=>$delegs,"recues"=>$delegsgiven, "menu_color" => "jaune" );
    }

    /**
     * @Route("/create", name="delegation_create")
     * @method("POST")
     */
    public function createAction(Request $request) {
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

        $messageDelegue = \Swift_Message::newInstance()
            ->setSubject("Nouvelle délégation reçue")
            ->setFrom($this->container->getParameter('email_sender_address'))
            ->setTo($delegation->getUser()->getEmail())
            ->setBody($this->renderView( 'SesileDelegationsBundle:Notifications:delegueCreation.html.twig',array("delegation"=>$delegation) ), 'text/html');
        $this->get('mailer')->send($messageDelegue);

        $this->get('session')->getFlashBag()->add(
            'success',
            'Délégation ajoutée avec succès !'
        );


        return $this->redirect($this->generateUrl('delegations_list'));
    }

    /**
     * @Route("/delete/{id}", name="delegation_delete")
     * @method("get")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $delegation = $em->getRepository('SesileDelegationsBundle:Delegations')->find($id);
        if (!$delegation) {
            throw $this->createNotFoundException('Unable to find Classeur entity.');
        }

        $messageDelegue = \Swift_Message::newInstance()
            ->setSubject("Délégation annulée")
            ->setFrom($this->container->getParameter('email_sender_address'))
            ->setTo($delegation->getUser()->getEmail())
            ->setBody($this->renderView( 'SesileDelegationsBundle:Notifications:delegationSuppression.html.twig',array("delegation"=>$delegation) ), 'text/html');
        $this->get('mailer')->send($messageDelegue);

        $em->remove($delegation);
        $em->flush();



        $this->get('session')->getFlashBag()->add(
            'success',
            'Délégation supprimée avec succès !'
        );
        return $this->redirect($this->generateUrl('delegations_list'));
    }

    /**
     * @Route("/edit/{id}", name="delegation_edit")
     * @method("GET")
     * @template("SesileDelegationsBundle:Default:edit.html.twig")
     */
    public function editAction($id) {
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

        return array("delegation" => $delegation, "users" => $users, "menu_color" => "jaune");
    }

    /**
     * @Route("/update", name="delegation_update")
     * @method("POST")
     */
    public function updateAction(Request $request) {
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
