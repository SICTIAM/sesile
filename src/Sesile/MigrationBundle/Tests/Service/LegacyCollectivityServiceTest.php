<?php


namespace Sesile\MigrationBundle\Tests\Service;


use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Sesile\MigrationBundle\Service\LegacyCollectivityService;
use Sesile\MigrationBundle\Tests\LegacyWebTestCase;

/**
 * Class LegacyCollectivityServiceTest
 * @package Sesile\MigrationBundle\Tests\Service
 */
class LegacyCollectivityServiceTest extends LegacyWebTestCase
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
     * @var LegacyCollectivityService
     */
    protected $service;

    public function setUp()
    {
        $this->em = $this->createMock(EntityManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->service = $this->getContainer()->get('legacy.collectivity.service');
        $this->resetLegacyTestDatabase();
        $this->loadLegacyFixtures();
        parent::setUp();
    }

    public function testGetCollectivityList()
    {
        $result = $this->service->getCollectivityList();
        self::assertCount(2, $result);
        self::assertArrayHasKey('id', $result[0]);
        self::assertArrayHasKey('name', $result[0]);
        self::assertArrayHasKey('domain', $result[0]);
    }

    public function testGetLegacyCollectivity()
    {
        $idCollectivity = 1;
        $result = $this->service->getLegacyCollectivity($idCollectivity);
        self::assertArrayHasKey('id', $result);
        self::assertArrayHasKey('nom', $result);
        self::assertArrayHasKey('domain', $result);
        self::assertArrayHasKey('image', $result);
        self::assertArrayHasKey('message', $result);
        self::assertArrayHasKey('active', $result);
        self::assertArrayHasKey('textmailnew', $result);
        self::assertArrayHasKey('textmailrefuse', $result);
        self::assertArrayHasKey('textmailwalid', $result);
        self::assertArrayHasKey('abscissesVisa', $result);
        self::assertArrayHasKey('ordonneesVisa', $result);
        self::assertArrayHasKey('abscissesSignature', $result);
        self::assertArrayHasKey('ordonneesSignature', $result);
        self::assertArrayHasKey('couleurVisa', $result);
        self::assertArrayHasKey('titreVisa', $result);
        self::assertArrayHasKey('pageSignature', $result);
        self::assertArrayHasKey('deleteClasseurAfter', $result);
        self::assertEquals($idCollectivity, $result['id']);
    }

}