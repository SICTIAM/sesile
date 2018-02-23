<?php

namespace Sesile\UserBundle\Command;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;

class NotificationDelayedClasseurCommand extends Command {

    private $logger;
    private $entityManager;
    private $mailer;
    private $container;

    public function __construct(LoggerInterface $logger, EntityManager $entityManager, Container $container, \Swift_Mailer $mailer) {
        parent::__construct();
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->container = $container;
        $this->mailer = $mailer;
    }

    protected function configure() {
        $this
            ->setName('sesile:user:delayedClasseurs')
            ->setDescription('Notify users when their classeurs are delayed');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $emailConstraint = new EmailConstraint();
        $this->logger->info('The delayed classeurs notification script started');
        $users = $this->entityManager
            ->getRepository('SesileUserBundle:User')
            ->findByEnabled(true);
        $message = \Swift_Message::newInstance()
            ->setFrom($this->container->getParameter('email_sender_address'))
            ->setContentType('text/html');

        foreach ($users as $key => $user) {
            $errorsString = "";
            $classeursDelayed = $this->entityManager
                ->getRepository('SesileClasseurBundle:Classeur')
                ->findDelayedClasseursByUser($user->getId());
            $nbClasseursDelayed = count($classeursDelayed);
            if($nbClasseursDelayed > 0) {
                $this->logger
                    ->debug('{user} have {count} delayed classeurs',
                        array("user"=> $user->getNom(), "count" => $nbClasseursDelayed));
                $emailConstraint->message = "L'adresse email " . $user->getEmail() . " n'est pas valide.";
                $errors = $this->container->get('validator')->validate($user->getEmail(), $emailConstraint);
                if (count($errors) > 0) {
                    $errorsString .= (string) $errors;
                    $this->logger
                        ->error('This email adress {mail} is not valid, errors : {errors} ',
                            array('erros' => $errorsString, 'mail' => $user->getEmail()));
                } else {
                    $message->setSubject(sprintf("Vous avez %s %s en retard", $nbClasseursDelayed, $nbClasseursDelayed > 1 ? "classeurs" : "classeur"));
                    $message->setBody(
                        $this->container
                            ->get('templating')
                            ->render( 'SesileUserBundle:Notifications:DelayedClasseurs.html.twig',
                                array("classeurs" => $classeursDelayed, "user" => $user)), 'text/html');
                    $message->setTo($user->getEmail());
                    try {
                        $this->mailer->send($message);
                    } catch( \Swift_RfcComplianceException $e) {
                        $this->logger
                            ->critical('Failed to send delayed classeurs mail to recipient {mail} with error message : {error}',
                                array('mail' => $user->getEmail(), 'error' => $e->getMessage()));
                    }
                }
            }
        }
        $this->logger->info('The delayed classeurs notification script finished');
    }
}