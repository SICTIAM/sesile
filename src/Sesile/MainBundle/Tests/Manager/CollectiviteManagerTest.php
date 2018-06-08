<?php


namespace Sesile\MainBundle\Tests\Manager;

use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Psr\Log\LoggerInterface;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\MainBundle\Entity\CollectiviteOzwillo;
use Sesile\MainBundle\Entity\CollectiviteRepository;
use Sesile\MainBundle\Manager\CollectiviteManager;
use Sesile\MainBundle\Repository\CollectiviteOzwilloRepository;

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
    /**
     * @var CollectiviteManager
     */
    protected $collectiviteManager;

    protected function setUp()
    {
        $this->em = $this->createMock(EntityManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->collectiviteManager = new CollectiviteManager($this->em, $this->logger);
    }

    public function testGetCollectiviteList()
    {
        $repository = $this->createMock(CollectiviteRepository::class);
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(Collectivite::class)
            ->willReturn($repository);

        $repository->expects(self::once())
            ->method('getCollectivitesList')
            ->willReturn(
                [
                    ['nom' => 'Sictiam Collectivité', 'domain' => 'sictiam'],
                    ['nom' => 'test organisation', 'domain' => 'casa'],
                ]
            );
        $result = $this->collectiviteManager->getCollectivitesList();
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
            ->with(Collectivite::class)
            ->willReturn($repository);

        $repository->expects(self::once())
            ->method('getCollectivitesList')
            ->willThrowException(new \Exception('ERROR'));
        $result = $this->collectiviteManager->getCollectivitesList();
        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
        self::assertNull($result->getData());
    }

    public function testUserHasOzwilloCollectivityShouldReturnTrue()
    {
        $repository = $this->createMock(CollectiviteOzwilloRepository::class);
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(CollectiviteOzwillo::class)
            ->willReturn($repository);

        $repository->expects(self::once())
            ->method('userHasOzwilloCollectivity')
            ->willReturn(true);
        $result = $this->collectiviteManager->userHasOzwilloCollectivity('userId', 'clientId');
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        self::assertTrue($result->getData());
    }

    public function testUserHasOzwilloCollectivityShouldReturnFalseIfNoCollectivityFoundForUser()
    {

        $repository = $this->createMock(CollectiviteOzwilloRepository::class);
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(CollectiviteOzwillo::class)
            ->willReturn($repository);

        $repository->expects(self::once())
            ->method('userHasOzwilloCollectivity')
            ->willReturn(false);
        $result = $this->collectiviteManager->userHasOzwilloCollectivity('userId', 'clientId');
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        self::assertFalse($result->getData());
    }

    public function testUserHasOzwilloCollectivityShouldReturnFalseIfExceptionIsThrown()
    {
        $repository = $this->createMock(CollectiviteOzwilloRepository::class);
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(CollectiviteOzwillo::class)
            ->willReturn($repository);

        $repository->expects(self::once())
            ->method('userHasOzwilloCollectivity')
            ->willThrowException(new \Exception('ERROR'));
        $result = $this->collectiviteManager->userHasOzwilloCollectivity('userId', 'clientId');
        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
        self::assertFalse($result->getData());
    }

    public function testGetOzwilloCollectivityByClientIdShouldReturnTheOzwilloCollectiviteFound()
    {
        $repository = $this->getMockBuilder(CollectiviteOzwilloRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['findOneByClientId']
            )->getMock();
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(CollectiviteOzwillo::class)
            ->willReturn($repository);
        $aValidCollectivity = CollectiviteFixtures::aValidCollectivite();
        $aValidOzwilloCollectivity = CollectiviteFixtures::aValidCollectiviteOzwillo($aValidCollectivity);
        $repository->expects(self::once())
            ->method('findOneByClientId')
            ->willReturn($aValidOzwilloCollectivity);
        $result = $this->collectiviteManager->getOzwilloCollectivityByClientId('clientId');
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        self::assertEquals($aValidOzwilloCollectivity, $result->getData());
    }

    public function testGetOzwilloCollectivityByClientIdShouldReturnNullWhenNotFound()
    {
        $repository = $this->getMockBuilder(CollectiviteOzwilloRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['findOneByClientId']
            )->getMock();
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(CollectiviteOzwillo::class)
            ->willReturn($repository);
        $repository->expects(self::once())
            ->method('findOneByClientId')
            ->willReturn(null);
        $result = $this->collectiviteManager->getOzwilloCollectivityByClientId('clientId');
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        self::assertNull($result->getData());
    }

    public function testGetOzwilloCollectivityByClientIdShouldReturnFalseWhenExceptionIsThrougn()
    {
        $repository = $this->getMockBuilder(CollectiviteOzwilloRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['findOneByClientId']
            )->getMock();
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(CollectiviteOzwillo::class)
            ->willReturn($repository);
        $repository->expects(self::once())
            ->method('findOneByClientId')
            ->willThrowException(new \Exception('ERROR'));
        $result = $this->collectiviteManager->getOzwilloCollectivityByClientId('clientId');
        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
        self::assertNull($result->getData());
    }

    public function testGetCollectiviteBySiren()
    {
        $mockCollectivite = CollectiviteFixtures::aValidCollectivite();
        $repository = $this->getMockBuilder(CollectiviteRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['findOneBySiren']
            )->getMock();
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(Collectivite::class)
            ->willReturn($repository);
        $repository->expects(self::once())
            ->method('findOneBySiren')
            ->willReturn($mockCollectivite);

        $result = $this->collectiviteManager->getCollectiviteBySiren('123456789');
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        self::assertInstanceOf(Collectivite::class, $result->getData());
        self::assertEquals($mockCollectivite, $result->getData());
    }
    public function testGetCollectiviteBySirenShouldReturnNullIfNoneFound()
    {
        $repository = $this->getMockBuilder(CollectiviteRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['findOneBySiren']
            )->getMock();
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(Collectivite::class)
            ->willReturn($repository);
        $repository->expects(self::once())
            ->method('findOneBySiren')
            ->willReturn(null);

        $result = $this->collectiviteManager->getCollectiviteBySiren('123456789');
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        self::assertNull($result->getData());
    }

    public function testGetCollectiviteBySirenShouldReturnFalseWhenExceptionIsThrougn()
    {
        $repository = $this->getMockBuilder(CollectiviteRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['findOneBySiren']
            )->getMock();
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(Collectivite::class)
            ->willReturn($repository);
        $repository->expects(self::once())
            ->method('findOneBySiren')
            ->willThrowException(new \Exception('ERROR'));
        $result = $this->collectiviteManager->getCollectiviteBySiren('123456789');
        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
        self::assertNull($result->getData());
    }

    public function testSaveCollectivity()
    {
        $newCollectivity = CollectiviteFixtures::aValidCollectivite('toto', 'Toto');
        $result = $this->collectiviteManager->saveCollectivity($newCollectivity);
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        self::assertInstanceOf(Collectivite::class, $result->getData());
        self::assertEquals($newCollectivity->getDomain(), $result->getData()->getDomain());
    }

}