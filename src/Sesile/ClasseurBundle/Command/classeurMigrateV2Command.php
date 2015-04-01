<?php
namespace Sesile\ClasseurBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class classeurMigrateV2Command extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('classeur:classeurmigratev')
            ->setDescription('Migration des circuits, des types et de la visibilité V2')
//            ->addArgument('nomPort', InputArgument::REQUIRED, 'A quel port voulez-vous affecter les utilisateurs')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        $em = $this->getDoctrine()->getManager();
        $em = $this->getContainer()->get('doctrine')->getManager();
        $types = $em->getRepository("SesileClasseurBundle:Classeur")->findAll();
        if($types !== null)
        {
            // On finit par la visibilite...
            $visibles = $em->getRepository("SesileClasseurBundle:Classeur")->findAll();
            foreach($visibles as $visible) {
                $users = explode(",", $visible->getCircuit());
                $users = array_unique($users);
                foreach($users as $user) {
                    $user_id = $em->getRepository('SesileUserBundle:User')->findOneById($user);
                    if($user_id) {
                        $visible->addVisible($user_id);
                    }
                }
                $em->persist($visible);
            }


            $em->flush();
            $output->writeln('Visibilité des classeurs a été modifiée');
        }
        else {
            $output->writeln('Visibilité non modifiable');
        }

    }
}
