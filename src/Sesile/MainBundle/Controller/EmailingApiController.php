<?php

namespace Sesile\MainBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sesile\MainBundle\Entity\Aide;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;

/**
 * @Security("is_granted('ROLE_SUPER_ADMIN')")
 * @Rest\Route("/apirest/emailing", options = { "expose" = true })
 */
class EmailingApiController extends Controller
{

    /**
     * @Rest\View("statusCode=Response::HTTP_CREATED")
     * @Rest\Post("/new")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     * @param Request $request
     * @return Aide|\Symfony\Component\Form\Form
     */
    public function postAction(Request $request)
    {
        $form = $this->emailingForm();
        $form->submit($request->request->all());

        if ($form->isValid()) {

            $users = $this->getDoctrine()->getManager()->getRepository('SesileUserBundle:User')->findByEnabled(true);
            $data = $form->getData();

            $message = \Swift_Message::newInstance()
                ->setSubject($data['sujet'])
                ->setFrom($this->container->getParameter('email_sender_address'))
                ->setBody($data['message'])
                ->setContentType('text/html');

            $errorsString = "";
            foreach ($users as $key => $user) {

                $email = $user->getEmail();
                $emailConstraint = new EmailConstraint();
                $emailConstraint->message = "L'adresse email " . $email . " n'est pas valide.";

                $errors = $this->get('validator')->validate(
                    $email,
                    $emailConstraint
                );

                if (count($errors) > 0) {
                    $errorsString .= (string) $errors;

                    return $errorsString;

                }
                else {
                    $message->setTo($email);
                    $this->get('mailer')->send($message);
                }
            }

            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Formulaire d envoie d emailing
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function emailingForm() {
        return $this->createFormBuilder(null, array('csrf_protection' => false))
            ->add('sujet')
            ->add('message')
            ->getForm();
    }
}
