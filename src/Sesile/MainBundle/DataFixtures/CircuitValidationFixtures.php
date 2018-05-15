<?php


namespace Sesile\MainBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sesile\ClasseurBundle\Entity\TypeClasseur;
use Sesile\UserBundle\Entity\EtapeGroupe;
use Sesile\UserBundle\Entity\Groupe;

/**
 * Class CircuitValidationFixtures
 * @package Sesile\MainBundle\DataFixtures
 */
class CircuitValidationFixtures extends Fixture implements DependentFixtureInterface
{
    const CIRCUIT_VALIDATION_REFERENCE = 'circuit-validation';
    const CIRCUIT_VALIDATION_REFERENCE_TWO = 'circuit-validation-two';

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $etapeGroup = new EtapeGroupe();
        $etapeGroup
            ->setOrdre(1)
            ->addUser($this->getReference('user-one'))
            ->addUserPack($this->getReference('user-pack-one'))
        ;

        $group = new Groupe();
        $group
            ->setNom('Circuit de validation')
            ->setCollectivite($this->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE))
            ->setCreation(new \DateTime())
            ;
        $type = $this->getReference(TypeClasseurFixtures::CLASSEUR_TYPE_ONE_REFERENCE);
        $group->addType($type);
        $group->addEtapeGroupe($etapeGroup);

        $manager->persist($type);
        $manager->persist($group);
        $etapeGroup->setGroupe($group);
        /**
         * second
         */
        $etapeGroup2 = new EtapeGroupe();
        $etapeGroup2
            ->setOrdre(1)
            ->addUser($this->getReference('user-two'))
        ;

        $group2 = new Groupe();
        $group2
            ->setNom('Circuit de validation two')
            ->setCollectivite($this->getReference(CollectiviteFixtures::COLLECTIVITE_TWO_REFERENCE))
            ->setCreation(new \DateTime())
            ;
        $type = $this->getReference(TypeClasseurFixtures::CLASSEUR_TYPE_ONE_REFERENCE);
        $group2->addType($type);
        $group2->addEtapeGroupe($etapeGroup2);

        $manager->persist($group2);
        $etapeGroup2->setGroupe($group2);
        $manager->persist($etapeGroup2);
        $manager->flush();
        $this->addReference(self::CIRCUIT_VALIDATION_REFERENCE, $group);
        $this->addReference(self::CIRCUIT_VALIDATION_REFERENCE_TWO, $group2);
    }


    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    function getDependencies()
    {
        return array(
            TypeClasseurFixtures::class,
            UserPackFixtures::class
        );
    }
}