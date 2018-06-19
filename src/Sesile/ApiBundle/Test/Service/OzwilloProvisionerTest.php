<?php


namespace Sesile\ApiBundle\Test\Service;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MigrationBundle\Tests\LegacyWebTestCase;

class OzwilloProvisionerTest extends LegacyWebTestCase
{
    /**
     * @var OzwilloProvisioner
     */
    protected $ozwilloProvisioner;
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
        $this->ozwilloProvisioner = $this->getContainer()->get('ozwillo.provisioner');
        parent::setUp();
    }

    public function testNotifyRegistrationToKernel()
    {
        $mock = new MockHandler([new Response(200, [])]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $logger = $this->createMock(LoggerInterface::class);
        $ozwilloProvisioner = new OzwilloProvisioner($client, $logger);
        $ozwilloProvisioner->notifyRegistrationToKernel();

        $lastRequest = $mock->getLastRequest();
        self::assertEquals('application/json', $lastRequest->getHeaderLine('Content-Type'));
        $requestAuth = $lastRequest->getHeaderLine('Authorization');
        $authString = 'username:password';
        self::assertEquals('Basic '. base64_encode($authString), $requestAuth);

    }
}