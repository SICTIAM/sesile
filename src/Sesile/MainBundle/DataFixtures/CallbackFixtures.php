<?php


namespace Sesile\MainBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sesile\ClasseurBundle\Entity\Callback;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\UserBundle\Entity\User;
use Sesile\UserBundle\Entity\UserRole;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class UserFixtures
 * @package Sesile\MainBundle\DataFixtures
 */
class CallbackFixtures extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    const CALLBACK_ONE_REFERENCE = 'callback-one';
    const CALLBACK_TWO_REFERENCE = 'callback-two';

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
        $classeurOne = $this->getReference('classeur-one');

        $callback = new Callback();

        $callback->setClasseurId($classeurOne->getId());
        $callback->setUrl("http://localhost/api/pes/7c34ed4f-385e-4fc4-b37e-fe68a031b4e0");
        $manager->persist($callback);
        $manager->flush();
        $this->addReference(self::CALLBACK_ONE_REFERENCE, $callback);
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
            ClasseurFixtures::class,
        );
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}