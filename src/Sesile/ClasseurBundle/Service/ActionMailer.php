<?php

namespace Sesile\ClasseurBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Sesile\ClasseurBundle\Entity\Action;
use Sesile\ClasseurBundle\Entity\Classeur;

class ActionMailer
{

    private $entityManager;
    private $mailer;
    private $fromEmail;
    private $twig;

    public function __construct(EntityManagerInterface $entityManager, \Swift_Mailer $mailer, \Twig_Environment $twig, $fromEmail)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->fromEmail = $fromEmail;
        $this->twig = $twig;
    }

    public function sendNotification(Classeur $classeur, Action $action)
    {

        $subject = 'Nouveau commentaire sur le classeur ' . $classeur->getNom();

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->fromEmail)
            ->setBody(
                $this->twig
                    ->render('SesileClasseurBundle:Notifications:CommentClasseur.html.twig',
                        array(
                            "classeur" => $classeur,
                            "comment" => $action->getAction(),
                            "user" => $action->getUserAction()
                        )),
                    'text/html');

        $users = $this->entityManager->getRepository('SesileUserBundle:EtapeClasseur')->findAllUsers($classeur);

        foreach ($users as $user) {
            $message->setTo($user->getEmail());
            $this->mailer->send($message);
        }

    }

}