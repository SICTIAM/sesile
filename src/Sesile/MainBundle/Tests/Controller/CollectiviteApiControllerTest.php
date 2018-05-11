<?php

namespace Sesile\MainBundle\Tests\Controller;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Manager\CollectiviteManager;

/**
 * Class CollectiviteApiControllerTest
 * @package Sesile\MainBundle\Tests\Controller
 */
class CollectiviteApiControllerTest extends WebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    public function setUp()
    {
        $this->fixtures = $this->loadFixtures([
            CollectiviteFixtures::class,
            UserFixtures::class
        ])->getReferenceRepository();
    }

    public function testGetAllOrganisationsShouldReturnAllCollectiviteWithNameAndDomain()
    {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/apirest/collectivite/list');
        $this->assertStatusCode(200, $client);
        $content = json_decode($client->getResponse()->getContent(), true);
        self::assertCount(2, $content);
        self::assertEquals('Sictiam Collectivité', $content[0]['nom']);
        self::assertEquals('sictiam', $content[0]['domain']);
    }

    public function testGetAllOrganisationsShouldReturnErrorCodeWhenErrorIsThrown()
    {
        $collectiviteManager = $this->getMockBuilder(CollectiviteManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectiviteManager->expects(self::once())
            ->method('getCollectivitesList')
            ->willReturn(new Message(false, null, []));

        $client = $this->makeClient();
        $client->getContainer()->set('collectivite.manager', $collectiviteManager);
        $crawler = $client->request('GET', '/apirest/collectivite/list');
        $this->assertStatusCode(500, $client);
    }
}