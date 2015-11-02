<?php
namespace Sesile\ClasseurBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Sesile\UserBundle\Entity\EtapeClasseur;

class deleteClasseursCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('classeur:delete:date')
            ->setDescription('Suppression des circuits depuis zéro jusqu\'à une date définie.')
//            ->addArgument('dateCreated', InputArgument::REQUIRED, 'Jusqu\'à quelle date voulez-vous suppimer les classeurs en cours ?')
        ;
    }

    /*protected function interact() {

    }*/

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelper('dialog');

        $dateCreation = $dialog->ask(
            $output,
            'Entrez la date de création SVP (2015-01-01) : ',
            '2015-01-01'
        );

//        $dateCreation = $input->getArgument('dateCreated');
        $date = new \DateTime($dateCreation);
        $em = $this->getContainer()->get('doctrine')->getManager();

        $classeurs = $em->getRepository("SesileClasseurBundle:Classeur")->findBy(
            array(
                'status' => 1
            )
        );

        if ($classeurs !== null) {

            $i = 0;
            foreach ($classeurs as $classeur) {

                if($classeur->getCreation() < $date) {


                    $CUtodel = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->findByClasseur($classeur);
                    foreach ($CUtodel as $Cluser) {
                        $em->remove($Cluser);
                    }

                    $Actionstodel = $em->getRepository('SesileClasseurBundle:Action')->findByClasseur($classeur);

                    foreach ($Actionstodel as $action) {
                        $em->remove($action);
                    }

                    $em->remove($classeur);

                    $output->writeln('Classeur supprimé ' . $classeur->getId() . " : " . $classeur->getNom());
                    $i++;

                    $em->flush();
                }

            }

            $output->writeln( $i . ' classeur(s) supprimé(s).');


        }
        else {
            $output->writeln('Aucun classeurs supprimés.');
        }

//        $output->writeln($dateX);

    }
}
