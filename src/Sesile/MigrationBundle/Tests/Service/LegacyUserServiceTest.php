<?php


namespace Sesile\MigrationBundle\Tests\Service;


use Sesile\MigrationBundle\Tests\LegacyWebTestCase;

class LegacyUserServiceTest extends LegacyWebTestCase
{
    /**
     * @var LegacyCollectivityService
     */
    protected $service;

    public function setUp()
    {
        $this->service = $this->getContainer()->get('legacy.user.service');
        $this->resetLegacyTestDatabase();
        $this->loadLegacyFixtures();
        parent::setUp();
    }

    public function testGetLegacyUsers()
    {
        $oldCollectivityId = 1;
        $result = $this->service->getLegacyUsers($oldCollectivityId);
        self::assertCount(2, $result);
        self::assertArrayHasKey('id', $result[0]);
        self::assertArrayHasKey('username', $result[0]);
        self::assertArrayHasKey('username_canonical', $result[0]);
        self::assertArrayHasKey('email', $result[0]);
        self::assertArrayHasKey('email', $result[0]);
        self::assertArrayHasKey('email_canonical', $result[0]);
        self::assertArrayHasKey('enabled', $result[0]);
        self::assertArrayHasKey('salt', $result[0]);
        self::assertArrayHasKey('password', $result[0]);
        self::assertArrayHasKey('last_login', $result[0]);
        self::assertArrayHasKey('confirmation_token', $result[0]);
        self::assertArrayHasKey('password_requested_at', $result[0]);
        self::assertArrayHasKey('roles', $result[0]);
        self::assertArrayHasKey('Nom', $result[0]);
        self::assertArrayHasKey('Prenom', $result[0]);
        self::assertArrayHasKey('path', $result[0]);
        self::assertArrayHasKey('ville', $result[0]);
        self::assertArrayHasKey('code_postal', $result[0]);
        self::assertArrayHasKey('pays', $result[0]);
        self::assertArrayHasKey('departement', $result[0]);
        self::assertArrayHasKey('role', $result[0]);
        self::assertArrayHasKey('apitoken', $result[0]);
        self::assertArrayHasKey('apisecret', $result[0]);
        self::assertArrayHasKey('apiactivated', $result[0]);
        self::assertArrayHasKey('collectivite', $result[0]);
        self::assertArrayHasKey('pathSignature', $result[0]);
        self::assertArrayHasKey('qualite', $result[0]);
        self::assertArrayHasKey('sesile_version', $result[0]);
    }

}