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


}
