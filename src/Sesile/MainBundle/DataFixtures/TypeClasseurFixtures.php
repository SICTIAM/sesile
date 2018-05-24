<?php


namespace Sesile\MainBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sesile\ClasseurBundle\Entity\TypeClasseur;
use Sesile\MainBundle\Entity\Collectivite;

/**
 * Class TypeClasseurFixtures
 * @package Sesile\MainBundle\DataFixtures
 */
class TypeClasseurFixtures extends Fixture implements DependentFixtureInterface
{

    const CLASSEUR_TYPE_ONE_REFERENCE = 'classeur-type-one';
    const CLASSEUR_TYPE_TWO_REFERENCE = 'classeur-type-two';

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

        $typeClasseur2 = new TypeClasseur();
        $typeClasseur2
            ->setNom('The second collectivite type')
            ->setCollectivites($this->getReference(CollectiviteFixtures::COLLECTIVITE_TWO_REFERENCE))
            ->setCreation(new \DateTime())
            ;
        $manager->persist($typeClasseur2);
        $manager->flush();
        $this->addReference(self::CLASSEUR_TYPE_ONE_REFERENCE, $typeClasseur);
        $this->addReference(self::CLASSEUR_TYPE_TWO_REFERENCE, $typeClasseur2);
    }

    /**
     * @param string $name
     * @param Collectivite $collectivite
     *
     * @return TypeClasseur
     */
    public static function aValidClasseurType($name = 'the type', Collectivite $collectivite)
    {
        $typeClasseur = new TypeClasseur();
        $typeClasseur
            ->setNom($name)
            ->setCollectivites($collectivite)
            ->setCreation(new \DateTime())
        ;

        return $typeClasseur;
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