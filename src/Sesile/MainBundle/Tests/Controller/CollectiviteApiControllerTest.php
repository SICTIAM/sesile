<?php

namespace Sesile\MainBundle\Tests\Controller;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\MainBundle\Manager\CollectiviteManager;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;

/**
 * Class CollectiviteApiControllerTest
 * @package Sesile\MainBundle\Tests\Controller
 */
class CollectiviteApiControllerTest extends SesileWebTestCase
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
        parent::setUp();
    }

    public function testGetAllOrganisationsShouldReturnAllCollectiviteWithNameAndDomain()
    {
        $crawler = $this->client->request('GET', '/apirest/collectivite/list');
        $this->assertStatusCode(200, $this->client);
        $content = json_decode($this->client->getResponse()->getContent(), true);
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

        $this->client->getContainer()->set('collectivite.manager', $collectiviteManager);
        $crawler = $this->client->request('GET', '/apirest/collectivite/list');
        $this->assertStatusCode(500, $this->client);
    }

    public function testUpdateCollectivite()
    {
        $this->logIn($this->fixtures->getReference('user-one'));
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $postData = [
            'nom' => 'this is the new name'
        ];
        $this->client->request('PATCH', sprintf('/apirest/collectivite/%s', $collectivite->getId()));

        $this->client->request(
            'PATCH',
            sprintf('/apirest/collectivite/%s', $collectivite->getId()),
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($postData)
        );
        $this->assertStatusCode(200, $this->client);
        /**
         * check database
         */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->clear();
        $data = $entityManager->getRepository(Collectivite::class)->find($collectivite->getId());
        self::assertEquals('this is the new name', $data->getNom());
    }

    public function testUpdateCollectiviteShouldNotBeAllowedIfUserIsNotInDemandedCollectivite()
    {
        $this->logIn($this->fixtures->getReference('user-one'));
        $collectivite = $this->fixtures->getReference('collectivite-two');
        $this->client->request('PATCH', sprintf('/apirest/collectivite/%s', $collectivite->getId()));
        $this->assertStatusCode(403, $this->client);
    }

    public function testUpdateCollectiviteShouldNotBeAllowedIfNotLoggedIn()
    {
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $this->client->request('PATCH', sprintf('/apirest/collectivite/%s', $collectivite->getId()));
        $this->assertStatusCode(302, $this->client);
    }
}