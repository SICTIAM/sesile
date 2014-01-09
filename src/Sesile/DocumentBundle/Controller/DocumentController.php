<?php

namespace Sesile\DocumentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class DocumentController extends Controller
{
    /**
     * @Route("/new", name="new_document",  options={"expose"=true})
     * @Template()
     */
    public function newAction()
    {


        return array();

    }


    /**
     * @Route("/editforclasseur/{id}", name="edit_document_for_classeur",  options={"expose"=true})
     * @Template()
     */
    public function editForClasseurAction(Request $request, $id)
    {


        return array();

    }


    /**
     * @Route("/{id}", name="show_document",  options={"expose"=true})
     * @Template()
     */
    public function showAction(Request $request, $id)
    {

        return array();

    }


}
