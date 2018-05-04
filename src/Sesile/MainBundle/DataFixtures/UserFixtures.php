<?php


namespace Sesile\MainBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sesile\MainBundle\Entity\Collectivite;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class UserFixtures
 * @package Sesile\MainBundle\DataFixtures
 */
class UserFixtures extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    const USER_ONE_REFERENCE = 'user-one';
    const USER_TWO_REFERENCE = 'user-two';

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
        // Get our userManager, you must implement `ContainerAwareInterface`
        $userManager = $this->container->get('fos_user.user_manager');

        // Create our user and set details
        $user = $userManager->createUser();
        $user->setUsername('username');
        $user->setEmail('email@domain.com');
        $user->setPlainPassword('password');
        //$user->setPassword('3NCRYPT3D-V3R51ON');
        $user->setEnabled(true);
        $user->setRoles(array('ROLE_ADMIN'));
        $user->setCollectivite($this->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE));

        // Update the user
        $userManager->updateUser($user, true);
        $this->addReference(self::USER_ONE_REFERENCE, $user);

        // Create our user and set details
        $user2 = $userManager->createUser();
        $user2->setUsername('username2');
        $user2->setEmail('email2@domain.com');
        $user2->setPlainPassword('password2');
        //$user->setPassword('3NCRYPT3D-V3R51ON');
        $user2->setEnabled(true);
        $user2->setRoles(array('ROLE_USER'));
        $user2->setCollectivite($this->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE));

        // Update the user
        $userManager->updateUser($user2, true);
        $this->addReference(self::USER_TWO_REFERENCE, $user2);
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

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}