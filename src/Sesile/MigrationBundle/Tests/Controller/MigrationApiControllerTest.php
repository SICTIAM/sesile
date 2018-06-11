<?php

namespace Sesile\MigrationBundle\Tests\Controller;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MigrationBundle\Tests\LegacyWebTestCase;

class MigrationApiControllerTest extends LegacyWebTestCase
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
        $this->resetLegacyTestDatabase();
        $this->loadLegacyFixtures();
        parent::setUp();
    }

    public function testGetLegacyCollectivityListShouldFailIfNotLoggedIn()
    {
        $this->client->request('GET', '/api/migration/v3v4/collectivity/list');
        $this->assertStatusCode(302, $this->client);
    }

    public function testGetLegacyCollectivityListShouldFailWithAccessDeniedIfNotRoleSuperAdmin()
    {
        $user = $this->fixtures->getReference(UserFixtures::USER_ONE_REFERENCE);
        $this->logIn($user);
        $this->client->request('GET', '/api/migration/v3v4/collectivity/list');
        $this->assertStatusCode(403, $this->client);
    }

    public function testGetLegacyCollectivityList()
    {
        $user = $this->fixtures->getReference(UserFixtures::USER_SUPER_REFERENCE);
        $this->logIn($user);
        $this->client->request('GET', '/api/migration/v3v4/collectivity/list');
        $this->assertStatusCode(200, $this->client);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(2, $content);
        self::assertArrayHasKey('id', $content[0]);
        self::assertArrayHasKey('name', $content[0]);
    }

}