<?php


namespace Sesile\MigrationBundle\Tests\Manager;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\SesileMigrationFixtures;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Entity\CollectiviteOzwillo;
use Sesile\MainBundle\Repository\CollectiviteOzwilloRepository;
use Sesile\MigrationBundle\Entity\SesileMigration;
use Sesile\MigrationBundle\Entity\SesileMigrationRepository;
use Sesile\MigrationBundle\Manager\SesileMigrationManager;
use Sesile\MigrationBundle\Tests\LegacyWebTestCase;

class SesileMigrationManagerTest extends LegacyWebTestCase
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
     * @var SesileMigrationManager
     */
    protected $sesileMigrationManager;

    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    public function setUp()
    {
        $this->fixtures = $this->loadFixtures(
            [
                CollectiviteFixtures::class,
            ]
        )->getReferenceRepository();
        $this->em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $this->sesileMigrationManager = $this->getContainer()->get('sesile_migration.manager');
    }

    public function testCreateSesileMigration()
    {
        $collectivityOne = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $aValidMigration = SesileMigrationFixtures::aValidSesileMigration($collectivityOne);
        $result = $this->sesileMigrationManager->create($aValidMigration);
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        self::assertInstanceOf(SesileMigration::class, $result->getData());
        /**
         * check DB
         */
        $newSesileMigration = $this->em->getRepository(SesileMigration::class)->find($result->getData()->getId());
        self::assertInstanceOf(SesileMigration::class, $newSesileMigration);
    }

    public function testCreateSesileMigrationShouldReturnFalseMessageIfExceptionIsThrown()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $em = $this->createMock(EntityManager::class);
        $manager = new SesileMigrationManager($em, $logger);
        $em->expects(self::once())
            ->method('flush')
            ->willThrowException(new \Exception('ERROR'));

        $collectivityOne = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $aValidMigration = SesileMigrationFixtures::aValidSesileMigration($collectivityOne);
        $result = $manager->create($aValidMigration);
        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
        self::assertNull($result->getData());
    }

    public function testGetSesileMigrationHistory()
    {
        $em = $this->createMock(EntityManager::class);
        $repository = $this->createMock(SesileMigrationRepository::class);
        $em->expects(self::once())
            ->method('getRepository')
            ->with(SesileMigration::class)
            ->willReturn($repository);

        $repository->expects(self::once())
            ->method('getSesileMigrationHistory')
            ->willReturn($this->getFicturesMockData());
        $logger = $this->createMock(LoggerInterface::class);
        $manager = new SesileMigrationManager($em, $logger);
        $result = $manager->getSesileMigrationHistory();
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        self::assertCount(3, $result->getData());

        $migration1 = $result->getData()[0];
        self::assertEquals(1, $migration1['allowExport']);
        $migration2 = $result->getData()[1];
        self::assertEquals(0, $migration2['allowExport']);
        $migration3 = $result->getData()[2];
        self::assertEquals(0, $migration3['allowExport']);
    }

    public function testAllowOzwilloUserExportShouldReturnTrueIfAllowed()
    {
        $collectivity = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);

        $em = $this->createMock(EntityManager::class);
        $sesileMigrationRepository = $this->createMock(SesileMigrationRepository::class);
        $em->expects(self::once())
            ->method('getRepository')
            ->with(SesileMigration::class)
            ->willReturn($sesileMigrationRepository);

        $sesileMigrationMockData = SesileMigrationFixtures::aValidSesileMigration($collectivity);
        $sesileMigrationRepository->expects(self::once())
            ->method('findOneBy')
            ->willReturn($sesileMigrationMockData);

        $logger = $this->createMock(LoggerInterface::class);
        $manager = new SesileMigrationManager($em, $logger);
        $result = $manager->allowOzwilloUserExport($collectivity);
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
    }

    public function testAllowOzwilloUserExportShouldReturnFalseIfUsersAlreadyExported()
    {
        $collectivity = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $em = $this->createMock(EntityManager::class);
        $repository = $this->createMock(SesileMigrationRepository::class);
        $em->expects(self::once())
            ->method('getRepository')
            ->with(SesileMigration::class)
            ->willReturn($repository);
        //return mock SesileMiration with boolen hasUserExported true
        $sesileMigrationMockData = SesileMigrationFixtures::aValidSesileMigration(
            $collectivity,
            '123456789',
            SesileMigration::STATUS_EN_COURS,
            true
        );
        $repository->expects(self::once())
            ->method('findOneBy')
            ->willReturn($sesileMigrationMockData);
        $logger = $this->createMock(LoggerInterface::class);
        $manager = new SesileMigrationManager($em, $logger);
        $result = $manager->allowOzwilloUserExport($collectivity);
        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
    }

    public function testAllowOzwilloUserExportShouldReturnFalseIfCollectivityHasNoOzwilloConfiguration()
    {
        $collectivity = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_THREE_REFERENCE);
        $em = $this->createMock(EntityManager::class);
        $logger = $this->createMock(LoggerInterface::class);
        $manager = new SesileMigrationManager($em, $logger);
        $result = $manager->allowOzwilloUserExport($collectivity);
        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
    }

    public function testAllowOzwilloUserExportShouldReturnFalseIfSesileConfigurationEntryFound()
    {
        $collectivity = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $em = $this->createMock(EntityManager::class);
        $repository = $this->createMock(SesileMigrationRepository::class);
        $em->expects(self::once())
            ->method('getRepository')
            ->with(SesileMigration::class)
            ->willReturn($repository);
        //will not found sesileMigration so will return Null
        $repository->expects(self::once())
            ->method('findOneBy')
            ->willReturn(null);
        $logger = $this->createMock(LoggerInterface::class);
        $manager = new SesileMigrationManager($em, $logger);
        $result = $manager->allowOzwilloUserExport($collectivity);
        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
    }

    public function testAllowOzwilloUserExportShouldReturnFalseExceptionIsThrown()
    {
        $collectivity = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $em = $this->createMock(EntityManager::class);
        $repository = $this->createMock(SesileMigrationRepository::class);
        $em->expects(self::once())
            ->method('getRepository')
            ->with(SesileMigration::class)
            ->willReturn($repository);
        //will thrown exception
        $repository->expects(self::once())
            ->method('findOneBy')
            ->willThrowException(new \Exception('Errrrrrror'));
        $logger = $this->createMock(LoggerInterface::class);
        $manager = new SesileMigrationManager($em, $logger);
        $result = $manager->allowOzwilloUserExport($collectivity);
        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
    }

    private function getFicturesMockData()
    {
        return $data = [
            [
                'id' => 6,
                'collectivityId' => '1',
                'collectivityName' => 'Sictiam Collectivité',
                'siren' => '123456789',
                'status' => 'EN_COURS',
                'usersExported' => false,
                'oldId' => null,
                'date' =>
                    \DateTime::__set_state(
                        array(
                            'date' => '2018-06-15 17:34:21.000000',
                            'timezone_type' => 3,
                            'timezone' => 'Europe/Paris',
                        )
                    ),
                'instanceId' => '2ca106ea-bf4b-4d08-9dc3-082b916d8fdf',
                'serviceId' => '3436e74c-2ed0-47f0-9296-51a1073ae55d',
            ],
            [
                'id' => 7,
                'collectivityId' => '2',
                'collectivityName' => 'Sophia Antipolis',
                'siren' => '77777777',
                'status' => 'EN_COURS',
                'usersExported' => false,
                'oldId' => null,
                'date' =>
                    \DateTime::__set_state(
                        array(
                            'date' => '2018-06-15 17:34:21.000000',
                            'timezone_type' => 3,
                            'timezone' => 'Europe/Paris',
                        )
                    ),
                'instanceId' => null,
                'serviceId' => null,
            ],
            [
                'id' => 8,
                'collectivityId' => '3',
                'collectivityName' => 'Mairie de Nice',
                'siren' => '987654321',
                'status' => 'FINALISE',
                'usersExported' => true,
                'oldId' => null,
                'date' =>
                    \DateTime::__set_state(
                        array(
                            'date' => '2018-06-15 17:34:21.000000',
                            'timezone_type' => 3,
                            'timezone' => 'Europe/Paris',
                        )
                    ),
                'instanceId' => '899b2927-9f05-4d86-b998-f1c1010a9810',
                'serviceId' => '7330b26b-fc9c-4472-b385-70f803a85290',
            ],
        ];
    }

    public function testFinishMustUpdateSesileMigrationEntryWhenDone()
    {
        $collectivity = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $this->persistSesileMigration($collectivity);
        $result = $this->sesileMigrationManager->finish($collectivity);
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        /**
         * check DB
         */
        $this->em->clear();
        $sesileMigration = $this->em->getRepository(SesileMigration::class)->findOneBy(
            ['collectivityId' => $collectivity->getId()]
        );
        self::assertTrue($sesileMigration->hasUsersExported());
        self::assertEquals(SesileMigration::STATUS_FINALISE, $sesileMigration->getStatus());
    }

    public function testFinishMustReturnFalseIfSesileMigrationForCollectivityNotFound()
    {
        $collectivity = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $result = $this->sesileMigrationManager->finish($collectivity);
        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
    }

    /**
     * @param $collectivity
     * @param string $siren
     * @return SesileMigration
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function persistSesileMigration($collectivity, $siren = '123456789')
    {
        $sesileMigration = SesileMigrationFixtures::aValidSesileMigration($collectivity);
        $this->em->persist($sesileMigration);
        $this->em->flush();

        return $sesileMigration;
    }
}