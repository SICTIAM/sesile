<?php
namespace Sesile\DelegationsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Sesile\UserBundle\Entity\EtapeClasseur;

class StartingDelegationMailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('delegation:startingmail')
            ->setDescription('Envoi un mail aux utilisateurs dont la délégation débute aujourd\'hui')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $delegations = $em->getRepository("SesileDelegationsBundle:Delegations")->findAll();
        /* on inverse jour et mois parceque DateTime l'exige */
        $today = new \DateTime(date('m/d/Y'));

            foreach ($delegations as $delegation)
            {
                if($delegation->getDebut() == $today) {
                    $messageDelegue = \Swift_Message::newInstance()
                        ->setSubject("Début de votre délégation")
                        ->setFrom($this->getContainer()->getParameter('email_sender_address'))
                        ->setTo($delegation->getUser()->getEmail())
                        ->setBody($this->getContainer()->get('templating')->render( 'SesileDelegationsBundle:Notifications:debutDelegation.html.twig',array("delegation"=>$delegation) ), 'text/html');
                    $this->getContainer()->get('mailer')->send($messageDelegue);
                    $output->writeln($delegation->getDebut()->format('d/m/Y'));

                }

            }

    }
}
