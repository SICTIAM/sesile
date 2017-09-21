<?php
namespace Sesile\ClasseurBundle\Command;

use Sesile\ClasseurBundle\Entity\TypeClasseur;
use Sesile\MainBundle\Entity\Collectivite;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
        $types = $em->getRepository("SesileClasseurBundle:TypeClasseur")->findAll();

        if ($collectivites !== null) {

            foreach ($collectivites as $key => $collectivite) {

                if ($types !== null) {
                    foreach ($types as $type) {

                        if ($key == 0) {
                            $type->setCollectivites($collectivite);

                            $em->persist($type);
                        } else {
                            $newType = new TypeClasseur();
                            $newType->setNom($type->getNom());
                            $newType->setCreation($type->getCreation());
                            $newType->setSupprimable($type->getSupprimable());
                            $newType->setCollectivites($collectivite);

                            $em->persist($newType);
                        }



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
