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
        $this->resetDatabase();
        $this->loadLegacyFixtures();
        parent::setUp();
    }

    public function testGetCollectivityList()
    {
        $result = $this->service->getCollectivityList();
        self::assertCount(2, $result);
        self::assertArrayHasKey('id', $result[0]);
        self::assertArrayHasKey('name', $result[0]);
    }

}