<?php

namespace Sesile\MainBundle\Tests\Controller;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;

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
        self::assertCount(1, $content);
        self::assertEquals('Sictiam Collectivité', $content[0]['nom']);
        self::assertEquals('sictiam', $content[0]['domain']);
    }
}