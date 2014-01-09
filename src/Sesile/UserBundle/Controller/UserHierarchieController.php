<?php
/**
 * Created by PhpStorm.
 * User: j.mercier
 * Date: 23/12/13
 * Time: 14:55
 */

namespace Sesile\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sesile\UserBundle\Entity\User;
use Sesile\UserBundle\Entity\Groupe;
use Sesile\UserBundle\Entity\UserHierarchie;

class UserHierarchieController extends Controller {

    /**
     * Creates a new Hierarchy entity
     *
     * @Route("/hierarchie", name="create_hierarchie")
     * @Template("SesileUserBundle:Default:create_hierarchie.html.twig")
     * @Method("POST")
     *
     */
    public function createHierarchy(Request $request, $IdGroupe) {

        $Groupe = new Groupe();
        $Groupe->setNom($request->request->get('Nom'));
        $Groupe->setCollectivite("1");
        $em = $this->getDoctrine()->getManager();
        $em->persist($Groupe);
        $em->flush();

        $repository  = $this->getDoctrine()
                            ->getManager()
                            ->getRepository('SesileUserBundle:Groupe');
        $Res = $repository->find($IdGroupe);
        $coordonnees = array ();
        foreach($coordonnees as $element)
        {
            $element->$request->request->get('UserId');
            array_push($element,$request->request->get('UserId'),$request->request->get('ParentId'),$Res);
            $element->$Id;
        }

    }
}