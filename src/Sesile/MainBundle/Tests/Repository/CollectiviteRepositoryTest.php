<?php

namespace Sesile\MainBundle\Tests\Repository;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\SesileMigrationFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\MainBundle\Entity\CollectiviteRepository;

/**
 * Class CollectiviteRepositoryTest
 * @package Sesile\MainBundle\Tests\Repository
 */
class CollectiviteRepositoryTest extends WebTestCase
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
    protected function setUp()
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

    public function testGetCollectivitesListShouldReturnArrayResultOnSuccess()
    {
        $result = $this->em->getRepository(Collectivite::class)->getCollectivitesList();
        self::assertCount(3, $result);
        self::assertEquals('Sictiam Collectivité', $result[0]['nom']);
        self::assertEquals('sictiam', $result[0]['domain']);
    }

    public function testGetCollectivitesListShouldThrowExceptionOnError()
    {
        $collectiviteRepository = $this->createMock(CollectiviteRepository::class);
//        $collectiviteRepository = $this->getMockBuilder(CollectiviteRepository::class)
//            ->disableOriginalConstructor()
//            ->setMethods(
//                ['getCollectivitesList']
//            )->getMock();

        $collectiviteRepository->expects(self::once())
            ->method('getCollectivitesList')
            ->willThrowException(new \Exception('ERROR'));

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($collectiviteRepository);
        self::expectException(\Exception::class);
        $entityManager->getRepository(Collectivite::class)->getCollectivitesList();
    }

    public function testGetMigrationCollectivityListShouldReturnArrayResultOnSuccess()
    {
        $collectivity = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_TWO_REFERENCE);
        $sesileMigration = SesileMigrationFixtures::aValidSesileMigration($collectivity);
        $this->em->persist($sesileMigration);
        $this->em->flush();
        $result = $this->em->getRepository(Collectivite::class)->getMigrationCollectivityList();
        self::assertCount(2, $result);
        self::assertEquals('Sictiam Collectivité', $result[0]['nom']);
        self::assertEquals('sictiam', $result[0]['domain']);
    }

    public function testGetMigrationCollectivityListShouldReturnEmptyArrayIfAllMigrated()
    {
        $collectivityOne = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $sesileMigration = SesileMigrationFixtures::aValidSesileMigration($collectivityOne);
        $collectivityTwo = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_TWO_REFERENCE);
        $this->em->persist($sesileMigration);
        $sesileMigration = SesileMigrationFixtures::aValidSesileMigration($collectivityTwo);
        $this->em->persist($sesileMigration);
        $collectivityThree = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_THREE_REFERENCE);
        $sesileMigration = SesileMigrationFixtures::aValidSesileMigration($collectivityThree);
        $this->em->persist($sesileMigration);
        $this->em->flush();
        $result = $this->em->getRepository(Collectivite::class)->getMigrationCollectivityList();
        self::assertCount(0, $result);
    }

    public function testClearCollectivityUsers()
    {
        $collectivityOne = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        /**
         * check DB
         */
        $res = $this->em->getRepository(Collectivite::class)->find($collectivityOne->getId());
        self::assertCount(3, $res->getUsers());

        $result = $this->em->getRepository(Collectivite::class)->clearCollectivityUsers($collectivityOne->getId());
        self::assertTrue($result);
        /**
         * check DB
         */
        $this->em->clear();
        $res = $this->em->getRepository(Collectivite::class)->find($collectivityOne->getId());
        self::assertCount(0, $res->getUsers());
    }

    public function testClearCollectivityUsersShouldThrowExceptionOnError()
    {
        $collectiviteRepository = $this->createMock(CollectiviteRepository::class);

        $collectiviteRepository->expects(self::once())
            ->method('clearCollectivityUsers')
            ->willThrowException(new \Doctrine\DBAL\DBALException('ERROR SQL'));

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($collectiviteRepository);
        self::expectException(\Doctrine\DBAL\DBALException::class);
        $entityManager->getRepository(Collectivite::class)->clearCollectivityUsers(1);
    }

}