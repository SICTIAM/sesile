<?php

namespace Sesile\MainBundle\Tests\Repository;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
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
        self::assertCount(1, $result);
        self::assertEquals('Sictiam Collectivité', $result[0]['nom']);
        self::assertEquals('sictiam', $result[0]['domain']);
    }

    public function testGetCollectivitesListShouldThrowExceptionOnError()
    {
        $collectiviteRepository = $this->createMock(CollectiviteRepository::class);
//        $collectiviteRepository = $this->createPartialMock(CollectiviteRepository::class, ['getCollectivitesList']);
//        $collectiviteRepository = $this->getMockBuilder(CollectiviteRepository::class)
//            ->disableOriginalConstructor()
//            ->setMethods(
//                ['getCollectivitesList']
//            )->getMock();

        $collectiviteRepository->expects(self::once())
            ->method('getCollectivitesList')
            ->willThrowException(new \Exception('ERROR'));
//
//        $objectManager = $this->createMock(ObjectManager::class);
//        $objectManager->expects($this->any())
//            ->method('getRepository')
//            ->willReturn($collectiviteRepository);

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($collectiviteRepository);
        self::expectException(\Exception::class);
        $entityManager->getRepository(Collectivite::class)->getCollectivitesList();
    }

}