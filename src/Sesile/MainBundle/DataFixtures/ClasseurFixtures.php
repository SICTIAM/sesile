<?php


namespace Sesile\MainBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\UserBundle\Entity\EtapeClasseur;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ClasseurFixtures
 * @package Sesile\MainBundle\DataFixtures
 */
class ClasseurFixtures extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    const CLASSEURS_REFERENCE = 'classeur-one';

    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $etapeClasseur = new EtapeClasseur();
        $etapeClasseur
            ->addUser($this->getReference('user-one'))
            ->setEtapeValide(false)
            ->setOrdre(1)
            ->setDate(new \DateTime())
            ->addUserPack($this->getReference('user-pack-one'))
            ;
        $classeur = new Classeur();
        $classeur
            ->addVisible($this->getReference('user-one'))
            ->setUser($this->getReference('user-one'))
            ->setCircuitId($this->getReference('circuit-validation'))
            ->setCollectivite($this->getReference('collectivite-one'))
            ->setDescription('Fixture Classeur Description')
            ->setNom('Fixture Classeur')
            ->addEtapeClasseur($etapeClasseur)
            ->setType($this->getReference('classeur-type-one'))
            ->setValidation(new \DateTime('2018-06-03 11:36'))
            ->setVisibilite(1)
            ->addCopy($this->getReference('user-two'))
            ;
        $manager->persist($etapeClasseur);
        $manager->persist($classeur);
        $manager->flush();
        $this->addReference(self::CLASSEURS_REFERENCE, $classeur);
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
            UserFixtures::class,
            CircuitValidationFixtures::class,
            TypeClasseurFixtures::class,
            UserPackFixtures::class
        );
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}