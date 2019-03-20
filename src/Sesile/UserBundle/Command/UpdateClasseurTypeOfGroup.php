<?php

namespace Sesile\UserBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateClasseurTypeOfGroup extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('groupe:UpdateClasseurType')
            ->setDescription('[Fix] Relier les circuits de validation (groupe) aux types basique (Helios, Acte ..) de chaque collectivité')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $groupes = $em->getRepository("SesileUserBundle:Groupe")->findAll();

        if ($groupes !== null) {

            foreach ($groupes as $key => $groupe) {
                $output->writeln('Groupe : ' . $groupe->getNom());

                $types = $groupe->getTypes();

                if ($types !== null) {
                    foreach ($types as $type) {
                        $output->writeln('Type : ' . $type->getNom());
                        //isn't editable and is a primary classeur type
                        if(!$type->getSupprimable() && in_array($type->getId(), array(1,2,3,4,5,6,7,8))) {
                            $typeOfCollectivite =
                                $em
                                    ->getRepository("SesileClasseurBundle:TypeClasseur")
                                    ->findOneBy(array("collectivites" => $groupe->getCollectivite()->getId(), "nom" => $type->getNom()));
                            if($typeOfCollectivite != null) {
                                $groupe->addType($typeOfCollectivite);
                                $groupe->removeType($type);
                            } else {
                                $output->writeln('Aucun type \'%s\' n\'a été trouvé pour la collectivite %s : ' . $type->getNom(), $groupe->getCollectivite()->getId());
                            }

                            $output->writeln(sprintf('Type %s replaced by %s for groupe %s', $type->getId(), $typeOfCollectivite->getId(), $groupe->getId()));
                        } else {
                            $output->writeln('Pas de type basic ou non supprimable trouvé pour le groupe : ' . $groupe->getNom());
                        }
                    }

                    $em->persist($groupe);

                    $em->flush();
                    $output->writeln('Types mise à jour pour le groupe : ' . $groupe->getNom());
                }

            }

        } else {
            $output->writeln('Pas de groupe trouvé');
        }

    }

}