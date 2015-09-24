<?php
namespace Sesile\DelegationsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Sesile\UserBundle\Entity\EtapeClasseur;

class EndingDelegationMailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('delegation:endingmail')
            ->setDescription('Envoi un mail aux utilisateurs dont la délégation fini aujourd\'hui')
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
            if($delegation->getFin() == $today) {
                $messageDelegue = \Swift_Message::newInstance()
                    ->setSubject("Début de votre délégation")
                    ->setFrom($this->getContainer()->getParameter('email_sender_address'))
                    ->setTo($delegation->getUser()->getEmail())
                    ->setBody($this->getContainer()->get('templating')->render( 'SesileDelegationsBundle:Notifications:finDelegation.html.twig',array("delegation"=>$delegation) ), 'text/html');
                $this->getContainer()->get('mailer')->send($messageDelegue);
                $output->writeln($delegation->getFin()->format('d/m/Y'));

            }

        }
    }
}
