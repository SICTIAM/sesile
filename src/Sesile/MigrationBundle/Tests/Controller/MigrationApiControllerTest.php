<?php

namespace Sesile\MigrationBundle\Tests\Controller;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\SesileMigrationFixtures;
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
        $this->client->request('GET', '/api/migration/v3v4/collectivity/legacy/list');
        $this->assertStatusCode(302, $this->client);
    }

    public function testGetLegacyCollectivityListShouldFailWithAccessDeniedIfNotRoleSuperAdmin()
    {
        $user = $this->fixtures->getReference(UserFixtures::USER_ONE_REFERENCE);
        $this->logIn($user);
        $this->client->request('GET', '/api/migration/v3v4/collectivity/legacy/list');
        $this->assertStatusCode(403, $this->client);
    }

    public function testGetLegacyCollectivityList()
    {
        $user = $this->fixtures->getReference(UserFixtures::USER_SUPER_REFERENCE);
        $this->logIn($user);
        $this->client->request('GET', '/api/migration/v3v4/collectivity/legacy/list');
        $this->assertStatusCode(200, $this->client);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(2, $content);
        self::assertArrayHasKey('id', $content[0]);
        self::assertArrayHasKey('name', $content[0]);
    }
    public function testGetCollectivityListShouldFailIfNotLoggedIn()
    {
        $this->client->request('GET', '/api/migration/v3v4/collectivity/list');
        $this->assertStatusCode(302, $this->client);
    }

    public function testGetCollectivityListShouldFailWithAccessDeniedIfNotRoleSuperAdmin()
    {
        $user = $this->fixtures->getReference(UserFixtures::USER_ONE_REFERENCE);
        $this->logIn($user);
        $this->client->request('GET', '/api/migration/v3v4/collectivity/list');
        $this->assertStatusCode(403, $this->client);
    }

    public function testGetCollectivityList()
    {
        $user = $this->fixtures->getReference(UserFixtures::USER_SUPER_REFERENCE);
        $this->logIn($user);
        $this->client->request('GET', '/api/migration/v3v4/collectivity/list');
        $this->assertStatusCode(200, $this->client);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(2, $content);
        $collectivityOne = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        self::assertEquals($collectivityOne->getId(), $content[0]['id']);
        self::assertEquals($collectivityOne->getNom(), $content[0]['nom']);
        self::assertEquals($collectivityOne->getDomain(), $content[0]['domain']);
        $collectivityTwo = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_TWO_REFERENCE);
        self::assertEquals($collectivityTwo->getId(), $content[1]['id']);
        self::assertEquals($collectivityTwo->getNom(), $content[1]['nom']);
        self::assertEquals($collectivityTwo->getDomain(), $content[1]['domain']);
    }

    public function testGetCollectivityListShouldExludeTheCollectivityInSesileMigration()
    {
        $collectivityOne = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $this->persistSesileMigration($collectivityOne);

        $user = $this->fixtures->getReference(UserFixtures::USER_SUPER_REFERENCE);
        $this->logIn($user);
        $this->client->request('GET', '/api/migration/v3v4/collectivity/list');
        $this->assertStatusCode(200, $this->client);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(1, $content);
        $collectivityTwo = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_TWO_REFERENCE);
        self::assertEquals($collectivityTwo->getId(), $content[0]['id']);
        self::assertEquals($collectivityTwo->getNom(), $content[0]['nom']);
        self::assertEquals($collectivityTwo->getDomain(), $content[0]['domain']);
    }

    public function testCheckCollectivitySirenShouldSucceedIfSirenIsAvailable()
    {
        $user = $this->fixtures->getReference(UserFixtures::USER_SUPER_REFERENCE);
        $this->logIn($user);
        $this->client->request('GET', '/api/migration/v3v4/org/check/siren/FR1212121');
        $this->assertStatusCode(200, $this->client);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        //{success: 1, siren: "21121"}
        self::assertEquals(1, $content['success']);
        self::assertEquals('FR1212121', $content['siren']);
    }

    public function testCheckCollectivitySirenShouldFailIfSirenIsNotAvailable()
    {
        $collectivity = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $user = $this->fixtures->getReference(UserFixtures::USER_SUPER_REFERENCE);
        $this->logIn($user);
        $this->client->request('GET', sprintf('/api/migration/v3v4/org/check/siren/%s', $collectivity->getSiren()));
        $this->assertStatusCode(200, $this->client);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        //{success: 0, siren: "21121", orgName : "nom de la collectivité avec le SIREN" }
        self::assertEquals(0, $content['success']);
        self::assertEquals($collectivity->getSiren(), $content['siren']);
        self::assertEquals($collectivity->getNom(), $content['orgName']);
    }

    private function persistSesileMigration($collectivity, $siren = '123456789')
    {
        $entityManager= $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $sesileMigration = SesileMigrationFixtures::aValidSesileMigration($collectivity);
        $entityManager->persist($sesileMigration);
        $entityManager->flush();
    }

}