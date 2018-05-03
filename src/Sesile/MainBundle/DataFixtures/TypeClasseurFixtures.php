<?php


namespace Sesile\MainBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sesile\ClasseurBundle\Entity\TypeClasseur;

/**
 * Class TypeClasseurFixtures
 * @package Sesile\MainBundle\DataFixtures
 */
class TypeClasseurFixtures extends Fixture implements DependentFixtureInterface
{

    const CLASSEUR_TYPE_ONE_REFERENCE = 'classeur-type-one';

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $typeClasseur = new TypeClasseur();
        $typeClasseur
            ->setNom('The type')
            ->setCollectivites($this->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE))
            ->setCreation(new \DateTime())
            ;
        $manager->persist($typeClasseur);
        $manager->flush();
        $this->addReference(self::CLASSEUR_TYPE_ONE_REFERENCE, $typeClasseur);
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
            CollectiviteFixtures::class,
        );
    }
}