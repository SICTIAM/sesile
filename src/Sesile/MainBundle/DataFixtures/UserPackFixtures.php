<?php


namespace Sesile\MainBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sesile\ClasseurBundle\Entity\TypeClasseur;
use Sesile\UserBundle\Entity\UserPack;

/**
 * Class UserPackFixtures
 * @package Sesile\MainBundle\DataFixtures
 */
class UserPackFixtures extends Fixture implements DependentFixtureInterface
{

    const USER_PACK_ONE_REFERENCE = 'user-pack-one';

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {

        $userPack = new UserPack();
        $userPack
            ->setNom('The User Group')
            ->setCollectivite($this->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE))
            ->setCreation(new \DateTime())
            ;
        $user = $this->getReference(UserFixtures::USER_ONE_REFERENCE);
        $userPack->addUser($user);
        $manager->persist($user);
        $manager->persist($userPack);
        $manager->flush();
        $this->addReference(self::USER_PACK_ONE_REFERENCE, $userPack);
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
        );
    }
}