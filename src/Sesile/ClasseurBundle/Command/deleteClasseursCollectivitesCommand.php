<?php
namespace Sesile\ClasseurBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Sesile\UserBundle\Entity\EtapeClasseur;

class deleteClasseursCollectivitesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('classeur:delete:all')
            ->setDescription('Suppression des circuits depuis zéro jusqu\'à une date définie.')
        ;
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $collectivites = $em->getRepository('SesileMainBundle:Collectivite')->findAll();

        foreach ($collectivites as $collectivite) {
            $nbValidDays = 'P' . $collectivite->getDeleteClasseurAfter() . 'D';
            $date = new \DateTime();
            $dateToDelete = $date->sub(new \DateInterval($nbValidDays));


            $output->writeln('===== ' . $collectivite->getNom() . ' : ' . $date->format('d-m-Y') . ' =====');

            $users = $collectivite->getUsers();
            $i = 0;
            foreach ($users as $user) {
                $classeurs = $user->getClasseurs();

                if ($classeurs !== null) {

                    foreach ($classeurs as $classeur) {

                        if($classeur->getCreation() < $dateToDelete && $classeur->getStatus() == 2) {

                            $documents = $classeur->getDocuments();
                            foreach ($documents as $document) {
                                if($document->getDownloaded() == true) {

                                    if ($file = $this->getContainer()->getParameter('upload')['fics'] . $document->getRepourl()) {
                                        if(file_exists($file) && !is_dir($file)) {
                                            unlink($file);
                                            $output->writeln("-- Suppression : " . $file);
                                        }
                                    }
                                    $em->remove($document);
                                }
                            }
                            $em->flush();

                            if (count($classeur->getDocuments()) == 0) {
                                $CUtodel = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->findByClasseur($classeur);
                                foreach ($CUtodel as $Cluser) {
                                $em->remove($Cluser);
                                }

                            $em->remove($classeur);
                                $output->writeln($classeur->getCreation()->format('d-m-Y') . ' - Classeur supprimé ' . $classeur->getId() . " : " . $classeur->getNom());
                                $i++;
                            }

                            $em->flush();
                        }

                    }

                }

            }
            $output->writeln( $i . ' classeur(s) supprimé(s).');
        }
    }
}
