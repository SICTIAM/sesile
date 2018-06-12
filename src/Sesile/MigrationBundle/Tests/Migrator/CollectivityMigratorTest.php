<?php


namespace Sesile\MigrationBundle\Tests\Migrator;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\MigrationBundle\Migrator\CollectivityMigrator;
use Sesile\MigrationBundle\Service\LegacyCollectivityService;
use Sesile\MigrationBundle\Tests\LegacyWebTestCase;

class CollectivityMigratorTest extends LegacyWebTestCase
{
    /**
     * @var CollectivityMigrator
     */
    protected $collectivityMigrator;
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;
    /**
     * @var LegacyCollectivityService
     */
    protected $collectivityService;

    public function setUp()
    {
        $this->fixtures = $this->loadFixtures(
            [
                CollectiviteFixtures::class
            ]
        )->getReferenceRepository();
        $this->resetLegacyTestDatabase();
        $this->loadLegacyFixtures();
        $this->collectivityMigrator = $this->getContainer()->get('collectivity.migrator');
        $this->collectivityService = $this->getContainer()->get('legacy.collectivity.service');
        parent::setUp();
    }

    public function testMigrateCollectivity()
    {
        $oldCollectivityId = 1;
        $siren = '1234567890';
        $result = $this->collectivityMigrator->migrate($oldCollectivityId, $siren);
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        self::assertInstanceOf(Collectivite::class, $result->getData());
        $expectedCollectivityData = $this->collectivityService->getLegacyCollectivity($oldCollectivityId);
        $this->assertCollectivity($result->getData(), $expectedCollectivityData);
        //@todo add assert on oldId field in sesileMigration table
    }

    public function testMigrateCollectivityShouldFailIfEmptySirentIsGiven()
    {
        $oldCollectivityId = 1;
        $result = $this->collectivityMigrator->migrate($oldCollectivityId, '');
        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
    }

    private function assertCollectivity(Collectivite $collectivity, $legacyData)
    {
        self::assertEquals($legacyData['nom'], $collectivity->getNom());
        self::assertEquals($legacyData['domain'], $collectivity->getDomain());
        self::assertEquals($legacyData['image'], $collectivity->getImage());
        self::assertEquals($legacyData['message'], $collectivity->getMessage());
        self::assertEquals($legacyData['active'], $collectivity->getActive());
        self::assertEquals($legacyData['textmailnew'], $collectivity->getTextmailnew());
        self::assertEquals($legacyData['textmailrefuse'], $collectivity->getTextmailrefuse());
        self::assertEquals($legacyData['textmailwalid'], $collectivity->getTextmailwalid());
        self::assertEquals($legacyData['abscissesVisa'], $collectivity->getAbscissesVisa());
        self::assertEquals($legacyData['ordonneesVisa'], $collectivity->getOrdonneesVisa());
        self::assertEquals($legacyData['abscissesSignature'], $collectivity->getAbscissesSignature());
        self::assertEquals($legacyData['ordonneesSignature'], $collectivity->getOrdonneesSignature());
        self::assertEquals($legacyData['couleurVisa'], $collectivity->getCouleurVisa());
        self::assertEquals($legacyData['titreVisa'], $collectivity->getTitreVisa());
        self::assertEquals($legacyData['pageSignature'], $collectivity->getPageSignature());
        self::assertEquals($legacyData['deleteClasseurAfter'], $collectivity->getDeleteClasseurAfter());
        /**
         * New fields
         */
        self::assertEquals('123456789', $collectivity->getSiren());
        $textcopymailnew = "<p>Bonjour {{ en_copie }},</p><p>Un nouveau classeur pour lequel vous êtes en copie {{ titre_classeur }} vient d'être déposé par {{ deposant }}, pour validation à {{ validant }}, à la date du <strong>{{ date_limite | date('d/m/Y') }}.</strong></p><p>Vous pouvez visionner le classeur {{lien|raw}}</p><p>**logo_coll** {{ qualite }}<br>{{ validant }}</p>";
        self::assertEquals($textcopymailnew, $collectivity->getTextcopymailnew());
        $textcopymailwalid = "<p>Bonjour {{ en_copie }},</p><p>Un nouveau classeur pour lequel vous êtes en copie {{ titre_classeur }} vient d'être validé par {{ validant }}.</p><p>Vous pouvez visionner le classeur {{lien|raw}}</p><p>**logo_coll** {{ qualite }}<br>{{ validant }}</p>";
        self::assertEquals($textcopymailwalid, $collectivity->getTextcopymailwalid());
    }

}