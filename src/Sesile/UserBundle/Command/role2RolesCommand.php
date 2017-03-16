<?php
namespace Sesile\UserBundle\Command;

use Sesile\UserBundle\Entity\UserRole;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class role2RolesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('user:addroles')
            ->setDescription('Migration des rôles pour ques les utilisateurs est plusieurs rôles...')//            ->addArgument('nomPort', InputArgument::REQUIRED, 'A quel port voulez-vous affecter les utilisateurs')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $users = $em->getRepository("SesileUserBundle:User")->findAll();
//        $userRole = $em->getRepository("SesileUserBundle:UserRole");

        foreach ($users as $user) {
            /*if (in_array('ROLE_AGENT_SICTIAM', $user->getRoles())) {
                // $output->writeln(print_r($user->getRoles()[0]));
                $user->setRoles(array('ROLE_SUPER_ADMIN'));
            }*/

            if (null !== $user->getRole()) {

                $userRoleExist = $em->getRepository('SesileUserBundle:UserRole')->findByUser($user);

                if (count($userRoleExist) == 0) {

                    $userRole = new userRole();
                    $userRole->setUserRoles($user->getRole());
                    $userRole->setUser($user);


                    $em->persist($userRole);
                    $em->flush();

                    $output->writeln('User rôle ajouté pour ' . $user->getPrenom() . " " . $user->getNom());
                }
            }

        }


        $output->writeln('Les nouveaux rôles sont disponibles');
    }
}
