<?php


namespace Sesile\MigrationBundle\Tests\Manager;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\SesileMigrationFixtures;
use Sesile\MainBundle\Domain\Message;
use Sesile\MigrationBundle\Entity\SesileMigration;
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
                CollectiviteFixtures::class
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

}