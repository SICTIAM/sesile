<?php

namespace Sesile\MainBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sesile\CircuitBundle\Controller\CircuitController;
use Sesile\MainBundle\Entity\Patch;
use Sesile\MainBundle\Entity\Aide;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Session\Session;


/**
 * Class DocumentaionController
 * @package Sesile\MainBundle\Controller
 * @Route("/documentation")
 */
class DocumentationController extends Controller
{

    /**
     * @Route("/",name="indexDoc")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $patchs = $em->getRepository('SesileMainBundle:Patch')->findAll();
        $aides = $em->getRepository('SesileMainBundle:Aide')->findAll();
        return array("menu_color" => "orange", "patchs" => $patchs, 'aides' => $aides);
    }

    /**
     * @Route("/patch",name="patchs")
     * @Template()
     */
    public function patchAction()
    {
        $em = $this->getDoctrine()->getManager();
        $patchs = $em->getRepository('SesileMainBundle:Patch')->findAll();
        return array("menu_color" => "orange", "patchs" => $patchs);
    }


    /**
     * @Route("/patch/new",name="newPatch")
     * @Method("GET")
     * @Template()
     */
    public function newPatchAction()
    {
        return array("menu_color" => "orange");
    }


    /**
     * @Route("/patch/new",name="createPatch")
     * @Method("POST")
     * @Template()
     */
    public function createPatchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $patch = new Patch();
        $patch->setFile($request->files->get('file'));
        $patch->setVersion($request->request->get('version'));
        $patch->setDescription($request->request->get('description'));
        $date = new \DateTime();
        $patch->setDate($date);
        $em->persist($patch);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            'le document a été déposé avec succès !'
        );
        return $this->redirect($this->generateUrl('indexDoc'));
    }


    /**
     * @Route("/patch/edit/{id}",name="editPatch")
     * @Method("GET")
     * @Template()
     */
    public function editPatchAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $patch = $em->getRepository('SesileMainBundle:Patch')->findOneById($id);
        return array("menu_color" => "orange", "patch" => $patch);
    }

    /**
     * @Route("/patch/edit/{id}",name="modifyPatch")
     * @Method("POST")
     * @Template()
     */
    public function modifyPatchAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $patch = $em->getRepository('SesileMainBundle:Patch')->findOneById($id);
        $date = new \DateTime();
        $patch->setDate($date);
        if (!is_null($request->files->get('file'))) {
            $patch->setFile($request->files->get('file'));
        }

        $patch->setVersion($request->request->get('version'));
        $patch->setDescription($request->request->get('description'));
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            'le document a été modifié avec succès !'
        );
        return $this->redirect($this->generateUrl('indexDoc'));
    }

    /**
     * @Route("/patch/delete/{id}",name="deletePatch")
     * @Method("GET")
     * @Template()
     */
    public function deletePatchAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $patch = $em->getRepository('SesileMainBundle:Patch')->findOneById($id);
        $em->remove($patch);
        $em->flush();
        $this->get('session')->getFlashBag()->add(
            'success',
            'le document a bien été supprimé !'
        );
        return $this->redirect($this->generateUrl('indexDoc'));
    }


    /*********************************AIDE EN LIGNE********************************************/


    /**
     * @Route("/aide",name="aides")
     * @Template()
     */
    public function aideAction()
    {
        $em = $this->getDoctrine()->getManager();
        $aides = $em->getRepository('SesileMainBundle:Aide')->findAll();
        return array("menu_color" => "orange", "aides" => $aides);
    }

    /**
     * @Route("/aide/new",name="newAide")
     * @Method("GET")
     * @Template()
     */
    public function newAideAction()
    {
        return array("menu_color" => "orange");
    }

    /**
     * @Route("/aide/new",name="createAide")
     * @Method("POST")
     * @Template()
     */
    public function createAideAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $aide = new Aide();
        $date = new \DateTime();
        $aide->setDate($date);
        $aide->setFile($request->files->get('file'));
        $aide->setDescription($request->request->get('description'));
        $em->persist($aide);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            'le document a été déposé avec succès !'
        );
        return $this->redirect($this->generateUrl('indexDoc'));
    }


    /**
     * @Route("/aide/edit/{id}",name="editAide")
     * @Method("GET")
     * @Template()
     */
    public function editAideAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $aide = $em->getRepository('SesileMainBundle:Aide')->findOneById($id);
        return array("menu_color" => "orange", "aide" => $aide);
    }

    /**
     * @Route("/aide/edit/{id}",name="modifyAide")
     * @Method("POST")
     * @Template()
     */
    public function modifyAideAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $aide = $em->getRepository('SesileMainBundle:Aide')->findOneById($id);
        $date = new \DateTime();
        $aide->setDate($date);
        if (!is_null($request->files->get('file'))) {
            $aide->setFile($request->files->get('file'));
        }
        $aide->setDescription($request->request->get('description'));
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            'le document a été modifié avec succès !'
        );
        return $this->redirect($this->generateUrl('indexDoc'));
    }

    /**
     * @Route("/aide/delete/{id}",name="deleteAide")
     * @Method("GET")
     * @Template()
     */
    public function deleteAideAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $aide = $em->getRepository('SesileMainBundle:Aide')->findOneById($id);
        $em->remove($aide);
        $em->flush();
        $this->get('session')->getFlashBag()->add(
            'success',
            'le document a bien été supprimé !'
        );
        return $this->redirect($this->generateUrl('indexDoc'));
    }
}
