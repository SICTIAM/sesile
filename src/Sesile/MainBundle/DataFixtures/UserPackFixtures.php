<?php


namespace Sesile\MainBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sesile\ClasseurBundle\Entity\TypeClasseur;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\UserBundle\Entity\User;
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
     * @param string $name
     * @param Collectivite $collectivite
     * @param User[] $users
     *
     * @return UserPack
     */
    public static function aValidUserPack($name = 'The User Pack', Collectivite $collectivite, array $users = [])
    {
        $userPack = new UserPack();
        $userPack
            ->setNom($name)
            ->setCollectivite($collectivite)
            ->setCreation(new \DateTime())
        ;
        foreach ($users as $user) {
            $userPack->addUser($user);
        }

        return $userPack;
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