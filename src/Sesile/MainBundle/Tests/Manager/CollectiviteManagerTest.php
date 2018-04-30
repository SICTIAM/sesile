<?php


namespace Sesile\MainBundle\Tests\Manager;

use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Sesile\MainBundle\Entity\CollectiviteRepository;
use Sesile\MainBundle\Manager\CollectiviteManager;

/**
 * Class CollectiviteManagerTest
 * @package Sesile\MainBundle\Tests\Manager
 */
class CollectiviteManagerTest extends WebTestCase
{
    /**
     * @var EntityManager
     */
    protected $em;

    protected function setUp()
    {
        $this->em = $this->createMock(EntityManager::class);
    }

    public function testGetCollectiviteList()
    {
        $repository = $this->createMock(CollectiviteRepository::class);
        $this->em->expects(self::once())
            ->method('getRepository')
            ->willReturn($repository);

        $repository->expects(self::once())
            ->method('getCollectivitesList')
            ->willReturn(
                [
                    ['nom' => 'Sictiam Collectivité', 'domain' => 'sictiam'],
                    ['nom' => 'test organisation', 'domain' => 'casa'],
                ]
            );
        $collectiviteManager = new CollectiviteManager($this->em);
        $result = $collectiviteManager->getCollectivitesList();
        self::assertCount(2, $result);
        self::assertEquals('Sictiam Collectivité', $result[0]['nom']);
        self::assertEquals('sictiam', $result[0]['domain']);
    }

    public function testGetCollectiviteListWillReturnEmptyArrayWhenExceptionIsThrown()
    {
        $repository = $this->createMock(CollectiviteRepository::class);
        $this->em->expects(self::once())
            ->method('getRepository')
            ->willReturn($repository);

        $repository->expects(self::once())
            ->method('getCollectivitesList')
            ->willThrowException(new \Exception('ERROR'));
        $collectiviteManager = new CollectiviteManager($this->em);
        $result = $collectiviteManager->getCollectivitesList();
        self::assertCount(0, $result);
    }

}