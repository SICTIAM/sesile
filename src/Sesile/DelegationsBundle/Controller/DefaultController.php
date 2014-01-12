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
     * @Route("/ajout", name="delegation_new")
     * @Template()
     * @method("GET")
     */
    public function ajoutAction()
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $users = $userManager->findUsers();
        foreach ($users as &$user) {
            if ($user->getId() == $this->getUser()->getId()) {

            }
        }
        return array("users" => $users);
    }

    /**
     * @Route("/create", name="delegation_create")
     * @method("POST")
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $delegation = new Delegations();
        $delegation->setDelegant($this->getUser());
        $userManager = $this->container->get('fos_user.user_manager');
        $delegation->setUser($userManager->findUserBy(array('id' => $request->request->get('user'))));
        $delegation->setDebut(new \DateTime($request->request->get('debut')));
        $delegation->setFin(new \DateTime($request->request->get('fin')));

        $em->persist($delegation);
        $em->flush();

        $error = false;
        if (!$error) {
            $this->get('session')->getFlashBag()->add(
                'success',
                'Délégations ajoutée avec succès !'
            );
        }

        return $this->redirect($this->generateUrl('delegations_list'));
    }
}
