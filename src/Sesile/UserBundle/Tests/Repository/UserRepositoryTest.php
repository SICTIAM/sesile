<?php


namespace Sesile\UserBundle\Tests\Repository;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;
use Sesile\UserBundle\Entity\User;

class UserRepositoryTest extends SesileWebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $kernel = self::bootKernel();

        $this->em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->fixtures = $this->loadFixtures(
            [
                CollectiviteFixtures::class,
                UserFixtures::class,
            ]
        )->getReferenceRepository();
    }

    public function testGetUsersByCollectivityIdShouldReturnArrayOfUsers()
    {
        $collectivity = $this->fixtures->getReference('collectivite-one');
        $result = $this->em->getRepository(User::class)->getUsersByCollectivityId($collectivity->getId());
        self::assertCount(3, $result);
        /**
         * user One
         */
        $userOne = $this->fixtures->getReference('user-one');
        $userOneArray = [
            'id' => $userOne->getId(),
            'nom' => $userOne->getNom(),
            'prenom' => $userOne->getPrenom(),
            'email' => $userOne->getEmail(),
            'username' => $userOne->getUsername(),
        ];
        self::assertArraySubset($userOneArray, $result[0]);
        /**
         * user two
         */
        $userTwo = $this->fixtures->getReference('user-two');
        $userTwoArray = [
            'id' => $userTwo->getId(),
            'nom' => $userTwo->getNom(),
            'prenom' => $userTwo->getPrenom(),
            'email' => $userTwo->getEmail(),
            'username' => $userTwo->getUsername(),
        ];
        self::assertArraySubset($userTwoArray, $result[1]);
        /**
         * super user
         */
        $superUser = $this->fixtures->getReference('user-super');
        $superUserArray = [
            'id' => $superUser->getId(),
            'nom' => $superUser->getNom(),
            'prenom' => $superUser->getPrenom(),
            'email' => $superUser->getEmail(),
            'username' => $superUser->getUsername(),
        ];
        self::assertArraySubset($superUserArray, $result[2]);
    }

    public function testGetUsersByCollectivityIdShouldReturnEmptyArrayOfUsersIsCollectivityHaseNone()
    {
        $collectivity = $this->fixtures->getReference('collectivite-two');
        $result = $this->em->getRepository(User::class)->getUsersByCollectivityId($collectivity->getId());
        self::assertCount(0, $result);
    }

    public function testGetUsersByCollectivityIdShouldReturnEmptyArrayIfCollectivityDoesNotExist()
    {
        $result = $this->em->getRepository(User::class)->getUsersByCollectivityId('wrong-id');
        self::assertCount(0, $result);
    }

}