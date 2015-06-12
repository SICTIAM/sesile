<?php
namespace Sesile\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ModifyRoleCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('user:modifyrole')
            ->setDescription('les utilisateurs ayant le rôle AGENT_SICTIAM auront le rôle SUPER_ADMIN')//            ->addArgument('nomPort', InputArgument::REQUIRED, 'A quel port voulez-vous affecter les utilisateurs')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        $em = $this->getDoctrine()->getManager();
        $em = $this->getContainer()->get('doctrine')->getManager();
        $users = $em->getRepository("SesileUserBundle:User")->findAll();

        foreach ($users as $user) {
            if (in_array('ROLE_AGENT_SICTIAM', $user->getRoles())) {
                // $output->writeln(print_r($user->getRoles()[0]));
                $user->setRoles(array('ROLE_SUPER_ADMIN'));
            }
        }
        $em->flush();
        $output->writeln('Les droits ont été modifiés');
    }
}
