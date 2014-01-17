<?php

namespace Sesile\MainBundle\Controller;

use Sesile\CircuitBundle\Controller\CircuitController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Yaml\Yaml;

class AdminController extends Controller
{
    /**
     * @Route("/admin", name="admin")
     *
     *
     */
    public function PageAdminAction(Request $request)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            // Sinon on déclenche une exception « Accès interdit »
            return $this->render('SesileMainBundle:Default:errorrestricted.html.twig');
        }
        $defaultData = array('msg' => 'Type your message here');
        $form = $this->createFormBuilder($defaultData)
            ->add('msg', 'textarea', array('label' => 'Message d\'accueil',))
            ->add('submit', 'submit', array('label' => 'Mettre à jour', 'attr' => array('class' => 'btn btn-success'),))
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->bind($request);


            // $data is a simply array with your form fields
            // like "query" and "category" as defined above.
            $data = $request->request->get('msg');
            //   var_dump($data);exit;

        }

        return $this->render('SesileMainBundle:Default:admin.html.twig', array('form' => $form->createView(),));

    }

}
