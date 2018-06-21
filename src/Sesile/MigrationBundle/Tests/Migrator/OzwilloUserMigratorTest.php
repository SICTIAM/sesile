<?php


namespace Sesile\MigrationBundle\Tests\Migrator;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Manager\CollectiviteManager;
use Sesile\MigrationBundle\Domain\MigrationReport;
use Sesile\MigrationBundle\Migrator\OzwilloUserMigrator;
use Sesile\MigrationBundle\Tests\LegacyWebTestCase;

class OzwilloUserMigratorTest extends LegacyWebTestCase
{
    /**
     * @var OzwilloUserMigrator
     */
    protected $sesileUserMigrator;
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
        $this->sesileUserMigrator = $this->getContainer()->get('sesile_user.migrator');
        parent::setUp();
    }

    public function testMigrateUsers()
    {
        $mock = new MockHandler([new Response(200, [])]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $collectivityManager = $this->getMockBuilder(CollectiviteManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCollectivityUsersList'])
            ->getMock();
        $collectivityManager->expects(self::once())
            ->method('getCollectivityUsersList')
            ->willReturn(new Message(true, $this->getCollectivityUsersListMockData()));
        //get user_gateway config this is set into the Sesile\MigrationBundle\DependencyInjection\SesileMigrationExtension
        $config = $this->getContainer()->getParameter('user_gateway');
        $logger = $this->createMock(LoggerInterface::class);
        $sesileUserMigrator = new OzwilloUserMigrator($client, $collectivityManager, $config, $logger);

        $collectivity = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $result = $sesileUserMigrator->exportCollectivityUsers($collectivity);
        $lastRequest = $mock->getLastRequest();
        self::assertEquals($this->getContainer()->getParameter('user_gateway')['gateway_uri'], $lastRequest->getUri()->__toString());
        self::assertEquals($this->getContainer()->getParameter('ozwillo_user_gateway_uri'), $lastRequest->getUri()->__toString());
        //assert basic auth header
        self::assertEquals('application/json', $lastRequest->getHeaderLine('Content-Type'));
        $requestAuth = $lastRequest->getHeaderLine('Authorization');
        $authString = sprintf('%s:%s',$this->getContainer()->getParameter('ozwillo_user_gateway_username'), $this->getContainer()->getParameter('ozwillo_user_gateway_password'));
        self::assertEquals('Basic '. base64_encode($authString), $requestAuth);
        /**
         * assert request body
         */
        $fixtureCollectivityOzwillo = $collectivity->getOzwillo();
        $requestBody = json_decode($lastRequest->getBody()->getContents(), true);
        self::assertArrayHasKey('emails', $requestBody);
        self::assertArraySubset(['user1@domain.com', 'toto@domain.com', 'email2@domain.com'], $requestBody['emails']);
        self::assertArrayHasKey('ozwilloInstanceInfo', $requestBody);
        self::assertEquals($fixtureCollectivityOzwillo->getOrganizationId(), $requestBody['ozwilloInstanceInfo']['organizationId']);
        self::assertEquals($fixtureCollectivityOzwillo->getInstanceId(), $requestBody['ozwilloInstanceInfo']['instanceId']);
        $colectivityUserAdminOzwilloId = '0ceacd38-1be2-4e3b-81c6-780d71b20b89';
        self::assertEquals($colectivityUserAdminOzwilloId, $requestBody['ozwilloInstanceInfo']['creatorId']);
        //@todo serviceId must be properly set during the provisioning
        self::assertEquals($fixtureCollectivityOzwillo->getServiceId(), $requestBody['ozwilloInstanceInfo']['serviceId']);

        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        self::assertInstanceOf(MigrationReport::class, $result->getData());
        $migrationReport = $result->getData();
        self::assertEquals(count($this->getCollectivityUsersListMockData()), $migrationReport->countUsers());
        self::assertEquals($fixtureCollectivityOzwillo->getOrganizationId(), $migrationReport->getOrganizationId());
        self::assertEquals($fixtureCollectivityOzwillo->getInstanceId(), $migrationReport->getInstanceId());
        self::assertEquals($fixtureCollectivityOzwillo->getServiceId(), $migrationReport->getServiceId());
    }

    public function testMigrateUsersShouldReturnFalseIfRequestFailed()
    {
        $mock = new MockHandler([new Response(404, [])]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $collectivityManager = $this->getMockBuilder(CollectiviteManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCollectivityUsersList'])
            ->getMock();
        $collectivityManager->expects(self::once())
            ->method('getCollectivityUsersList')
            ->willReturn(new Message(true, $this->getCollectivityUsersListMockData()));
        //get user_gateway config this is set into the Sesile\MigrationBundle\DependencyInjection\SesileMigrationExtension
        $config = $this->getContainer()->getParameter('user_gateway');
        $logger = $this->createMock(LoggerInterface::class);
        $sesileUserMigrator = new OzwilloUserMigrator($client, $collectivityManager, $config, $logger);

        $collectivity = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $result = $sesileUserMigrator->exportCollectivityUsers($collectivity);

        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
        self::assertNull($result->getData());
    }

    public function testMigrateUsersShouldReturnFalseIfCollectivityHasNoOzwilloConfiguration()
    {
        $mock = new MockHandler([new Response(200, [])]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $collectivityManager = $this->createMock(CollectiviteManager::class);
        //get user_gateway config this is set into the Sesile\MigrationBundle\DependencyInjection\SesileMigrationExtension
        $config = $this->getContainer()->getParameter('user_gateway');
        $logger = $this->createMock(LoggerInterface::class);
        $sesileUserMigrator = new OzwilloUserMigrator($client, $collectivityManager, $config, $logger);

        $collectivity = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_THREE_REFERENCE);
        $result = $sesileUserMigrator->exportCollectivityUsers($collectivity);

        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
        self::assertNull($result->getData());
    }

    public function testMigrateUsersShouldReturnFalseIfCollectivityHasNoUsers()
    {
        $mock = new MockHandler([new Response(200, [])]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $collectivityManager = $this->getMockBuilder(CollectiviteManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCollectivityUsersList'])
            ->getMock();
        $collectivityManager->expects(self::once())
            ->method('getCollectivityUsersList')
            ->willReturn(new Message(true, []));
        //get user_gateway config this is set into the Sesile\MigrationBundle\DependencyInjection\SesileMigrationExtension
        $config = $this->getContainer()->getParameter('user_gateway');
        $logger = $this->createMock(LoggerInterface::class);
        $sesileUserMigrator = new OzwilloUserMigrator($client, $collectivityManager, $config, $logger);

        $collectivity = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $result = $sesileUserMigrator->exportCollectivityUsers($collectivity);

        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
        self::assertNull($result->getData());
    }

    private function getCollectivityUsersListMockData()
    {
        return
            [
                [
                    'id' => 16,
                    'nom' => 'nom1',
                    'prenom' => 'prenom1',
                    'email' => 'user1@domain.com',
                    'username' => 'username',
                    'ozwilloId' => '0ceacd38-1be2-4e3b-81c6-780d71b20b89',
                    'ville' => 'Nice',
                    'cp' => '06000',
                    'pays' => 'France',
                    'departement' => 'Alpes-Maritimes',
                    'role' => 'Développeur',
                    'qualite' => 'CTO',
                    'roles' =>[
                        0 => 'ROLE_ADMIN',
                    ]
                ],[
                    'id' => 8,
                    'nom' => 'nom1',
                    'prenom' => 'toto',
                    'email' => 'toto@domain.com',
                    'username' => 'lolo',
                    'ozwilloId' => null,
                    'ville' => 'Nice',
                    'cp' => '06000',
                    'pays' => 'France',
                    'departement' => 'Alpes-Maritimes',
                    'role' => 'Développeur',
                    'qualite' => 'CTO',
                    'roles' =>[]
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
            ];
    }

}