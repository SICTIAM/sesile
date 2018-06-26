<?php


namespace Sesile\MigrationBundle\Tests\Migrator;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Psr\Log\LoggerInterface;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\SesileMigrationFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\MainBundle\Manager\CollectiviteManager;
use Sesile\MigrationBundle\Manager\SesileMigrationManager;
use Sesile\MigrationBundle\Migrator\SesileMigrator;
use Sesile\MigrationBundle\Tests\LegacyWebTestCase;

class SesileMigratorTest extends LegacyWebTestCase
{
    /**
     * @var SesileMigrator
     */
    protected $sesileMigrator;
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function setUp()
    {
        $this->fixtures = $this->loadFixtures(
            [
                CollectiviteFixtures::class,
                UserFixtures::class
            ]
        )->getReferenceRepository();
        $this->resetLegacyTestDatabase();
        $this->loadLegacyFixtures();
        $this->sesileMigrator = $this->getContainer()->get('sesile.migrator');
        $this->em = $this->getContainer()
            ->get('doctrine')
            ->getManager();
        parent::setUp();
    }

    public function testHandleNewSesileMigration()
    {
        $collectivity = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_THREE_REFERENCE);
        $result = $this->sesileMigrator->hanldeNewMigration($collectivity->getId(), '777777777');
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        /**
         * check DB
         */
        $this->em->clear();
        $res = $this->em->getRepository(Collectivite::class)->find($collectivity->getId());
        self::assertEquals('777777777', $res->getSiren());
    }

    public function testHandleNewSesileMigrationShouldReturnFalseWhenCollectivityNotFound()
    {
        $result = $this->sesileMigrator->hanldeNewMigration('uknownId', '777777777');
        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
    }

    public function testHandleNewSesileMigrationShouldReturnFalseWhenErrorOnCreatingSesileMigration()
    {
        list($sesileManager, $collectivityManager, $logger) = $this->getMockObjects();
        $collectivity = CollectiviteFixtures::aValidCollectivite();

        $collectivityManager->expects(self::once())
            ->method('getCollectivity')
            ->willReturn(new Message(true, $collectivity));
        $collectivityManager->expects(self::never())
            ->method('getCollectiviteBySiren');


        $sesileManager->expects(self::once())
            ->method('create')
            ->willReturn(new Message(false, null));

        $sesileMigrator = new SesileMigrator($sesileManager, $collectivityManager, $logger);
        $result = $sesileMigrator->hanldeNewMigration(1, '777777777');
        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
    }

    public function testHandleNewSesileMigrationShouldReturnFalseWhenErrorOnSavingCollectivity()
    {
        list($sesileManager, $collectivityManager, $logger) = $this->getMockObjects();
        $collectivity = CollectiviteFixtures::aValidCollectivite();

        $collectivityManager->expects(self::once())
            ->method('getCollectivity')
            ->willReturn(new Message(true, $collectivity));

        $collectivityManager->expects(self::once())
            ->method('saveCollectivity')
            ->willReturn(new Message(false, null));

        $sesileManager->expects(self::once())
            ->method('create')
            ->willReturn(new Message(true, SesileMigrationFixtures::aValidSesileMigration($collectivity)));

        $sesileMigrator = new SesileMigrator($sesileManager, $collectivityManager, $logger);
        $result = $sesileMigrator->hanldeNewMigration(1, '777777777');
        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
    }

    /**
     * Lors qu'une collecitivty pour un siren, et on trouve une collectivité avec le même siren
     * Si cette collectivité est déjà provisioné par Ozwillo on essaye d'assigner l'entré de la table
     * collecticité_ozwillo à la collectivité qu'on migre.
     * Ici on teste que ce changement a echoué
     */
    public function testHandleNewSesileMigrationShouldReturnFalseIfErrorOnSwitchingOzwilloConf()
    {
        list($sesileManager, $collectivityManager, $logger) = $this->getMockObjects();
        $collectivity = CollectiviteFixtures::aValidCollectivite();

        $collectivityManager->expects(self::once())
            ->method('getCollectivity')
            ->willReturn(new Message(true, $collectivity));
        /**
         * build a mock collectivity with ozwillo
         */

        $aValidcollectivite = CollectiviteFixtures::aValidCollectivite();
        $collectiviteOzwillo = CollectiviteFixtures::aValidCollectiviteOzwillo($aValidcollectivite);
        $aValidcollectivite->setOzwillo($collectiviteOzwillo);
        $collectivityManager->expects(self::once())
            ->method('getCollectiviteBySiren')
            ->willReturn(new Message(true, $aValidcollectivite));
        $collectivityManager->expects(self::once())
            ->method('switchCollectivityOzwillo')
            ->willReturn(new Message(false, null));

        $sesileManager->expects(self::once())
            ->method('create')
            ->willReturn(new Message(true, SesileMigrationFixtures::aValidSesileMigration($collectivity)));

        $collectivityManager->expects(self::once())
            ->method('saveCollectivity')
            ->willReturn(new Message(true, null));

        $sesileMigrator = new SesileMigrator($sesileManager, $collectivityManager, $logger);
        $result = $sesileMigrator->hanldeNewMigration(1, '777777777');
        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
    }

    private function getMockObjects()
    {
        $sesileManager = $this->getMockBuilder(SesileMigrationManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'saveCollectivity'])
            ->getMock();

        $collectivityManager = $this->getMockBuilder(CollectiviteManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCollectivity', 'saveCollectivity', 'getCollectiviteBySiren', 'switchCollectivityOzwillo'])
            ->getMock();
        $logger = $this->createMock(LoggerInterface::class);

        return [$sesileManager, $collectivityManager, $logger];
    }


}