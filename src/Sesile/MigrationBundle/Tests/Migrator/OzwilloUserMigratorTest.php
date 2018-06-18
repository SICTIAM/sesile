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
use Sesile\MigrationBundle\Migrator\SesileUserMigrator;
use Sesile\MigrationBundle\Tests\LegacyWebTestCase;
use Http\Mock\Client as MockClient;

class OzwilloUserMigratorTest extends LegacyWebTestCase
{
    /**
     * @var SesileUserMigrator
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
        $sesileUserMigrator = new SesileUserMigrator($client, $collectivityManager, $config, $logger);

        $collectivity = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $result = $sesileUserMigrator->exportCollectivityUsers($collectivity->getId());
        $lastRequest = $mock->getLastRequest();
        //assert basic auth header
        self::assertEquals('application/json', $lastRequest->getHeaderLine('Content-Type'));
        $requestAuth = $lastRequest->getHeaderLine('Authorization');
        $authString = sprintf('%s:%s',$this->getContainer()->getParameter('ozwillo_user_gateway_username'), $this->getContainer()->getParameter('ozwillo_user_gateway_password'));
        self::assertEquals('Basic '. base64_encode($authString), $requestAuth);
        /**
         * assert request body
         */
        $requestBody = json_decode($lastRequest->getBody()->getContents(), true);
        self::assertArrayHasKey('emails', $requestBody);
        self::assertArraySubset(['user1@domain.com', 'toto@domain.com', 'email2@domain.com'], $requestBody['emails']);
        self::assertArrayHasKey('ozwilloInstanceInfo', $requestBody);
        self::assertEquals('todo', $requestBody['ozwilloInstanceInfo']['organizationId']);
        self::assertEquals($collectivity->getOzwillo()->getInstanceId(), $requestBody['ozwilloInstanceInfo']['instanceId']);
        self::assertEquals('0ceacd38-1be2-4e3b-81c6-780d71b20b89', $requestBody['ozwilloInstanceInfo']['creatorId']);
        //@todo serviceId must be properly set during the provisioning
        self::assertEquals($collectivity->getOzwillo()->getServiceId(), $requestBody['ozwilloInstanceInfo']['serviceId']);

        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
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
        $sesileUserMigrator = new SesileUserMigrator($client, $collectivityManager, $config, $logger);

        $collectivity = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $result = $sesileUserMigrator->exportCollectivityUsers($collectivity->getId());

        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
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