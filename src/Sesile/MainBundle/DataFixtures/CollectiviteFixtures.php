<?php


namespace Sesile\MainBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Sesile\MainBundle\Entity\Collectivite;

/**
 * Class ColelctiviteFixtures
 * @package Sesile\MainBundle\DataFixtures
 */
class CollectiviteFixtures extends Fixture
{
    const COLLECTIVITE_ONE_REFERENCE = 'collectivite-one';

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $collectivite = new Collectivite();
        $collectivite->setActive(true)
            ->setDomain('sictiam')
            ->setNom('Sictiam Collectivité')
            ->setSiren('123456789')
            ;
        $manager->persist($collectivite);
        $manager->flush();
        $this->addReference(self::COLLECTIVITE_ONE_REFERENCE, $collectivite);
    }
}