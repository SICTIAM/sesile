<?php


namespace Sesile\MainBundle\Tests\Manager;

use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Psr\Log\LoggerInterface;
use Sesile\MainBundle\Domain\Message;
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
    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected function setUp()
    {
        $this->em = $this->createMock(EntityManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);
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
        $collectiviteManager = new CollectiviteManager($this->em, $this->logger);
        $result = $collectiviteManager->getCollectivitesList();
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        self::assertCount(2, $result->getData());
        $data = $result->getData();
        self::assertEquals('Sictiam Collectivité', $data[0]['nom']);
        self::assertEquals('sictiam', $data[0]['domain']);
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
        $collectiviteManager = new CollectiviteManager($this->em, $this->logger);
        $result = $collectiviteManager->getCollectivitesList();
        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
        self::assertNull($result->getData());
    }

}