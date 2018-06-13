<?php


namespace Sesile\MigrationBundle\Tests\Manager;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Psr\Log\LoggerInterface;
use Sesile\MainBundle\Domain\Message;
use Sesile\MigrationBundle\Manager\LegacyCollectivityManager;
use Sesile\MigrationBundle\Service\LegacyCollectivityService;
use Sesile\MigrationBundle\Tests\LegacyWebTestCase;

class LegacyCollectivityManagerTest extends LegacyWebTestCase
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var LegacyCollectivityManager
     */
    protected $legacyCollectivityManager;
    /**
     * @var LegacyCollectivityService
     */
    protected $service;

    public function setUp()
    {
        $this->service = $this->getMockBuilder(LegacyCollectivityService::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['fetchData', 'getLegacyCollectivityList', 'fetchAllData', 'prepareQuery']
            )->getMock();
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->legacyCollectivityManager = new LegacyCollectivityManager($this->service, $this->logger);
    }

    public function testGetLegacyCollectivityList()
    {
        $statement = $this->getMockBuilder(Statement::class)->disableOriginalConstructor()->getMock();
        $this->service->expects(self::once())
            ->method('prepareQuery')
            ->willReturn(
                $statement
            );
        $this->service->expects(self::once())
            ->method('fetchAllData')
            ->willReturn(
                [
                    ['id' => '1', 'name' => 'Sictiam', 'domain' => 'sictiam'],
                    ['id' => '2', 'name' => 'Département 06', 'domain' => 'departement06'],
                ]
            );
        $result = $this->legacyCollectivityManager->getLegacyCollectivityList();
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        self::assertCount(2, $result->getData());
    }
}