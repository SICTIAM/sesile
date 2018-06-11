<?php


namespace Sesile\MainBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\MainBundle\Entity\SesileMigration;

/**
 * Class SesileMigrationFixtures
 * @package Sesile\MainBundle\DataFixtures
 */
class SesileMigrationFixtures extends Fixture
{
    const SESILE_MIGRATION_ONE_REFERENCE = 'migration-history-one';
    const SESILE_MIGRATION_TWO_REFERENCE = 'migration-history-two';

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $collectivite = $this->getReference('collectivite-one');
        $sesileMigration = self::aValidSesileMigration($collectivite);
        $manager->persist($sesileMigration);
        $this->addReference(self::SESILE_MIGRATION_ONE_REFERENCE, $sesileMigration);
        $collectivite2 = $this->getReference('collectivite-two');
        $sesileMigration2 = self::aValidSesileMigration($collectivite2, '3333');
        $manager->persist($sesileMigration2);
        $this->addReference(self::SESILE_MIGRATION_TWO_REFERENCE, $sesileMigration2);
        $manager->flush();
    }

    public static function aValidSesileMigration(Collectivite $collectivite, $oldId = '1234')
    {
        $sesileMigration = new SesileMigration();
        $sesileMigration
            ->setCollectivity($collectivite->getId())
            ->setOldId($oldId)
            ->setDate(new \DateTime());

        return $sesileMigration;
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