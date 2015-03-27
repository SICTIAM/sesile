<?php
namespace Sesile\ClasseurBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class classeurMigrateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('classeur:classeurmigrate')
            ->setDescription('Migration des circuits, des types et de la visibilité')
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
            // On commence par mettre les bons types
            // Helios
            $types_helios = $em->getRepository("SesileClasseurBundle:Classeur")->findByType("Helios");
            foreach($types_helios as $type_helios) {
                $type_helios->setTypes(2);
            }

            // Classique
            $types_classique = $em->getRepository("SesileClasseurBundle:Classeur")->findByType("Classique");
            foreach($types_classique as $type_classique) {
                $type_classique->setTypes(8);
            }

            // Acte
            $types_acte = $em->getRepository("SesileClasseurBundle:Classeur")->findByType("Acte");
            foreach($types_acte as $type_acte) {
                $type_acte->setTypes(3);
            }

            // Convocation
            $types_convoc = $em->getRepository("SesileClasseurBundle:Classeur")->findByType("Convocation");
            foreach($types_convoc as $type_convoc) {
                $type_convoc->setTypes(5);
            }

            // Marchés
            $types_marches = $em->getRepository("SesileClasseurBundle:Classeur")->findByType("Marchés");
            foreach($types_marches as $type_marches) {
                $type_marches->setTypes(4);
            }
            // Courrier AR
            $types_courrierar = $em->getRepository("SesileClasseurBundle:Classeur")->findByType("Courrier AR");
            foreach($types_courrierar as $type_courrierar) {
                $type_courrierar->setTypes(6);
            }

            // On continue par mettre a jour le champ ordreCircuit
            $ordreCircuits = $em->getRepository("SesileClasseurBundle:Classeur")->findAll();
            foreach($ordreCircuits as $ordreCircuit) {
                $validant = $ordreCircuit->getValidant();
                $circuit = explode(",", $ordreCircuit->getCircuit());
                $curr_validant = array_search($validant, $circuit);
                $ordreCircuit->setOrdreCircuit($curr_validant);
            }

            // On finit par la visibilite...
//            $visibles = $em->getRepository("SesileClasseurBundle:Classeur")->findAll();
//            foreach($visibles as $visible) {
//                $users = explode(",", $visible->getCircuit());
//                foreach($users as $user) {
//                    $user_id = $em->getRepository('SesileUserBundle:User')->findOneById($user);
//                    $visible->addVisible($user_id);
//                }
//                $em->persist($visible);
//            }

            // A finir


            $em->flush();
            $output->writeln('Les classeurs ont été modifié');
        }
        else {
            $output->writeln('Aucun type trouvé');
        }

    }
}
