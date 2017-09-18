<?php
namespace Sesile\ClasseurBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Sesile\UserBundle\Entity\EtapeClasseur;

class AddTypeClasseurCollectivitesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('collectivite:addtypeclasseur')
            ->setDescription('Ajout des types de classeur à toutes les collectivités.')
//            ->addArgument('nomPort', InputArgument::REQUIRED, 'A quel port voulez-vous affecter les utilisateurs')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $collectivites = $em->getRepository("SesileMainBundle:Collectivite")->findAll();

        if ($collectivites !== null) {

            foreach ($collectivites as $collectivite) {
                $types = $em->getRepository("SesileClasseurBundle:TypeClasseur")->findAll();

                if ($types !== null) {
                    foreach ($types as $type) {
                        $collectivite->addType($type);
                        $type->addCollectivite($collectivite);
                        $em->persist($collectivite);
                        $em->flush();

                        $output->writeln('Type : ' . $type->getNom());
                    }

                    $output->writeln('Type ajoutés pour  : ' . $collectivite->getNom());
                }

            }

        } else {
            $output->writeln('Aucune type créé');
        }

    }
}
