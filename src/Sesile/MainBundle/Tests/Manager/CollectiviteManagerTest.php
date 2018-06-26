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
use Sesile\UserBundle\Entity\User;
use Sesile\UserBundle\Entity\UserRepository;

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
                ['findOneBy']
            )->getMock();
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(Collectivite::class)
            ->willReturn($repository);
        $repository->expects(self::once())
            ->method('findOneBy')
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
                ['findOneBy']
            )->getMock();
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(Collectivite::class)
            ->willReturn($repository);
        $repository->expects(self::once())
            ->method('findOneBy')
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
                ['findOneBy']
            )->getMock();
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(Collectivite::class)
            ->willReturn($repository);
        $repository->expects(self::once())
            ->method('findOneBy')
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

    public function testGetMigrationCollectivityList()
    {
        $repository = $this->createMock(CollectiviteRepository::class);
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(Collectivite::class)
            ->willReturn($repository);

        $repository->expects(self::once())
            ->method('getMigrationCollectivityList')
            ->willReturn(
                [
                    ['nom' => 'Sictiam Collectivité', 'domain' => 'sictiam'],
                    ['nom' => 'test organisation', 'domain' => 'casa'],
                ]
            );
        $result = $this->collectiviteManager->getMigrationCollectivityList();
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        self::assertCount(2, $result->getData());
        $data = $result->getData();
        self::assertEquals('Sictiam Collectivité', $data[0]['nom']);
        self::assertEquals('sictiam', $data[0]['domain']);
    }

    /**
     * switch the CollecitvityOzwillo to another collectivity
     */
    public function testSwitchCollectivityOzwillo()
    {
        $repository = $this->getMockBuilder(CollectiviteOzwillo::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['switchCollectivityId']
            )->getMock();
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(CollectiviteOzwillo::class)
            ->willReturn($repository);

        $repository->expects(self::once())
            ->method('switchCollectivityId')
            ->willReturn(true);

        $result = $this->collectiviteManager->switchCollectivityOzwillo(
            CollectiviteFixtures::aValidCollectivite(),
            CollectiviteFixtures::aValidCollectivite('test')
        );
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
    }

    /**
     * switch the CollecitvityOzwillo to another collectivity
     */
    public function testSwitchCollectivityOzwilloShouldReturnFalseIfFailed()
    {
        $repository = $this->getMockBuilder(CollectiviteOzwillo::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['switchCollectivityId']
            )->getMock();
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(CollectiviteOzwillo::class)
            ->willReturn($repository);

        $repository->expects(self::once())
            ->method('switchCollectivityId')
            ->willReturn(false);

        $result = $this->collectiviteManager->switchCollectivityOzwillo(
            CollectiviteFixtures::aValidCollectivite(),
            CollectiviteFixtures::aValidCollectivite('test')
        );
        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
    }

    public function testUpdateNotifiedToKernel()
    {
        $repository = $this->getMockBuilder(CollectiviteOzwillo::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['updateNotifiedToKernel']
            )->getMock();
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(CollectiviteOzwillo::class)
            ->willReturn($repository);

        $repository->expects(self::once())
            ->method('updateNotifiedToKernel')
            ->willReturn(true);

        $newCollectivity = CollectiviteFixtures::aValidCollectivite('toto', 'Toto');
        $result = $this->collectiviteManager->updateNotifiedToKernel($newCollectivity, 'serviceId', true);
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        self::assertInstanceOf(Collectivite::class, $result->getData());
    }

    public function testUpdateNotifiedToKernelShouldReturnErrorMessageIfFailed()
    {
        $repository = $this->getMockBuilder(CollectiviteOzwillo::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['updateNotifiedToKernel']
            )->getMock();
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(CollectiviteOzwillo::class)
            ->willReturn($repository);

        $repository->expects(self::once())
            ->method('updateNotifiedToKernel')
            ->willThrowException(new \Exception('Error'));

        $newCollectivity = CollectiviteFixtures::aValidCollectivite('toto', 'Toto');
        $result = $this->collectiviteManager->updateNotifiedToKernel($newCollectivity, 'serviceId', true);
        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
    }

    public function testGetCollectivityUsersList()
    {
        $repository = $this->createMock(UserRepository::class);
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($repository);

        $repository->expects(self::once())
            ->method('getUsersByCollectivityId')
            ->willReturn(
                [
                    [
                        'id' => 16,
                        'nom' => 'nom1',
                        'prenom' => 'prenom1',
                        'email' => 'user1@domain.com',
                        'username' => 'username',
                        'ozwilloId' => null,
                        'ville' => 'Nice',
                        'cp' => '06000',
                        'pays' => 'France',
                        'departement' => 'Alpes-Maritimes',
                        'role' => 'Développeur',
                        'qualite' => 'CTO',
                        'roles' =>[
                                0 => 'ROLE_ADMIN',
                            ]
                    ],
                    [
                        'id' => 17,
                        'nom' => 'nom2',
                        'prenom' => 'prenom',
                        'email' => 'email2@domain.com',
                        'username' => 'username2',
                        'ozwilloId' => '76fd56f5-502b-4210-abef-c8f67e60b8ac',
                        'ville' => null,
                        'cp' => null,
                        'pays' => null,
                        'departement' => null,
                        'role' => null,
                        'qualite' => null,
                        'roles' =>[]
                    ]
                ]
            );
        $result = $this->collectiviteManager->getCollectivityUsersList(1);
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        self::assertCount(2, $result->getData());
        $data = $result->getData();
        self::assertArrayHasKey('id', $data[0]);
        self::assertArrayHasKey('prenom', $data[0]);
        self::assertArrayHasKey('email', $data[0]);
        self::assertArrayHasKey('username', $data[0]);
        self::assertArrayHasKey('ozwilloId', $data[0]);
        self::assertArrayHasKey('ville', $data[0]);
        self::assertArrayHasKey('cp', $data[0]);
        self::assertArrayHasKey('pays', $data[0]);
        self::assertArrayHasKey('departement', $data[0]);
        self::assertArrayHasKey('role', $data[0]);
        self::assertArrayHasKey('qualite', $data[0]);
    }

    public function testGetCollectivityUsersListShouldReturnFalseIfExceptionIsThrown()
    {
        $repository = $this->createMock(UserRepository::class);
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($repository);

        $repository->expects(self::once())
            ->method('getUsersByCollectivityId')
            ->willThrowException(new \Exception('ERROR'));
        $result = $this->collectiviteManager->getCollectivityUsersList(1);
        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
        self::assertNull($result->getData());
    }

    public function testClearCollectivityUsers()
    {
        $repository = $this->createMock(CollectiviteRepository::class);
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(Collectivite::class)
            ->willReturn($repository);

        $repository->expects(self::once())
            ->method('clearCollectivityUsers')
            ->willReturn(true);
        $result = $this->collectiviteManager->clearCollectivityUsers(CollectiviteFixtures::aValidCollectivite());
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        self::assertTrue($result->getData());
    }

    public function testClearCollectivityUsersShouldReturnFalseIfExceptionIsThrown()
    {
        $repository = $this->createMock(CollectiviteRepository::class);
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(Collectivite::class)
            ->willReturn($repository);

        $repository->expects(self::once())
            ->method('clearCollectivityUsers')
            ->willThrowException(new \Doctrine\DBAL\DBALException('ERROR'));
        $result = $this->collectiviteManager->clearCollectivityUsers(CollectiviteFixtures::aValidCollectivite());
        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
        self::assertNull($result->getData());
    }
}