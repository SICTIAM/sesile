<?php


namespace Sesile\MainBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\UserBundle\Entity\User;
use Sesile\UserBundle\Entity\UserRole;
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
    const USER_THREE_REFERENCE = 'user-three';
    const USER_SUPER_REFERENCE = 'user-super';

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
        $user->setNom('nom1');
        $user->setPrenom('prenom1');
        $user->setUsername('username');
        $user->setEmail('user1@domain.com');
        $user->setPlainPassword('password');
        //$user->setPassword('3NCRYPT3D-V3R51ON');
        $user->setEnabled(true);
        $user->setRoles(array('ROLE_ADMIN'));
        //$user->setApitoken("token_" . md5(uniqid(rand(), true)))
        $user->setApitoken('token_09b7cedb5f9a6df29468b9ddf490ed70');
        //$this->setApisecret("secret_" . md5(uniqid(rand(), true)))
        $user->setApisecret('secret_abf9411ade3787a8e668ac534f97cf1a');
        $user->setApiactivated(true);
        //ozwillo id null for testing reasons on ozwillo provider
        $user->setOzwilloId(null);
        $user->setRole('Développeur');
        $user->addUserrole(self::aValidUserRole($user));
        $user->addUserrole(self::aValidUserRole($user, 'Développeur'));
        $user->setVille('Nice');
        $user->setCp('06000');
        $user->setPays('France');
        $user->setDepartement('Alpes-Maritimes');
        $user->setQualite('CTO');
//        $user->setCollectivite($this->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE));
        $user->addCollectivity($this->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE));

        // Update the user
        $userManager->updateUser($user, true);
        $this->addReference(self::USER_ONE_REFERENCE, $user);

        // Create our user and set details
        $user2 = $userManager->createUser();
        $user2->setNom('nom2');
        $user2->setPrenom('prenom');
        $user2->setUsername('username2');
        $user2->setEmail('email2@domain.com');
        $user2->setPlainPassword('password2');
        //$user->setPassword('3NCRYPT3D-V3R51ON');
        $user2->setEnabled(true);
        $user2->setRoles(array('ROLE_USER'));
        $user2->addUserrole(self::aValidUserRole($user, 'RH'));
        $user2->setApitoken('token_91186cb2457c4ce2d8d4a45893211a4b');
        $user2->setApisecret('secret_ef316bf44b62815f29c5f81fbf92f5c0');
        $user2->setApiactivated(true);
        $user2->setOzwilloId('76fd56f5-502b-4210-abef-c8f67e60b8ac');
//        $user2->setCollectivite($this->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE));
        $user2->addCollectivity($this->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE));

        // Update the user
        $userManager->updateUser($user2, true);
        $this->addReference(self::USER_TWO_REFERENCE, $user2);

        // Create our user and set details
        $user3 = $userManager->createUser();
        $user3->setNom('nom3');
        $user3->setPrenom('prenom3');
        $user3->setUsername('username3');
        $user3->setEmail('email3@domain.com');
        $user3->setPlainPassword('password3');
        //$user->setPassword('3NCRYPT3D-V3R51ON');
        $user3->setEnabled(true);
        $user3->setRoles(array('ROLE_USER'));
        $user3->addUserrole(self::aValidUserRole($user, 'President'));
        $user3->setApitoken('token_43286cb2457c4ce2d8d4z45893211a3g');
        $user3->setApisecret('secret_tr365bf44b62815f29c5f81fbf87f7c6');
        $user3->setApiactivated(true);
        $user3->setOzwilloId('43fd57f5-762b-2810-abtr-t6f67e56b8re');
//        $user3->setCollectivite($this->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE));
        $user3->addCollectivity($this->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE));
        $user3->addCollectivity($this->getReference(CollectiviteFixtures::COLLECTIVITE_TWO_REFERENCE));

        // Update the user
        $userManager->updateUser($user3, true);
        $this->addReference(self::USER_THREE_REFERENCE, $user3);

        // Create our user and set details
        $superUser = $userManager->createUser();
        $superUser->setNom('super nom');
        $superUser->setPrenom('super prenom');
        $superUser->setUsername('super');
        $superUser->setEmail('super@domain.com');
        $superUser->setPlainPassword('password');
        //$user->setPassword('3NCRYPT3D-V3R51ON');
        $superUser->setEnabled(true);
        $superUser->setRoles(array('ROLE_SUPER_ADMIN'));
        $superUser->setOzwilloId('8f152f70-3075-46ee-b1a2-4ed0e362626f');
//        $superUser->setCollectivite($this->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE));
        $superUser->addCollectivity($this->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE));

        // Update the user
        $userManager->updateUser($superUser, true);
        $this->addReference(self::USER_SUPER_REFERENCE, $superUser);
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

    /**
     * @param User $user
     * @param string $role
     *
     * @return UserRole
     */
    public static function aValidUserRole(User $user, $role = "Maire")
    {
        $userRole = new UserRole();
        $userRole->setUser($user);
        $userRole->setUserRoles($role);

        return $userRole;
    }

    /**
     * @param string $role
     * @param string $nom
     * @param string $email
     * @param array $collectivities
     * @param array $userRoles
     */
    public static function aValidUser(
        $role = 'ROLE_ADMIN',
        $nom = 'nom',
        $email = 'valid@email.com',
        array $collectivities = [],
        array $userRoles = []
    ) {
        $user = new User();
        $user->setNom($nom);
        $user->setPrenom('prenom1');
        $user->setUsername('username');
        $user->setEmail($email);
        $user->setPlainPassword('password');
        //$user->setPassword('3NCRYPT3D-V3R51ON');
        $user->setEnabled(true);
        $user->setRoles(array($role));
        //$user->setApitoken("token_" . md5(uniqid(rand(), true)))
        $user->setApitoken('token_09b7cedb5f9a6df29468b9ddf490ed70');
        //$this->setApisecret("secret_" . md5(uniqid(rand(), true)))
        $user->setApisecret('secret_abf9411ade3787a8e668ac534f97cf1a');
        $user->setApiactivated(true);
        //ozwillo id null for testing reasons on ozwillo provider
        $user->setOzwilloId(null);
        $user->setRole('Développeur');
        foreach ($userRoles as $role) {
            $user->addUserrole(self::aValidUserRole($user, $role));
        }
        $user->setVille('Nice');
        $user->setCp('06000');
        $user->setPays('France');
        $user->setDepartement('Alpes-Maritimes');
        $user->setQualite('CTO');
        foreach ($collectivities as $collectivity) {
            $user->addCollectivity($collectivity);
        }

        return $user;

    }
}