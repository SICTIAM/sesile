<?php

namespace Sesile\ClasseurBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sesile\ClasseurBundle\Entity\Classeur as Classeur;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ClasseurApiController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @return array
     * @Rest\View()
     * @Method("get")
     *
     */
    public function listAction()
    {

        $classeurs = $this->getDoctrine()->getManager()->getRepository('SesileClasseurBundle:Classeur')->findAll();

        $classeurView = array();
        foreach ($classeurs as $classeur) {
            $classeurView[] = array('id' => $classeur->getId(),
                'nom' => $classeur->getNom(), 'description' => $classeur->getDescription(),
                'creation' => $classeur->getCreation(),
                'validation' => $classeur->getValidation(),
                'type' => $classeur->getType()->getId(),
                'visibilite' => $classeur->getVisibilite(),
                'circuit' => $classeur->getCircuit(),
                'status' => $classeur->getStatus(),
            );
        }

//        return $view = $this->view($classeurView, 200);
        return $classeurView;

    }

    /**
     * @Rest\View()
     * @Method("get")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param Classeur $classeur
     * @return array
     * @internal param $id
     */
    public function getAction(Classeur $classeur)
    {

        return $this->classeurToArray($classeur);

    }


    /**
     * @param Classeur $classeur
     * @return array
     */
    private function classeurToArray(Classeur $classeur)
    {

        $tabActions = $classeur->getActions();
        $cleanTabAction = array();
        foreach ($tabActions as $action) {
            $cleanTabAction[] = $this->actionToArray($action);
        }

        $tabDocs = $classeur->getDocuments();
        $cleanTabDocs = array();
        foreach ($tabDocs as $doc) {
            $cleanTabDocs[] = $this->docToArray($doc);
        }
        $em = $this->getDoctrine()->getManager();
        $validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($classeur);
        $tabValidant = array();
        foreach($validants as $validant)
        {
            $tabValidant[] = array(
                'id' => $validant->getId(),
                'nom'=> $validant->getPrenom() . ' ' . $validant->getNom()
            );
        }

        return array(
            'id' => $classeur->getId(),
            'nom' => $classeur->getNom(), 'description' => $classeur->getDescription(),
            'creation' => $classeur->getCreation(),
            'validation' => $classeur->getValidation(), 'type' => $classeur->getType()->getId(),
            'validant' => $tabValidant,
            'visibilite' => $classeur->getVisibilite(),
            'circuit' => $classeur->getCircuit(),
            'status' => $classeur->getStatus(),
            'documents' => $cleanTabDocs,
            'actions' => $cleanTabAction
        );
    }

    private function actionToArray($action)
    {
        return array(
            'id' => $action->getId(),
            'username' => $action->getUsername(),
            'date' => $action->getDate(),
            'action' => $action->getAction(),
            'observation' => $action->getObservation()
        );
    }

    private function docToArray($doc)
    {
        $tabHisto = $doc->getHistories();
        $cleanTabHisto = array();
        foreach ($tabHisto as $histo) {
            $cleanTabHisto[] = $this->histoToArray($histo);
        }
        return array('id' => $doc->getId(),
            'name' => $doc->getName(),
            'repourl' => $doc->getrepourl(),
            'type' => $doc->getType(),
            'signed' => $doc->getSigned(),
            'histories' => $cleanTabHisto);
    }

    private function histoToArray($histo)
    {
        return array(
            'id' => $histo->getId(),
            'date' => $histo->getDate(),
            'comment' => $histo->getComment()
        );
    }

}
