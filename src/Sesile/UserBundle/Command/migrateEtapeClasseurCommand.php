<?php
namespace Sesile\UserBundle\Command;

use Sesile\UserBundle\Entity\UserRole;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class migrateEtapeClasseurCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('etapeclasseur:migrate:valid')
            ->setDescription('Migration du processus de validation des étapes du classeur')//            ->addArgument('nomPort', InputArgument::REQUIRED, 'A quel port voulez-vous affecter les utilisateurs')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $classeurs = $em->getRepository("SesileClasseurBundle:Classeur")->findAll();

        foreach ($classeurs as $classeur) {


            if (null !== $classeur->getCircuit()) {

                if ($classeur->getCircuit() != '' && $classeur->getCircuit() != null) {
                    $usersValidant = explode(',', $classeur->getCircuit());
                } else {
                    $usersValidant = null;
                }
                $etapeValidant = explode(',', $classeur->getOrdreValidant());

                $etapesClasseur = $em->getRepository('SesileUserBundle:EtapeClasseur')->findBy(
                    array('classeur' => $classeur),
                    array('ordre'    => 'ASC')
                );

                $output->writeln('============================');

                foreach ($etapesClasseur as $key => $etapeClasseur) {

                    $output->writeln('Etape classeur : ' . $etapeClasseur->getId());

                    if (in_array($etapeClasseur->getId(), $etapeValidant)) {

                        $output->writeln('User validant : ' . $etapeClasseur->getId());

                        $etapeClasseur->setEtapeValide(true);

                        $output->writeln('count uservalidant : ' . count($usersValidant));
                        if ($usersValidant != null && array_key_exists($key, $usersValidant)) {
                            $userValidant = $em->getrepository('SesileUserBundle:User')->findOneById($usersValidant[$key]);

                            $etapeClasseur->setUserValidant($userValidant);
                            $output->writeln('User validant : ' . $userValidant->getNom());
                        }

                        $em->persist($etapeClasseur);
                        $em->flush();
                    }
                }

                $output->writeln('Migration classeur ' . $classeur->getNom() . ' ok.');

            }

        }


        $output->writeln('=========== Migration effectuée !!! ==========');
    }
}
