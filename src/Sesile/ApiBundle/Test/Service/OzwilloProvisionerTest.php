<?php


namespace Sesile\ApiBundle\Test\Service;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;
use Sesile\ApiBundle\Service\OzwilloProvisioner;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\MainBundle\Manager\CollectiviteManager;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;

class OzwilloProvisionerTest extends SesileWebTestCase
{
    /**
     * @var OzwilloProvisioner
     */
    protected $ozwilloProvisioner;
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;
    /**
     * @var CollectiviteManager
     */
    protected $collectivityManager;

    public function setUp()
    {
        $this->fixtures = $this->loadFixtures(
            [
                CollectiviteFixtures::class,
            ]
        )->getReferenceRepository();
        $this->ozwilloProvisioner = $this->getContainer()->get('ozwillo.provisioner');
        $this->collectivityManager = $this->getContainer()->get('collectivite.manager');
        parent::setUp();
    }

    public function testNotifyRegistrationToKernel()
    {
        $collectivite = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $expectedServiceLocalId = OzwilloProvisioner::SERVICE_LOCAL_ID.'-'.$collectivite->getDomain();
        $mock = new MockHandler(
            [
                new Response(
                    201,
                    ['Content-Type' => 'application/json'],
                    '{"'.$expectedServiceLocalId.'": "31336385-f2ff-4488-8835-1f7da53669b9"}'
                ),
            ]
        );
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);


        $logger = $this->createMock(LoggerInterface::class);
        $router = $this->getContainer()->get('router');
        $domainParameter = $this->getContainer()->getParameter('domain');
        $contactParameter = $this->getContainer()->getParameter('contact');
        $ozwilloProvisioner = new OzwilloProvisioner(
            $client,
            $this->collectivityManager,
            $router,
            $domainParameter,
            $contactParameter,
            $logger
        );

        $result = $ozwilloProvisioner->notifyRegistrationToKernel($collectivite);
        $lastRequest = $mock->getLastRequest();
        self::assertEquals('application/json', $lastRequest->getHeaderLine('Content-Type'));
        $requestAuth = $lastRequest->getHeaderLine('Authorization');
        $authString = $collectivite->getOzwillo()->getClientId().':'.$collectivite->getOzwillo()->getClientSecret();
        self::assertEquals('Basic '.base64_encode($authString), $requestAuth);
        /**
         * assert request body
         */
        $requestBody = json_decode($lastRequest->getBody()->getContents(), true);
        self::assertEquals($collectivite->getOzwillo()->getInstanceId(), $requestBody['instance_id']);
        $domainParameter = $this->getContainer()->getParameter('domain');
        $services = [
            'local_id' => OzwilloProvisioner::SERVICE_LOCAL_ID.'-'.$collectivite->getDomain(),
            'name' => "SESILE - ".$collectivite->getNom(),
            'tos_uri' => "https://sesile.fr/tos",
            'policy_uri' => "https://sesile.fr/policy",
            'icon' => "https://www.ozwillo.com/static/img/editors/sesile-icon-64x64.png",
            'contacts' => ['mailto:'.$this->getContainer()->getParameter('contact')],//demat@sictiam.fr
            'payment_option' => "PAID",
            'target_audience' => ["PUBLIC_BODIES"],
            'visibility' => "VISIBLE",
            'access_control' => "RESTRICTED",
//            'service_uri' => $this->urlRegistrationToKernel($collectivite, '/'),
            'service_uri' => 'https://sictiam.'.$domainParameter.'/connect/ozwillo',
//            'redirect_uris' => [$this->urlRegistrationToKernel($collectivite, '/login/check-ozwillo')],
            'redirect_uris' => [
                'https://sictiam.'.$domainParameter.'/login/check-ozwillo',
                'http://sictiam.'.$domainParameter.'/login/check-ozwillo',
            ],
        ];
        self::assertCount(1, $requestBody['services']);
        self::assertCount(2, $requestBody['services'][0]['redirect_uris']);
        self::assertArraySubset($services, $requestBody['services'][0]);
        self::assertArrayHasKey('destruction_uri', $requestBody);
        self::assertArrayHasKey('destruction_secret', $requestBody);
        self::assertArrayHasKey('status_changed_uri', $requestBody);
        self::assertArrayHasKey('status_changed_secret', $requestBody);

        self::assertEquals($collectivite->getOzwillo()->getInstanceId(), $requestBody['instance_id']);
        /**
         * assert $result
         */
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        /**
         * CHECK DATABASE
         */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $em->clear();
        $collectiviteData = $em->getRepository(Collectivite::class)->find($collectivite->getId());
        self::assertTrue($collectiviteData->getOzwillo()->getNotifiedToKernel());
        self::assertEquals("31336385-f2ff-4488-8835-1f7da53669b9", $collectiviteData->getOzwillo()->getServiceId());
    }

    public function testNotifyRegistrationToKernelShouldFailIfNoInstanceRegistrationUriIsSet()
    {
        $mock = new MockHandler(
            [
                new Response(
                    201,
                    ['Content-Type' => 'application/json'],
                    '{"localId": "31336385-f2ff-4488-8835-1f7da53669b9"}'
                ),
            ]
        );
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $collectivite = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $ozwillo = $collectivite->getOzwillo();
        $ozwillo->setInstanceRegistrationUri(null);

        $logger = $this->createMock(LoggerInterface::class);
        $router = $this->getContainer()->get('router');
        $domainParameter = $this->getContainer()->getParameter('domain');
        $contactParameter = $this->getContainer()->getParameter('contact');
        $ozwilloProvisioner = new OzwilloProvisioner(
            $client,
            $this->collectivityManager,
            $router,
            $domainParameter,
            $contactParameter,
            $logger
        );

        $result = $ozwilloProvisioner->notifyRegistrationToKernel($collectivite);

        $lastRequest = $mock->getLastRequest();
        self::assertNull($lastRequest);
        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
        /**
         * CHECK DATABASE
         */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $em->clear();
        $collectiviteData = $em->getRepository(Collectivite::class)->find($collectivite->getId());
        self::assertFalse($collectiviteData->getOzwillo()->getNotifiedToKernel());
    }

    public function testNotifyRegistrationToKernelShouldFailIfRegistrationUriReturnsAnErrorCode()
    {
        $mock = new MockHandler(
            [
                new Response(
                    422,
                    ['Content-Type' => 'application/json'],
                    '{"error": "invalid_request"}'
                ),
            ]
        );
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $collectivite = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $logger = $this->createMock(LoggerInterface::class);
        $router = $this->getContainer()->get('router');
        $domainParameter = $this->getContainer()->getParameter('domain');
        $contactParameter = $this->getContainer()->getParameter('contact');
        $ozwilloProvisioner = new OzwilloProvisioner(
            $client,
            $this->collectivityManager,
            $router,
            $domainParameter,
            $contactParameter,
            $logger
        );
        $result = $ozwilloProvisioner->notifyRegistrationToKernel($collectivite);

        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
        self::assertCount(1, $result->getErrors());
        /**
         * CHECK DATABASE
         */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $em->clear();
        $collectiviteData = $em->getRepository(Collectivite::class)->find($collectivite->getId());
        self::assertFalse($collectiviteData->getOzwillo()->getNotifiedToKernel());
    }

    private function urlRegistrationToKernel(Collectivite $collectivite, $path)
    {
        return 'https://'.$collectivite->getDomain().'.'.$this->getContainer()->getParameter('domain').$path;
    }

}