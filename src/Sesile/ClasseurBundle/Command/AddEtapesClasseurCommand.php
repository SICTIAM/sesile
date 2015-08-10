<?php
namespace Sesile\ClasseurBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Sesile\UserBundle\Entity\EtapeClasseur;

class AddEtapesClasseurCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('classeur:addetapesclasseur')
            ->setDescription('Migration des circuits vers les étapes classeur.')
//            ->addArgument('nomPort', InputArgument::REQUIRED, 'A quel port voulez-vous affecter les utilisateurs')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $classeurs = $em->getRepository("SesileClasseurBundle:Classeur")->findAll();

        if ($classeurs !== null) {

            foreach ($classeurs as $classeur) {
                $classeur->setOrdreEtape($classeur->getOrdreCircuit());
                $etapes_circuit = explode(',', $classeur->getCircuit());

                foreach ($etapes_circuit as $k => $etape_circuit) {
                    if (!empty($etape_circuit) AND $classeur->getOrdreValidant() === null) {
                        $step = new EtapeClasseur();

                        // Enregistrement de l etape construite
                        $step->setClasseur($classeur);
                        $classeur->addEtapeClasseur($step);

                        // Enregistrement de l ordre des etapes
                        $step->setOrdre($k);

                        // Enregistrement de l utilisateur lié à l'étape
                        // Et oui un seul utilisateur car ce n etait pas le multi-pattes donc pas de boucles et pas de groupes
                        $user = $em->getRepository('SesileUserBundle:User')->findOneById($etape_circuit);
                        $step->addUser($user);


                        $em->persist($step);
                        $em->flush();


                        // On enregistre les etapes validantes dans le classeur
                        if ($k == 0) {
                            $classeur->setOrdreValidant($step->getId());
                        } else if ($k <= $classeur->getOrdreCircuit()) {
                            $classeur->setOrdreValidant($classeur->getOrdreValidant() . ',' . $step->getId());
                        }

                        $em->flush();
                    }

                }
                $output->writeln('Classeur : ' . $classeur->getId());

            }

            foreach ($classeurs as $classeur) {
                $etapes_circuit = explode(',', $classeur->getCircuit());
                $etapes_circuit = array_slice($etapes_circuit, 0, $classeur->getOrdreEtape());

                $classeur->setCircuitZero(implode(',', $etapes_circuit));

                $em->flush();

                $output->writeln('Classeur setCircuit : ' . $classeur->getId());
            }

            $output->writeln('Les étapes classeurs ont été modifié');
        }
        else {
            $output->writeln('Aucune étape créé');
        }

    }
}
