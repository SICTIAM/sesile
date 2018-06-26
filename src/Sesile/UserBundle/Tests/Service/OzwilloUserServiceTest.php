<?php


namespace Sesile\UserBundle\Tests\Service;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;
use Sesile\UserBundle\Service\OzwilloUserService;

class OzwilloUserServiceTest extends SesileWebTestCase
{
    /**
     * @var OzwilloUserService
     */
    protected $ozwilloUserService;
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
        $this->ozwilloUserService = $this->getContainer()->get('ozwillo_user.service');
        parent::setUp();
    }

    public function testGetOzwilloAclInstance()
    {
        $mock = new MockHandler(
            [
                new Response(
                    200, ['Content-Type' => 'application/json'], '[
                    {
                        "id": "c50eda08-ea25-49b2-a4fc-ec89cc98209c",
                        "entry_uri": "https://kernel.ozwillo.com/apps/acl/ace/c50eda08-ea25-49b2-a4fc-ec89cc98209c",
                        "entry_etag": "\"1529942756432\"",
                        "instance_id": "b9bdc41e-4c8c-4e2a-8e38-d3abe120b535",
                        "user_id": "7b2ee276-cef6-4227-add0-6242515f0780",
                        "user_name": "nom1 prenom1",
                        "user_email_address": "user1@domain.com",
                        "created": 1529942756.432,
                        "creator_id": "7b2ee276-cef6-4227-add0-6242515f0780",
                        "creator_name": "ali boulajine",
                        "app_user": true,
                        "app_admin": true
                    },
                    {
                        "id": "00fd8055-5440-4a2f-bcaa-4758e8db5ee4",
                        "entry_uri": "https://kernel.ozwillo.com/apps/acl/ace/00fd8055-5440-4a2f-bcaa-4758e8db5ee4",
                        "entry_etag": "\"1529942746327\"",
                        "instance_id": "b9bdc41e-4c8c-4e2a-8e38-d3abe120b535",
                        "user_id": "9be1ffcf-116a-48e6-b41f-707dba8fdab1",
                        "user_name": "nom2 prenom",
                        "user_email_address": "email2@domain.com",
                        "created": 1529942746.327,
                        "creator_id": "7b2ee276-cef6-4227-add0-6242515f0780",
                        "creator_name": "ali boulajine",
                        "app_user": true
                    },
                    {
                        "instance_id": "b9bdc41e-4c8c-4e2a-8e38-d3abe120b535",
                        "user_id": "0056988d-55e4-4c76-9ed3-29054b459026",
                        "user_name": "super nom super prenom",
                        "user_email_address": "super@domain.com",
                        "app_admin": true
                    }
                ]'
                ),
            ]
        );
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $config = $this->getContainer()->getParameter('ozwillo_acl_instance_url');
        $logger = $this->createMock(LoggerInterface::class);
        $ozwilloUserService = new OzwilloUserService($client, $config, $logger);

        $collectivite = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $userAccessToken = "eyJpZCI6IjdjMTRmZjhmLWY2NWUtNDRhOC1iMjU0LTBmMzQ4MjQ4ZjliMC9EV1RPaW5sMWhSRUhVcmtXWkVqbXNBIiwiaWF0IjoxNTMwMDE1NzU4Ljk4OTAwMDAwMCwiZXhwIjoxNTMwMDE5MzU4Ljk4OTAwMDAwMH0";
        $result = $ozwilloUserService->getOzwilloAclInstance($collectivite->getOzwillo()->getInstanceId(), $userAccessToken);
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        self::assertCount(3, json_decode($result->getData(), true));
    }

    public function testGetOzwilloAclInstanceShouldReturnFalseIfUserTokenNotAdmin()
    {
        $mock = new MockHandler(
            [
                new Response(
                    403, ['Content-Type' => 'application/json'], 'Current user is not an app_admin for the application instance'
                )
            ]
        );
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $config = $this->getContainer()->getParameter('ozwillo_acl_instance_url');
        $logger = $this->createMock(LoggerInterface::class);
        $ozwilloUserService = new OzwilloUserService($client, $config, $logger);

        $collectivite = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $userAccessToken = "eyJpZCI6IjdjMTRmZjhmLWY2NWUtNDRhOC1iMjU0LTBmMzQ4MjQ4ZjliMC9EV1RPaW5sMWhSRUhVcmtXWkVqbXNBIiwiaWF0IjoxNTMwMDE1NzU4Ljk4OTAwMDAwMCwiZXhwIjoxNTMwMDE5MzU4Ljk4OTAwMDAwMH0";
        $result = $ozwilloUserService->getOzwilloAclInstance($collectivite->getOzwillo()->getInstanceId(), $userAccessToken);
        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
    }

}